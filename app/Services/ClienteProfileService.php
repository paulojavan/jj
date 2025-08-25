<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\Parcela;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ClienteProfileService
{
    /**
     * Gera perfil detalhado de compras do cliente
     */
    public function gerarPerfilCompras($clienteId)
    {
        try {
            $cliente = cliente::findOrFail($clienteId);
            
            // Buscar todos os tickets do cliente, excluindo devoluções
            $tickets = Ticket::where('id_cliente', $clienteId)
                ->whereDoesntHave('parcelasRelacao', function ($query) {
                    $query->where('status', 'devolucao');
                })
                ->orderBy('data', 'asc')
                ->get();

            if ($tickets->isEmpty()) {
                return $this->perfilVazio('compras');
            }

            // Calcular estatísticas básicas
            $totalCompras = $tickets->count();
            $valorTotalGasto = $tickets->sum('valor');
            $ticketMedio = $totalCompras > 0 ? $valorTotalGasto / $totalCompras : 0;

            // Calcular frequência de compras
            $primeiraCompra = $tickets->first()->data;
            $ultimaCompra = $tickets->last()->data;
            $mesesAtivo = Carbon::parse($primeiraCompra)->diffInMonths(Carbon::parse($ultimaCompra)) + 1;
            $comprasPorMes = $mesesAtivo > 0 ? $totalCompras / $mesesAtivo : 0;

            // Analisar produtos preferidos
            $produtosPreferidos = $this->analisarProdutosPreferidos($clienteId);

            // Analisar sazonalidade
            $sazonalidade = $this->analisarSazonalidade($tickets);

            // Evolução de gastos
            $evolucaoGastos = $this->analisarEvolucaoGastos($tickets);

            return [
                'total_compras' => $totalCompras,
                'valor_total_gasto' => $valorTotalGasto,
                'ticket_medio' => $ticketMedio,
                'frequencia_compras' => [
                    'total_meses_ativo' => $mesesAtivo,
                    'compras_por_mes' => round($comprasPorMes, 2),
                    'ultima_compra' => Carbon::parse($ultimaCompra),
                    'primeira_compra' => Carbon::parse($primeiraCompra)
                ],
                'produtos_preferidos' => $produtosPreferidos,
                'sazonalidade' => $sazonalidade,
                'evolucao_gastos' => $evolucaoGastos
            ];

        } catch (Exception $e) {
            \Log::error('Erro ao gerar perfil de compras: ' . $e->getMessage());
            return $this->perfilVazio('compras');
        }
    }

    /**
     * Analisa produtos preferidos do cliente
     */
    private function analisarProdutosPreferidos($clienteId)
    {
        try {
            // Buscar parcelas para determinar as tabelas de vendas, excluindo devoluções
            $parcelas = Parcela::where('id_cliente', $clienteId)
                ->where('status', '!=', 'devolucao')
                ->get();
            
            $marcas = [];
            $grupos = [];
            $numeracoes = [];

            foreach ($parcelas as $parcela) {
                if (!$parcela->bd) continue;

                $tabelaVendas = $this->determinarTabelaVendas($parcela->bd);
                if (!$tabelaVendas) continue;

                // Buscar vendas do ticket
                $vendas = DB::table($tabelaVendas)
                    ->where('ticket', $parcela->ticket)
                    ->get();

                foreach ($vendas as $venda) {
                    // Buscar informações do produto
                    $produto = DB::table('produtos')
                        ->where('id', $venda->id_produto)
                        ->first();

                    if ($produto) {
                        // Contar marcas
                        if (!empty($produto->marca)) {
                            $marcas[$produto->marca] = ($marcas[$produto->marca] ?? 0) + 1;
                        }

                        // Contar grupos
                        if (!empty($produto->grupo)) {
                            $grupos[$produto->grupo] = ($grupos[$produto->grupo] ?? 0) + 1;
                        }

                        // Contar numerações
                        if (!empty($venda->numeracao)) {
                            $numeracoes[$venda->numeracao] = ($numeracoes[$venda->numeracao] ?? 0) + 1;
                        }
                    }
                }
            }

            // Ordenar por frequência e pegar os top 5
            arsort($marcas);
            arsort($grupos);
            arsort($numeracoes);

            return [
                'marcas' => array_slice($marcas, 0, 5, true),
                'grupos' => array_slice($grupos, 0, 5, true),
                'numeracoes' => array_slice($numeracoes, 0, 5, true)
            ];

        } catch (Exception $e) {
            \Log::error('Erro ao analisar produtos preferidos: ' . $e->getMessage());
            return [
                'marcas' => [],
                'grupos' => [],
                'numeracoes' => []
            ];
        }
    }

    /**
     * Analisa sazonalidade das compras
     */
    private function analisarSazonalidade($tickets)
    {
        $meses = [];
        
        foreach ($tickets as $ticket) {
            $mes = Carbon::parse($ticket->data)->format('m');
            $meses[$mes] = ($meses[$mes] ?? 0) + 1;
        }

        // Ordenar por mês
        ksort($meses);

        // Converter números dos meses para nomes
        $nomesMeses = [
            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
            '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
            '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
            '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
        ];

        $sazonalidade = [];
        foreach ($meses as $mes => $quantidade) {
            $sazonalidade[$nomesMeses[$mes]] = $quantidade;
        }

        return $sazonalidade;
    }

    /**
     * Analisa evolução de gastos ao longo do tempo
     */
    private function analisarEvolucaoGastos($tickets)
    {
        $gastosPorMes = [];

        foreach ($tickets as $ticket) {
            $mesAno = Carbon::parse($ticket->data)->format('Y-m');
            $gastosPorMes[$mesAno] = ($gastosPorMes[$mesAno] ?? 0) + $ticket->valor;
        }

        // Ordenar por data
        ksort($gastosPorMes);

        // Calcular tendência (últimos 6 meses vs 6 meses anteriores)
        $valores = array_values($gastosPorMes);
        $totalMeses = count($valores);
        
        if ($totalMeses >= 6) {
            $ultimosSeisMeses = array_slice($valores, -6);
            $seisMesesAnteriores = array_slice($valores, -12, 6);
            
            $mediaUltimos = array_sum($ultimosSeisMeses) / 6;
            $mediaAnteriores = count($seisMesesAnteriores) > 0 ? array_sum($seisMesesAnteriores) / count($seisMesesAnteriores) : 0;
            
            $tendencia = $mediaAnteriores > 0 ? (($mediaUltimos - $mediaAnteriores) / $mediaAnteriores) * 100 : 0;
        } else {
            $tendencia = 0;
        }

        return [
            'gastos_por_mes' => $gastosPorMes,
            'tendencia_percentual' => round($tendencia, 2)
        ];
    }

    /**
     * Determina a tabela de vendas baseada no campo bd
     */
    private function determinarTabelaVendas($bd)
    {
        $mapeamento = [
            'tabira' => 'vendas_tabira',
            'princesa' => 'vendas_princesa',
            'vendas_tabira' => 'vendas_tabira',
            'vendas_princesa' => 'vendas_princesa'
        ];

        $bdLower = strtolower($bd);
        return $mapeamento[$bdLower] ?? null;
    }

    /**
     * Gera perfil detalhado de pagamentos do cliente
     */
    public function gerarPerfilPagamentos($clienteId)
    {
        try {
            $cliente = cliente::findOrFail($clienteId);
            
            // Buscar todas as parcelas do cliente, excluindo devoluções
            $parcelas = Parcela::where('id_cliente', $clienteId)
                ->where('status', '!=', 'devolucao')
                ->orderBy('data_vencimento', 'asc')
                ->get();

            if ($parcelas->isEmpty()) {
                return $this->perfilVazio('pagamentos');
            }

            // Calcular pontualidade
            $pontualidade = $this->calcularPontualidade($parcelas);

            // Calcular inadimplência
            $inadimplencia = $this->calcularInadimplencia($parcelas);

            // Analisar comportamento de pagamento
            $comportamentoPagamento = $this->analisarComportamentoPagamento($parcelas);

            // Calcular risco
            $riscoCalculado = $this->calcularIndicadoresRisco($pontualidade, $inadimplencia, $comportamentoPagamento);

            return [
                'pontualidade' => $pontualidade,
                'inadimplencia' => $inadimplencia,
                'comportamento_pagamento' => $comportamentoPagamento,
                'risco_calculado' => $riscoCalculado
            ];

        } catch (Exception $e) {
            \Log::error('Erro ao gerar perfil de pagamentos: ' . $e->getMessage());
            return $this->perfilVazio('pagamentos');
        }
    }

    /**
     * Calcula pontualidade de pagamentos
     */
    private function calcularPontualidade($parcelas)
    {
        $parcelasPagas = $parcelas->where('data_pagamento', '!=', null);
        $totalPagas = $parcelasPagas->count();

        if ($totalPagas === 0) {
            return [
                'percentual_pontual' => 0,
                'atraso_medio_dias' => 0,
                'maior_atraso_dias' => 0
            ];
        }

        $pagamentosPontuais = 0;
        $totalDiasAtraso = 0;
        $maiorAtraso = 0;

        foreach ($parcelasPagas as $parcela) {
            $vencimento = Carbon::parse($parcela->data_vencimento);
            $pagamento = Carbon::parse($parcela->data_pagamento);
            
            // Calcular dias de atraso: positivo = atraso, negativo = antecipado
            if ($pagamento->gt($vencimento)) {
                // Pagamento após vencimento = atraso
                $diasAtraso = $vencimento->diffInDays($pagamento);
                $totalDiasAtraso += $diasAtraso;
                $maiorAtraso = max($maiorAtraso, $diasAtraso);
                $pagamentosAtrasados = ($pagamentosAtrasados ?? 0) + 1;
            } else {
                // Pagamento antes ou no vencimento = pontual
                $pagamentosPontuais++;
            }
        }
        
        $pagamentosAtrasados = $pagamentosAtrasados ?? 0;

        $percentualPontual = ($pagamentosPontuais / $totalPagas) * 100;
        $atrasoMedio = $pagamentosAtrasados > 0 ? $totalDiasAtraso / $pagamentosAtrasados : 0;

        return [
            'percentual_pontual' => round($percentualPontual, 2),
            'atraso_medio_dias' => round($atrasoMedio, 1),
            'maior_atraso_dias' => $maiorAtraso
        ];
    }

    /**
     * Calcula inadimplência
     */
    private function calcularInadimplencia($parcelas)
    {
        $hoje = Carbon::now();
        $parcelasVencidas = $parcelas->filter(function ($parcela) use ($hoje) {
            return Carbon::parse($parcela->data_vencimento)->lt($hoje) 
                && is_null($parcela->data_pagamento)
                && $parcela->status !== 'devolucao';
        });

        $totalParcelas = $parcelas->count();
        $parcelasEmAtraso = $parcelasVencidas->count();
        $valorEmAtraso = $parcelasVencidas->sum('valor');
        
        $percentualInadimplencia = $totalParcelas > 0 ? ($parcelasEmAtraso / $totalParcelas) * 100 : 0;

        return [
            'parcelas_em_atraso' => $parcelasEmAtraso,
            'valor_em_atraso' => $valorEmAtraso,
            'percentual_inadimplencia' => round($percentualInadimplencia, 2)
        ];
    }

    /**
     * Analisa comportamento de pagamento
     */
    private function analisarComportamentoPagamento($parcelas)
    {
        $parcelasPagas = $parcelas->where('data_pagamento', '!=', null);
        
        if ($parcelasPagas->isEmpty()) {
            return [
                'metodos_preferidos' => [],
                'valor_medio_parcela' => 0,
                'prazo_medio_pagamento' => 0
            ];
        }

        // Analisar métodos de pagamento preferidos
        $metodos = [];
        foreach ($parcelasPagas as $parcela) {
            if (!empty($parcela->metodo)) {
                $metodos[$parcela->metodo] = ($metodos[$parcela->metodo] ?? 0) + 1;
            }
        }
        arsort($metodos);

        // Calcular valor médio das parcelas
        $valorMedioParcela = $parcelasPagas->avg('valor_parcela');

        // Calcular prazo médio de pagamento (dias após vencimento)
        $totalDias = 0;
        $contador = 0;
        
        foreach ($parcelasPagas as $parcela) {
            $vencimento = Carbon::parse($parcela->data_vencimento);
            $pagamento = Carbon::parse($parcela->data_pagamento);
            $diasAposPagamento = $pagamento->diffInDays($vencimento, false);
            
            $totalDias += max(0, $diasAposPagamento); // Só conta se pagou após o vencimento
            $contador++;
        }

        $prazoMedioPagamento = $contador > 0 ? $totalDias / $contador : 0;

        return [
            'metodos_preferidos' => $metodos,
            'valor_medio_parcela' => round($valorMedioParcela, 2),
            'prazo_medio_pagamento' => round($prazoMedioPagamento, 1)
        ];
    }

    /**
     * Calcula indicadores de risco e recomendação de limite
     */
    public function calcularIndicadoresRisco($pontualidade, $inadimplencia, $comportamentoPagamento)
    {
        // Calcular score baseado em múltiplos fatores
        $score = 100; // Começar com score máximo

        // Penalizar por pontualidade baixa
        $penalizacaoPontualidade = (100 - $pontualidade['percentual_pontual']) * 0.4;
        $score -= $penalizacaoPontualidade;

        // Penalizar por inadimplência
        $penalizacaoInadimplencia = $inadimplencia['percentual_inadimplencia'] * 0.6;
        $score -= $penalizacaoInadimplencia;

        // Penalizar por atraso médio alto
        if ($pontualidade['atraso_medio_dias'] > 30) {
            $score -= 20;
        } elseif ($pontualidade['atraso_medio_dias'] > 15) {
            $score -= 10;
        } elseif ($pontualidade['atraso_medio_dias'] > 7) {
            $score -= 5;
        }

        // Garantir que o score não seja negativo
        $score = max(0, $score);

        // Determinar classificação
        if ($score >= 80) {
            $classificacao = 'baixo';
            $multiplicadorLimite = 3.0;
        } elseif ($score >= 60) {
            $classificacao = 'medio';
            $multiplicadorLimite = 2.0;
        } else {
            $classificacao = 'alto';
            $multiplicadorLimite = 1.0;
        }

        // Calcular recomendação de limite baseada no valor médio das parcelas
        $valorMedioParcela = $comportamentoPagamento['valor_medio_parcela'];
        $recomendacaoLimite = $valorMedioParcela * $multiplicadorLimite;

        // Ajustar recomendação baseada na inadimplência
        if ($inadimplencia['valor_em_atraso'] > 0) {
            $recomendacaoLimite *= 0.5; // Reduzir pela metade se há valores em atraso
        }

        return [
            'score' => round($score),
            'classificacao' => $classificacao,
            'recomendacao_limite' => round($recomendacaoLimite, 2)
        ];
    }

    /**
     * Retorna perfil vazio quando não há dados
     */
    private function perfilVazio($tipo)
    {
        if ($tipo === 'compras') {
            return [
                'total_compras' => 0,
                'valor_total_gasto' => 0,
                'ticket_medio' => 0,
                'frequencia_compras' => [
                    'total_meses_ativo' => 0,
                    'compras_por_mes' => 0,
                    'ultima_compra' => null,
                    'primeira_compra' => null
                ],
                'produtos_preferidos' => [
                    'marcas' => [],
                    'grupos' => [],
                    'numeracoes' => []
                ],
                'sazonalidade' => [],
                'evolucao_gastos' => [
                    'gastos_por_mes' => [],
                    'tendencia_percentual' => 0
                ]
            ];
        } elseif ($tipo === 'pagamentos') {
            return [
                'pontualidade' => [
                    'percentual_pontual' => 0,
                    'atraso_medio_dias' => 0,
                    'maior_atraso_dias' => 0
                ],
                'inadimplencia' => [
                    'parcelas_em_atraso' => 0,
                    'valor_em_atraso' => 0,
                    'percentual_inadimplencia' => 0
                ],
                'comportamento_pagamento' => [
                    'metodos_preferidos' => [],
                    'valor_medio_parcela' => 0,
                    'prazo_medio_pagamento' => 0
                ],
                'risco_calculado' => [
                    'score' => 0,
                    'classificacao' => 'alto',
                    'recomendacao_limite' => 0
                ]
            ];
        }

        return [];
    }
}