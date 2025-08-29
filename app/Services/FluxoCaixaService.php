<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Parcela;
use App\Services\TabelaDinamica;

class FluxoCaixaService
{
    /**
     * Obtém dados do fluxo geral agrupados por cidade e vendedor
     */
    public function obterDadosFluxoGeral($dataInicio, $dataFim, $usuario)
    {
        // Cache para consultas frequentes (apenas para períodos pequenos)
        $cacheKey = "fluxo_geral_{$usuario->id}_{$dataInicio}_{$dataFim}";
        $cacheMinutos = config('fluxo-caixa.cache_consultas_minutos', 5);

        // Só usar cache para consultas do dia atual ou períodos pequenos
        $usarCache = $this->deveUsarCache($dataInicio, $dataFim);

        if ($usarCache) {
            return Cache::remember($cacheKey, $cacheMinutos, function() use ($dataInicio, $dataFim, $usuario) {
                return $this->processarFluxoGeral($dataInicio, $dataFim, $usuario);
            });
        }

        return $this->processarFluxoGeral($dataInicio, $dataFim, $usuario);
    }

    /**
     * Processa os dados do fluxo geral
     */
    private function processarFluxoGeral($dataInicio, $dataFim, $usuario)
    {
        $cidades = $this->obterCidadesPermitidas($usuario);
        $dadosFluxo = [];

        foreach ($cidades as $cidade) {
            $dadosCidade = $this->processarDadosCidade($cidade, $dataInicio, $dataFim, $usuario);

            if (!empty($dadosCidade['vendedores'])) {
                $dadosFluxo[] = $dadosCidade;
            }
        }

        return [
            'cidades' => $dadosFluxo,
            'resumo_geral' => $this->calcularResumoGeral($dadosFluxo)
        ];
    }

    /**
     * Determina se deve usar cache baseado no período
     */
    private function deveUsarCache($dataInicio, $dataFim)
    {
        $inicio = \Carbon\Carbon::parse($dataInicio);
        $fim = \Carbon\Carbon::parse($dataFim);
        $diasDiferenca = $inicio->diffInDays($fim);

        // Usar cache apenas para períodos de até 7 dias
        return $diasDiferenca <= 7;
    }

    /**
     * Obtém dados do fluxo individual para um vendedor específico
     */
    public function obterDadosFluxoIndividual($dataInicio, $dataFim, $vendedorId, $usuario)
    {
        // Validar se o usuário tem permissão para ver este vendedor
        if (!$this->usuarioPodeVerVendedor($usuario, $vendedorId)) {
            throw new \Exception('Você não tem permissão para visualizar dados deste vendedor.');
        }

        $vendedor = User::find($vendedorId);
        if (!$vendedor) {
            throw new \Exception('Vendedor não encontrado.');
        }

        $cidade = $this->obterNomeCidade($vendedor->cidade);

        $vendas = $this->obterVendasIndividuais($cidade, $vendedorId, $dataInicio, $dataFim);

        return [
            'vendedor' => $vendedor,
            'cidade' => $cidade,
            'vendas' => $vendas,
            'resumo_vendas' => $this->calcularResumoVendas($vendas),
            'periodo' => ['inicio' => $dataInicio, 'fim' => $dataFim]
        ];
    }

    /**
     * Processa dados de uma cidade específica
     */
    private function processarDadosCidade($cidade, $dataInicio, $dataFim, $usuario)
    {
        $vendedores = $this->obterVendedoresCidade($cidade['id'], $usuario, $cidade['nome'], $dataInicio, $dataFim);
        $dadosVendedores = [];

        foreach ($vendedores as $vendedor) {
            $vendas = $this->obterVendasVendedor($cidade['nome'], $vendedor->id, $dataInicio, $dataFim);
            $recebimentos = $this->obterRecebimentosVendedor($cidade['nome'], $vendedor->id, $dataInicio, $dataFim);

            // Só inclui vendedor se teve vendas ou recebimentos no período
            if (!empty($vendas) || !empty($recebimentos)) {
                $dadosVendedores[] = [
                    'vendedor' => $vendedor,
                    'vendas' => $vendas,
                    'recebimentos' => $recebimentos,
                    'resumo_vendas' => $this->calcularResumoVendas($vendas),
                    'resumo_recebimentos' => $this->calcularResumoRecebimentos($recebimentos)
                ];
            }
        }

        $despesas = $this->obterDespesas($cidade['nome'], $dataInicio, $dataFim);

        return [
            'cidade' => $cidade,
            'vendedores' => $dadosVendedores,
            'despesas' => $despesas,
            'resumo_cidade' => $this->calcularResumoCidade($dadosVendedores, $despesas)
        ];
    }

    /**
     * Obtém cidades que o usuário tem permissão para visualizar
     */
    private function obterCidadesPermitidas($usuario)
    {
        if ($usuario->nivel === 'administrador') {
            return DB::table('cidades')
                ->select('id', 'cidade as nome')
                ->get()
                ->map(function($cidade) {
                    return [
                        'id' => $cidade->id,
                        'nome' => $cidade->nome
                    ];
                })
                ->toArray();
        } else {
            $cidade = DB::table('cidades')->where('id', $usuario->cidade)->first();
            return $cidade ? [['id' => $cidade->id, 'nome' => $cidade->cidade]] : [];
        }
    }

    /**
     * Obtém vendedores de uma cidade específica que tenham dados no período
     */
    private function obterVendedoresCidade($cidadeId, $usuario, $nomeCidade, $dataInicio, $dataFim)
    {
        // Se não for administrador, mostra apenas o próprio usuário
        if ($usuario->nivel !== 'administrador') {
            return User::where('id', $usuario->id)->get();
        }

        // Para administradores, buscar vendedores que tenham vendas ou recebimentos na cidade no período
        $tabelaVendas = TabelaDinamica::vendas($nomeCidade);
        
        // Buscar IDs de vendedores que fizeram vendas
        $vendedoresComVendas = [];
        if ($this->tabelaExiste($tabelaVendas)) {
            $vendedoresComVendas = DB::table($tabelaVendas)
                ->whereBetween('data_venda', [$dataInicio, $dataFim])
                ->whereRaw('(data_estorno IS NULL OR data_venda != data_estorno)')
                ->distinct()
                ->pluck('id_vendedor')
                ->toArray();
        }
        
        // Buscar IDs de vendedores que receberam parcelas
        $vendedoresComRecebimentos = Parcela::where('bd', $tabelaVendas)
            ->whereBetween('data_pagamento', [$dataInicio, $dataFim])
            ->whereNotNull('data_pagamento')
            ->distinct()
            ->pluck('id_vendedor')
            ->toArray();
        
        // Combinar ambos os arrays
        $vendedoresIds = array_unique(array_merge($vendedoresComVendas, $vendedoresComRecebimentos));
        
        if (empty($vendedoresIds)) {
            return collect([]);
        }
        
        // Retornar usuários que têm dados, independente de status ou cidade
        return User::whereIn('id', $vendedoresIds)->get();
    }

    /**
     * Obtém vendas de um vendedor específico
     */
    private function obterVendasVendedor($nomeCidade, $vendedorId, $dataInicio, $dataFim)
    {
        $tabelaVendas = TabelaDinamica::vendas($nomeCidade);

        if (!$this->tabelaExiste($tabelaVendas)) {
            Log::warning("Tabela de vendas não encontrada: {$tabelaVendas}");
            return [];
        }

        return $this->executarConsultaSegura(function() use ($tabelaVendas, $vendedorId, $dataInicio, $dataFim) {
            return DB::table($tabelaVendas)
                ->where('id_vendedor', $vendedorId)
                ->whereBetween('data_venda', [$dataInicio, $dataFim])
                ->whereRaw('(data_estorno IS NULL OR data_venda != data_estorno)')
                ->orderBy('data_venda')
                ->orderBy('hora')
                ->get()
                ->toArray();
        }, "Consulta vendas vendedor {$vendedorId} em {$nomeCidade}");
    }

    /**
     * Obtém vendas individuais (usando id_vendedor_atendente)
     */
    private function obterVendasIndividuais($nomeCidade, $vendedorId, $dataInicio, $dataFim)
    {
        $tabelaVendas = TabelaDinamica::vendas($nomeCidade);

        if (!$this->tabelaExiste($tabelaVendas)) {
            Log::warning("Tabela de vendas não encontrada: {$tabelaVendas}");
            return [];
        }

        return DB::table($tabelaVendas)
            ->where('id_vendedor_atendente', $vendedorId)
            ->whereBetween('data_venda', [$dataInicio, $dataFim])
            ->whereRaw('(data_estorno IS NULL OR data_venda != data_estorno)')
            ->orderBy('data_venda')
            ->orderBy('hora')
            ->get()
            ->toArray();
    }

    /**
     * Obtém recebimentos de parcelas de um vendedor
     */
    private function obterRecebimentosVendedor($nomeCidade, $vendedorId, $dataInicio, $dataFim)
    {
        $tabelaVendas = TabelaDinamica::vendas($nomeCidade);
        
        return $this->executarConsultaSegura(function() use ($tabelaVendas, $vendedorId, $dataInicio, $dataFim) {
            return Parcela::where('id_vendedor', $vendedorId)
                ->where('bd', $tabelaVendas)
                ->whereBetween('data_pagamento', [$dataInicio, $dataFim])
                ->whereNotNull('data_pagamento')
                ->orderBy('metodo')
                ->orderBy('data_pagamento')
                ->orderBy('hora')
                ->get()
                ->toArray();
        }, "Consulta recebimentos vendedor {$vendedorId} em {$nomeCidade}");
    }

    /**
     * Obtém despesas de uma cidade no período
     */
    private function obterDespesas($nomeCidade, $dataInicio, $dataFim)
    {
        $tabelaDespesas = TabelaDinamica::despesas($nomeCidade);

        if (!$this->tabelaExiste($tabelaDespesas)) {
            Log::warning("Tabela de despesas não encontrada: {$tabelaDespesas}");
            return [];
        }

        return DB::table($tabelaDespesas)
            ->where('tipo', 'despesa')
            ->where('status', 'Pago')
            ->whereBetween('data', [$dataInicio, $dataFim])
            ->orderBy('data')
            ->get()
            ->toArray();
    }

    /**
     * Calcula resumo de vendas
     */
    private function calcularResumoVendas($vendas)
    {
        $resumo = [
            'total_dinheiro' => 0,
            'total_pix' => 0,
            'total_cartao' => 0,
            'total_crediario' => 0,
            'total_geral' => 0,
            'quantidade_vendas' => 0,
            'vendas_estornadas' => 0
        ];

        foreach ($vendas as $venda) {
            // Tratar tanto objetos quanto arrays
            $dataEstorno = is_object($venda) ? ($venda->data_estorno ?? null) : ($venda['data_estorno'] ?? null);
            $multiplicador = ($dataEstorno) ? -1 : 1;

            $valorDinheiro = is_object($venda) ? ($venda->valor_dinheiro ?? 0) : ($venda['valor_dinheiro'] ?? 0);
            $valorPix = is_object($venda) ? ($venda->valor_pix ?? 0) : ($venda['valor_pix'] ?? 0);
            $valorCartao = is_object($venda) ? ($venda->valor_cartao ?? 0) : ($venda['valor_cartao'] ?? 0);
            $valorCrediario = is_object($venda) ? ($venda->valor_crediario ?? 0) : ($venda['valor_crediario'] ?? 0);

            $resumo['total_dinheiro'] += $valorDinheiro * $multiplicador;
            $resumo['total_pix'] += $valorPix * $multiplicador;
            $resumo['total_cartao'] += $valorCartao * $multiplicador;
            $resumo['total_crediario'] += $valorCrediario * $multiplicador;

            $resumo['quantidade_vendas']++;

            if ($multiplicador === -1) {
                $resumo['vendas_estornadas']++;
            }
        }

        $resumo['total_geral'] = $resumo['total_dinheiro'] + $resumo['total_pix'] +
                                $resumo['total_cartao'] + $resumo['total_crediario'];

        return $resumo;
    }

    /**
     * Calcula resumo de recebimentos
     */
    private function calcularResumoRecebimentos($recebimentos)
    {
        $resumo = [
            'total_dinheiro' => 0,
            'total_pix' => 0,
            'total_cartao' => 0,
            'total_geral' => 0,
            'quantidade_recebimentos' => count($recebimentos)
        ];

        foreach ($recebimentos as $recebimento) {
            // Tratar tanto objetos quanto arrays
            $dinheiro = is_object($recebimento) ? ($recebimento->dinheiro ?? 0) : ($recebimento['dinheiro'] ?? 0);
            $pix = is_object($recebimento) ? ($recebimento->pix ?? 0) : ($recebimento['pix'] ?? 0);
            $cartao = is_object($recebimento) ? ($recebimento->cartao ?? 0) : ($recebimento['cartao'] ?? 0);

            $resumo['total_dinheiro'] += $dinheiro;
            $resumo['total_pix'] += $pix;
            $resumo['total_cartao'] += $cartao;
        }

        $resumo['total_geral'] = $resumo['total_dinheiro'] + $resumo['total_pix'] + $resumo['total_cartao'];

        return $resumo;
    }

    /**
     * Calcula resumo de uma cidade
     */
    private function calcularResumoCidade($vendedores, $despesas)
    {
        $resumo = [
            'vendas' => [
                'total_dinheiro' => 0,
                'total_pix' => 0,
                'total_cartao' => 0,
                'total_crediario' => 0,
                'total_geral' => 0
            ],
            'recebimentos' => [
                'total_dinheiro' => 0,
                'total_pix' => 0,
                'total_cartao' => 0,
                'total_geral' => 0
            ],
            'despesas' => [
                'total' => 0
            ]
        ];

        // Soma vendas e recebimentos de todos os vendedores
        foreach ($vendedores as $vendedor) {
            $resumoVendas = $vendedor['resumo_vendas'];
            $resumoRecebimentos = $vendedor['resumo_recebimentos'];

            $resumo['vendas']['total_dinheiro'] += $resumoVendas['total_dinheiro'];
            $resumo['vendas']['total_pix'] += $resumoVendas['total_pix'];
            $resumo['vendas']['total_cartao'] += $resumoVendas['total_cartao'];
            $resumo['vendas']['total_crediario'] += $resumoVendas['total_crediario'];

            $resumo['recebimentos']['total_dinheiro'] += $resumoRecebimentos['total_dinheiro'];
            $resumo['recebimentos']['total_pix'] += $resumoRecebimentos['total_pix'];
            $resumo['recebimentos']['total_cartao'] += $resumoRecebimentos['total_cartao'];
        }

        // Calcula totais
        $resumo['vendas']['total_geral'] = array_sum($resumo['vendas']);
        $resumo['recebimentos']['total_geral'] = array_sum($resumo['recebimentos']);

        // Soma despesas
        foreach ($despesas as $despesa) {
            $valor = is_object($despesa) ? ($despesa->valor ?? 0) : ($despesa['valor'] ?? 0);
            $resumo['despesas']['total'] += $valor;
        }

        // Calcula total de dinheiro (vendas à vista + recebimentos de parcelas)
        $resumo['recebimentos']['total_dinheiro_completo'] =
            $resumo['vendas']['total_dinheiro'] + $resumo['recebimentos']['total_dinheiro'];

        // Calcula valor líquido em dinheiro (total dinheiro - despesas)
        $resumo['recebimentos']['total_dinheiro_liquido'] =
            $resumo['recebimentos']['total_dinheiro_completo'] - $resumo['despesas']['total'];

        return $resumo;
    }

    /**
     * Calcula resumo geral de todas as cidades
     */
    private function calcularResumoGeral($cidades)
    {
        $resumoGeral = [
            'vendas' => [
                'total_dinheiro' => 0,
                'total_pix' => 0,
                'total_cartao' => 0,
                'total_crediario' => 0,
                'total_geral' => 0
            ],
            'recebimentos' => [
                'total_dinheiro' => 0,
                'total_pix' => 0,
                'total_cartao' => 0,
                'total_geral' => 0,
                'total_dinheiro_liquido' => 0
            ],
            'despesas' => [
                'total' => 0
            ]
        ];

        foreach ($cidades as $cidade) {
            $resumoCidade = $cidade['resumo_cidade'];

            $resumoGeral['vendas']['total_dinheiro'] += $resumoCidade['vendas']['total_dinheiro'];
            $resumoGeral['vendas']['total_pix'] += $resumoCidade['vendas']['total_pix'];
            $resumoGeral['vendas']['total_cartao'] += $resumoCidade['vendas']['total_cartao'];
            $resumoGeral['vendas']['total_crediario'] += $resumoCidade['vendas']['total_crediario'];

            $resumoGeral['recebimentos']['total_dinheiro'] += $resumoCidade['recebimentos']['total_dinheiro'];
            $resumoGeral['recebimentos']['total_pix'] += $resumoCidade['recebimentos']['total_pix'];
            $resumoGeral['recebimentos']['total_cartao'] += $resumoCidade['recebimentos']['total_cartao'];

            $resumoGeral['despesas']['total'] += $resumoCidade['despesas']['total'];
        }

        $resumoGeral['vendas']['total_geral'] = array_sum($resumoGeral['vendas']);
        $resumoGeral['recebimentos']['total_geral'] = array_sum($resumoGeral['recebimentos']);
        
        // Calcula total de dinheiro (vendas à vista + recebimentos de parcelas)
        $resumoGeral['recebimentos']['total_dinheiro_completo'] =
            $resumoGeral['vendas']['total_dinheiro'] + $resumoGeral['recebimentos']['total_dinheiro'];
        
        $resumoGeral['recebimentos']['total_dinheiro_liquido'] =
            $resumoGeral['recebimentos']['total_dinheiro_completo'] - $resumoGeral['despesas']['total'];

        return $resumoGeral;
    }

    /**
     * Verifica se usuário pode ver dados de um vendedor específico
     */
    private function usuarioPodeVerVendedor($usuario, $vendedorId)
    {
        if ($usuario->nivel === 'administrador') {
            return true;
        }

        return $usuario->id == $vendedorId;
    }

    /**
     * Obtém nome da cidade pelo ID
     */
    private function obterNomeCidade($cidadeId)
    {
        $cidade = DB::table('cidades')->where('id', $cidadeId)->first();
        return $cidade ? $cidade->cidade : null;
    }

    /**
     * Verifica se uma tabela existe no banco de dados
     */
    private function tabelaExiste($nomeTabela)
    {
        try {
            DB::select("SELECT 1 FROM {$nomeTabela} LIMIT 1");
            return true;
        } catch (\Exception $e) {
            Log::warning("Tabela não encontrada: {$nomeTabela}", [
                'erro' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Valida se o usuário e cidade são válidos
     */
    private function validarUsuarioECidade($usuario)
    {
        if (!$usuario) {
            throw new \Exception('Usuário não autenticado.');
        }

        if (!$usuario->cidade) {
            throw new \Exception('Usuário não possui cidade configurada.');
        }

        $cidade = DB::table('cidades')->where('id', $usuario->cidade)->first();
        if (!$cidade) {
            throw new \Exception('Cidade do usuário não encontrada no sistema.');
        }

        return $cidade;
    }

    /**
     * Executa consulta com tratamento de erro
     */
    private function executarConsultaSegura($callback, $contexto = '')
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            Log::error("Erro na consulta de fluxo de caixa: {$contexto}", [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw com mensagem mais amigável se necessário
            if (str_contains($e->getMessage(), "doesn't exist")) {
                throw new \Exception("Tabela de dados não encontrada. Verifique a configuração da cidade.");
            }

            throw $e;
        }
    }

    /**
     * Limpa cache relacionado ao fluxo de caixa
     */
    public function limparCache($usuarioId = null)
    {
        if ($usuarioId) {
            // Limpar cache específico do usuário
            $pattern = "fluxo_*_{$usuarioId}_*";
        } else {
            // Limpar todo cache do fluxo de caixa
            $pattern = "fluxo_*";
        }

        // Laravel não tem um método nativo para limpar por pattern
        // Em produção, considere usar Redis com SCAN
        Cache::flush(); // Por simplicidade, limpa todo o cache

        Log::info('Cache do fluxo de caixa limpo', ['usuario' => $usuarioId]);
    }

    /**
     * Obtém estatísticas de performance da consulta
     */
    public function obterEstatisticasPerformance($dataInicio, $dataFim, $usuario)
    {
        $inicio = microtime(true);

        $dados = $this->obterDadosFluxoGeral($dataInicio, $dataFim, $usuario);

        $tempoExecucao = microtime(true) - $inicio;

        $estatisticas = [
            'tempo_execucao_segundos' => round($tempoExecucao, 3),
            'total_cidades' => count($dados['cidades'] ?? []),
            'total_vendedores' => 0,
            'total_vendas' => 0,
            'total_recebimentos' => 0,
        ];

        foreach ($dados['cidades'] ?? [] as $cidade) {
            $estatisticas['total_vendedores'] += count($cidade['vendedores'] ?? []);

            foreach ($cidade['vendedores'] ?? [] as $vendedor) {
                $estatisticas['total_vendas'] += count($vendedor['vendas'] ?? []);
                $estatisticas['total_recebimentos'] += count($vendedor['recebimentos'] ?? []);
            }
        }

        return $estatisticas;
    }
}
