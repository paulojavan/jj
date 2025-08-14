<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CarrinhoController extends Controller
{
    public function adicionar(Request $request, $id)
    {
        $produto = \App\Models\Produto::findOrFail($id);
        $numeracao = $request->input('numeracao');

        if (!$numeracao) {
            return redirect()->back()->with('error', 'Por favor, selecione uma numeração.');
        }

        $carrinho = Session::get('carrinho', []);

        $itemId = $id . '-' . $numeracao;

        // VERIFICA SE O ITEM JÁ ESTÁ NO CARRINHO
        if (isset($carrinho[$itemId])) {
            return redirect()->back()->with('error', 'Este item já está no seu carrinho.');
        }

        // Se não estiver no carrinho, verifica o estoque para a numeração específica.
        $user = \Illuminate\Support\Facades\Auth::user(); // Necessário para nome da tabela de estoque
        // Busca o nome da cidade a partir do ID da cidade do usuário
        $nomeCidade = DB::table('cidades')->where('id', $user->cidade)->value('cidade');
        if (!$nomeCidade) {
            return redirect()->back()->with('error', 'Cidade do usuário não configurada ou inválida.');
        }
        $nomeTabelaEstoque = 'estoque_' . strtolower(str_replace(' ', '_', $nomeCidade));
        $quantidadeDisponivelParaNumeracao = DB::table($nomeTabelaEstoque)
            ->where('id_produto', $produto->id)
            ->where('numero', $numeracao)
            ->value('quantidade');

        if (!$quantidadeDisponivelParaNumeracao || $quantidadeDisponivelParaNumeracao <= 0) {
            return redirect()->back()->with('error', 'Não há estoque disponível para esta numeração no momento.');
        }

        // Adiciona o produto ao carrinho com quantidade 1
        $carrinho[$itemId] = [
            'id' => $id,
            'nome' => $produto->produto,
            'preco' => $produto->preco,
            'numeracao' => $numeracao,
            'quantidade' => 1,
            'foto' => $produto->foto
            // 'max_quantidade_disponivel' => $quantidadeDisponivelParaNumeracao // Opcional: guardar para uso futuro na atualização de quantidade
        ];

        Session::put('carrinho', $carrinho);
        return redirect()->back()->with('success', 'Produto adicionado ao carrinho!');
    }

    public function exibir()
    {
        $carrinho = Session::get('carrinho', []);
        $total = 0;
        $user = \Illuminate\Support\Facades\Auth::user();
        $nomeTabelaEstoque = null; // Inicializa como null

        // Busca o nome da cidade a partir do ID da cidade do usuário
        $nomeCidadeDb = DB::table('cidades')->where('id', $user->cidade)->value('cidade');

        if ($nomeCidadeDb) {
            $nomeTabelaEstoque = 'estoque_' . strtolower(str_replace(' ', '_', $nomeCidadeDb));
        } else {
            // Log do erro ou tratamento alternativo se a cidade não for encontrada
            \Illuminate\Support\Facades\Log::error("CarrinhoController@exibir: Cidade com ID {$user->cidade} não encontrada para o usuário {$user->id}.");
            // A view 'carrinho.index' precisará lidar com a possibilidade de não ter informações de estoque
            // ou podemos passar uma flag/mensagem de erro. Se $nomeTabelaEstoque permanecer null,
            // as buscas de estoque no loop abaixo resultarão em 0 para quantidadeDisponivel.
            Session::flash('warning', 'Não foi possível verificar o estoque para todos os itens devido a um problema na configuração da cidade.');
        }

        foreach ($carrinho as $itemId => $item) {
            $total += $item['preco'] * $item['quantidade'];

            // Busca a quantidade disponível no estoque para cada item
            $quantidadeDisponivel = 0; // Padrão para 0
            if ($nomeTabelaEstoque) { // Só tenta buscar se a tabela de estoque foi definida
                $quantidadeDisponivel = DB::table($nomeTabelaEstoque)
                    ->where('id_produto', $item['id'])
                    ->where('numero', $item['numeracao'])
                    ->value('quantidade');
            }


            // Armazena a quantidade disponível na sessão para uso no select
            Session::put('quantidade_disponivel_' . $itemId, $quantidadeDisponivel ?: 0);
        }

        $descontos = DB::table('descontos')->first();

        // Armazena os descontos na sessão
        Session::put('descontos', $descontos);

        // Busca usuários que podem ser vendedores atendentes
        $vendedores = \App\Models\User::where('cidade', $user->cidade)
                                    ->where('status', 'ativo')
                                    ->where('nivel', 'usuario')
                                    ->get();

        // Recupera nome do cliente e vendedor atendente da sessão
        $clienteVendedor = Session::get('cliente_vendedor', []);

        // Calcula informações do troco
        $dadosTroco = $this->calcularTroco($total);

        // Passa os valores de desconto, a lista de vendedores e os dados do cliente/vendedor para a view
        return view('carrinho.index', compact('carrinho', 'total', 'descontos', 'vendedores', 'clienteVendedor', 'dadosTroco'));
    }

    public function remover($itemId)
    {
        $carrinho = Session::get('carrinho', []);

        if (isset($carrinho[$itemId])) {
            unset($carrinho[$itemId]);
            Session::put('carrinho', $carrinho);
            
            // Se o carrinho ficou vazio, limpa as sessões de desconto
            if (empty($carrinho)) {
                Session::forget('descontos_aplicados');
                Session::forget('cliente_vendedor');
                Session::forget('valor_dinheiro_recebido');
            }
        }

        return redirect()->back()->with('success', 'Item removido do carrinho!');
    }

    public function atualizar(Request $request, $itemId)
    {
        $carrinho = Session::get('carrinho', []);
        $quantidade = $request->input('quantidade');

        if (isset($carrinho[$itemId])) {
            if ($quantidade > 0) {
                $carrinho[$itemId]['quantidade'] = $quantidade;
            } else {
                unset($carrinho[$itemId]);
            }
            Session::put('carrinho', $carrinho);
            
            // Se o carrinho ficou vazio, limpa as sessões de desconto
            if (empty($carrinho)) {
                Session::forget('descontos_aplicados');
                Session::forget('cliente_vendedor');
                Session::forget('valor_dinheiro_recebido');
            }
        }

        return redirect()->back()->with('success', 'Carrinho atualizado!');
    }

    public function limpar()
    {
        Session::forget('carrinho');
        Session::forget('descontos_aplicados');
        Session::forget('cliente_vendedor');
        Session::forget('valor_dinheiro_recebido');
        return redirect()->back()->with('success', 'Carrinho esvaziado!');
    }

    public function aplicarDesconto(Request $request)
    {
        $tipoDesconto = $request->input('tipo_desconto');
        $total = $this->calcularTotalCarrinho();
        
        // Processa os descontos baseado no tipo selecionado
        $descontosAplicados = $this->processarDescontos($tipoDesconto, $total, $request);
        
        // Armazena informações do cliente e vendedor
        $this->armazenarDadosClienteVendedor($request);
        
        // Armazena valor do dinheiro recebido
        $this->armazenarValorDinheiro($request);
        
        $mensagem = $this->obterMensagemSucesso($tipoDesconto);
        
        return redirect()->back()->with('success', $mensagem);
    }

    /**
     * Calcula o total do carrinho
     */
    private function calcularTotalCarrinho()
    {
        $carrinho = Session::get('carrinho', []);
        $total = 0;

        foreach ($carrinho as $item) {
            $total += $item['preco'] * $item['quantidade'];
        }

        return $total;
    }

    /**
     * Processa os descontos baseado no tipo selecionado
     */
    private function processarDescontos($tipoDesconto, $total, Request $request)
    {
        switch ($tipoDesconto) {
            case 'sem desconto':
                Session::forget('descontos_aplicados');
                return null;
                
            case 'manual':
                return $this->aplicarDescontoManual($request);
                
            default:
                return $this->aplicarDescontoAutomatico($tipoDesconto, $total);
        }
    }

    /**
     * Aplica desconto manual baseado nos valores inseridos pelo usuário
     */
    private function aplicarDescontoManual(Request $request)
    {
        $descontosAplicados = [
            'avista' => $this->formatarValorMonetario($request->input('valor_avista_manual')),
            'pix' => $this->formatarValorMonetario($request->input('valor_pix_manual')),
            'cartao' => $this->formatarValorMonetario($request->input('valor_cartao_manual')),
            'crediario' => $this->formatarValorMonetario($request->input('valor_crediario_manual')),
            'dinheiro' => '0,00',
            'tipo_selecionado' => 'manual',
            'modo_manual' => true
        ];

        Session::put('descontos_aplicados', $descontosAplicados);
        return $descontosAplicados;
    }

    /**
     * Aplica desconto automático baseado nas configurações do sistema
     */
    private function aplicarDescontoAutomatico($tipoDesconto, $total)
    {
        $descontos = Session::get('descontos');
        
        if (!$descontos) {
            Session::forget('descontos_aplicados');
            return null;
        }

        $descontosAplicados = $this->inicializarDescontosZerados($tipoDesconto);
        $configuracaoDesconto = $this->obterConfiguracaoDesconto($tipoDesconto, $descontos);
        
        // Aplica o desconto (mesmo se for 0%)
        $totalComDesconto = $total * (1 - ($configuracaoDesconto['percentual'] / 100));
        $descontosAplicados[$configuracaoDesconto['campo']] = number_format($totalComDesconto, 2, ',', '.');

        Session::put('descontos_aplicados', $descontosAplicados);
        return $descontosAplicados;
    }

    /**
     * Inicializa array de descontos zerados
     */
    private function inicializarDescontosZerados($tipoDesconto)
    {
        return [
            'avista' => '0,00',
            'pix' => '0,00',
            'cartao' => '0,00',
            'crediario' => '0,00',
            'dinheiro' => '0,00',
            'tipo_selecionado' => $tipoDesconto,
            'modo_manual' => false
        ];
    }

    /**
     * Obtém a configuração de desconto baseada no tipo
     */
    private function obterConfiguracaoDesconto($tipoDesconto, $descontos)
    {
        $configuracoes = [
            'credito' => ['percentual' => $descontos->credito ?? 0, 'campo' => 'cartao'],
            'debito' => ['percentual' => $descontos->debito ?? 0, 'campo' => 'cartao'],
            'avista' => ['percentual' => $descontos->avista ?? 0, 'campo' => 'avista'],
            'pix' => ['percentual' => $descontos->pix ?? 0, 'campo' => 'pix'],
            'crediario' => ['percentual' => $descontos->crediario ?? 0, 'campo' => 'crediario']
        ];

        return $configuracoes[$tipoDesconto] ?? ['percentual' => 0, 'campo' => $tipoDesconto];
    }

    /**
     * Armazena dados do cliente e vendedor na sessão
     */
    private function armazenarDadosClienteVendedor(Request $request)
    {
        $nomeCliente = $request->input('nome_cliente');
        $vendedorAtendente = $request->input('vendedor_atendente');

        if ($nomeCliente || $vendedorAtendente) {
            Session::put('cliente_vendedor', [
                'nome_cliente' => $nomeCliente,
                'vendedor_atendente_id' => $vendedorAtendente
            ]);
        }
    }

    /**
     * Armazena valor do dinheiro recebido na sessão
     */
    private function armazenarValorDinheiro(Request $request)
    {
        $valorDinheiroRecebido = $request->input('valor_dinheiro');

        if (!empty($valorDinheiroRecebido)) {
            Session::put('valor_dinheiro_recebido', $valorDinheiroRecebido);
        } else {
            Session::forget('valor_dinheiro_recebido');
        }
    }

    /**
     * Obtém a mensagem de sucesso baseada no tipo de desconto
     */
    private function obterMensagemSucesso($tipoDesconto)
    {
        $mensagens = [
            'manual' => 'Valores manuais aplicados!',
            'sem desconto' => 'Descontos removidos!',
            'default' => 'Desconto aplicado!'
        ];

        return $mensagens[$tipoDesconto] ?? $mensagens['default'];
    }

    /**
     * Calcula o troco baseado no valor recebido e total final
     */
    private function calcularTroco($totalOriginal)
    {
        $valorDinheiroRecebido = Session::get('valor_dinheiro_recebido', '');
        $descontosAplicados = Session::get('descontos_aplicados', []);
        
        // Calcula o total final considerando descontos
        $totalFinal = $this->obterTotalFinal($totalOriginal, $descontosAplicados);
        
        // Converte valor do dinheiro recebido para float
        $dinheiroRecebidoFloat = $this->converterValorMonetarioParaFloat($valorDinheiroRecebido);
        
        // Calcula o troco
        $valorTroco = $dinheiroRecebidoFloat - $totalFinal;
        
        return [
            'valor_recebido' => $valorDinheiroRecebido,
            'valor_recebido_float' => $dinheiroRecebidoFloat,
            'total_final' => $totalFinal,
            'troco' => $valorTroco,
            'troco_formatado' => number_format(abs($valorTroco), 2, ',', '.'),
            'tem_troco' => !empty($valorDinheiroRecebido) && $dinheiroRecebidoFloat > 0,
            'troco_positivo' => $valorTroco >= 0,
            'valor_faltante' => $valorTroco < 0 ? abs($valorTroco) : 0
        ];
    }

    /**
     * Obtém o total final considerando os descontos aplicados
     */
    private function obterTotalFinal($totalOriginal, $descontosAplicados)
    {
        if (empty($descontosAplicados)) {
            return $totalOriginal;
        }

        $tipoSelecionado = $descontosAplicados['tipo_selecionado'] ?? null;
        
        if (!$tipoSelecionado) {
            return $totalOriginal;
        }

        // Para modo manual, calcula a soma de todos os valores
        if ($tipoSelecionado === 'manual') {
            return $this->calcularTotalModoManual($descontosAplicados);
        }

        // Para descontos automáticos
        return $this->calcularTotalDescontoAutomatico($tipoSelecionado, $descontosAplicados, $totalOriginal);
    }

    /**
     * Calcula total no modo manual (soma de todos os valores de pagamento)
     */
    private function calcularTotalModoManual($descontosAplicados)
    {
        $campos = ['avista', 'pix', 'cartao', 'crediario'];
        $total = 0;

        foreach ($campos as $campo) {
            if (isset($descontosAplicados[$campo])) {
                $total += $this->converterValorMonetarioParaFloat($descontosAplicados[$campo]);
            }
        }

        return $total;
    }

    /**
     * Calcula total para desconto automático
     */
    private function calcularTotalDescontoAutomatico($tipoSelecionado, $descontosAplicados, $totalOriginal)
    {
        // Mapeia tipos de desconto para campos na sessão
        $mapeamentoCampos = [
            'credito' => 'cartao',
            'debito' => 'cartao',
            'avista' => 'avista',
            'pix' => 'pix',
            'crediario' => 'crediario'
        ];

        $campo = $mapeamentoCampos[$tipoSelecionado] ?? $tipoSelecionado;
        
        if (isset($descontosAplicados[$campo])) {
            return $this->converterValorMonetarioParaFloat($descontosAplicados[$campo]);
        }

        return $totalOriginal;
    }

    /**
     * Converte valor monetário formatado (ex: "1.234,56") para float
     */
    private function converterValorMonetarioParaFloat($valor)
    {
        if (empty($valor)) {
            return 0.0;
        }

        // Remove "R$" e espaços
        $valor = str_replace(['R$', ' '], '', $valor);
        
        // Se contém vírgula, trata como formato brasileiro (1.234,56)
        if (strpos($valor, ',') !== false) {
            // Remove pontos (separadores de milhares) e substitui vírgula por ponto
            $valorLimpo = str_replace(['.', ','], ['', '.'], $valor);
        } else {
            // Se não tem vírgula, assume formato americano ou número inteiro
            $valorLimpo = $valor;
        }
        
        return floatval($valorLimpo);
    }

    /**
     * Valida se o valor do dinheiro recebido é suficiente
     */
    public function validarPagamento($valorRecebido, $totalFinal)
    {
        $valorRecebidoFloat = $this->converterValorMonetarioParaFloat($valorRecebido);
        
        return [
            'valido' => $valorRecebidoFloat >= $totalFinal,
            'valor_recebido' => $valorRecebidoFloat,
            'diferenca' => $valorRecebidoFloat - $totalFinal,
            'valor_faltante' => max(0, $totalFinal - $valorRecebidoFloat)
        ];
    }

    /**
     * Formata valor para exibição monetária brasileira
     */
    private function formatarValorBrasileiro($valor)
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    /**
     * Obtém resumo completo do troco para relatórios
     */
    public function obterResumoTroco()
    {
        $carrinho = Session::get('carrinho', []);
        if (empty($carrinho)) {
            return null;
        }

        $total = $this->calcularTotalCarrinho();
        $dadosTroco = $this->calcularTroco($total);
        
        return [
            'total_original' => $this->formatarValorBrasileiro($total),
            'total_final' => $this->formatarValorBrasileiro($dadosTroco['total_final']),
            'valor_recebido' => $dadosTroco['valor_recebido'],
            'troco' => $this->formatarValorBrasileiro($dadosTroco['troco']),
            'status' => $dadosTroco['troco_positivo'] ? 'OK' : 'INSUFICIENTE',
            'desconto_aplicado' => $total - $dadosTroco['total_final'],
            'desconto_formatado' => $this->formatarValorBrasileiro($total - $dadosTroco['total_final'])
        ];
    }

    private function formatarValorMonetario($valor)
    {
        if (empty($valor)) {
            return '0,00';
        }
        
        // Remove caracteres não numéricos exceto vírgula
        $valor = preg_replace('/[^\d,]/', '', $valor);
        
        // Se não tem vírgula, adiciona ,00
        if (strpos($valor, ',') === false) {
            $valor = $valor . ',00';
        }
        
        // Garante que tem pelo menos 2 casas decimais
        $partes = explode(',', $valor);
        if (isset($partes[1]) && strlen($partes[1]) == 1) {
            $valor = $partes[0] . ',' . $partes[1] . '0';
        }
        
        return $valor;
    }

    public function limparCarrinho()
    {
        Session::forget('carrinho');
        Session::forget('descontos_aplicados');
        Session::forget('cliente_vendedor');
        Session::forget('valor_dinheiro_recebido');
        return redirect()->route('carrinho.index')->with('success', 'Carrinho limpo com sucesso!');
    }

    /**
     * Determina a tabela de vendas baseada na cidade do usuário
     */
    private function determineSalesTable($user)
    {
        if (!$user || !$user->cidade) {
            throw new \Exception('Usuário não possui cidade configurada.');
        }

        // Busca o nome da cidade a partir do ID da cidade do usuário
        $nomeCidade = DB::table('cidades')->where('id', $user->cidade)->value('cidade');
        
        if (!$nomeCidade) {
            throw new \Exception('Cidade do usuário não encontrada ou inválida.');
        }

        // Mapeia cidades para tabelas de vendas
        $mapeamentoCidades = [
            'tabira' => 'vendas_tabira',
            'princesa isabel' => 'vendas_princesa',
            'princesa' => 'vendas_princesa' // Alternativa para Princesa Isabel
        ];

        $cidadeNormalizada = strtolower(trim($nomeCidade));
        
        // Log para debug
        \Illuminate\Support\Facades\Log::info("Determinando tabela de vendas para cidade: {$nomeCidade} (normalizada: {$cidadeNormalizada})");
        
        if (!isset($mapeamentoCidades[$cidadeNormalizada])) {
            throw new \Exception("Tabela de vendas não configurada para a cidade: {$nomeCidade}. Cidades disponíveis: " . implode(', ', array_keys($mapeamentoCidades)));
        }

        $tableName = $mapeamentoCidades[$cidadeNormalizada];
        \Illuminate\Support\Facades\Log::info("Tabela de vendas selecionada: {$tableName}");
        
        return $tableName;
    }

    /**
     * Valida se a tabela de vendas existe no banco de dados
     */
    private function validateSalesTable($tableName)
    {
        try {
            // Verifica se a tabela existe fazendo uma consulta simples
            DB::select("SELECT 1 FROM {$tableName} LIMIT 1");
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Tabela de vendas {$tableName} não existe ou não está acessível.");
        }
    }

    /**
     * Valida a configuração da cidade do usuário
     */
    private function validateCityConfiguration($user)
    {
        if (!$user) {
            return ['valid' => false, 'message' => 'Usuário não autenticado.'];
        }

        if (!$user->cidade) {
            return ['valid' => false, 'message' => 'Usuário não possui cidade configurada.'];
        }

        $nomeCidade = DB::table('cidades')->where('id', $user->cidade)->value('cidade');
        
        if (!$nomeCidade) {
            return ['valid' => false, 'message' => 'Cidade do usuário não encontrada no sistema.'];
        }

        return ['valid' => true, 'cidade_nome' => $nomeCidade];
    }

    /**
     * Calcula a distribuição proporcional de pagamentos por item do carrinho
     */
    private function calculateItemPaymentDistribution($cartItem, $totalPayments, $cartTotal)
    {
        $itemTotal = $cartItem['preco'] * $cartItem['quantidade'];
        $itemProportion = $cartTotal > 0 ? ($itemTotal / $cartTotal) : 0;

        return [
            'valor_dinheiro' => $totalPayments['avista'] * $itemProportion,
            'valor_pix' => $totalPayments['pix'] * $itemProportion,
            'valor_cartao' => $totalPayments['cartao'] * $itemProportion,
            'valor_crediario' => $totalPayments['crediario'] * $itemProportion
        ];
    }

    /**
     * Converte valores de pagamento da sessão para formato numérico
     */
    private function convertPaymentSessionData()
    {
        $descontosAplicados = Session::get('descontos_aplicados', []);
        
        return [
            'avista' => $this->converterValorMonetarioParaFloat($descontosAplicados['avista'] ?? '0,00'),
            'pix' => $this->converterValorMonetarioParaFloat($descontosAplicados['pix'] ?? '0,00'),
            'cartao' => $this->converterValorMonetarioParaFloat($descontosAplicados['cartao'] ?? '0,00'),
            'crediario' => $this->converterValorMonetarioParaFloat($descontosAplicados['crediario'] ?? '0,00')
        ];
    }

    /**
     * Valida se os dados de pagamento estão completos
     */
    private function validatePaymentData($paymentData)
    {
        $totalPayment = array_sum($paymentData);
        
        \Illuminate\Support\Facades\Log::info('Validando dados de pagamento: ' . json_encode($paymentData));
        \Illuminate\Support\Facades\Log::info('Total de pagamento calculado: ' . $totalPayment);
        
        if ($totalPayment <= 0) {
            return ['valid' => false, 'message' => 'Nenhum valor de pagamento foi informado. Configure os descontos primeiro.'];
        }

        return ['valid' => true, 'total' => $totalPayment];
    }

    /**
     * Cria um registro de venda individual
     */
    private function createSalesRecord($cartItem, $salesTable, $paymentDistribution, $user, $clienteVendedor)
    {
        $now = now();
        $totalOriginal = $cartItem['preco'] * $cartItem['quantidade'];
        
        // Calcula o preço de venda com desconto
        $totalPayments = array_sum($paymentDistribution);
        $precoVenda = $totalPayments / $cartItem['quantidade']; // Preço unitário com desconto
        
        // Calcula valores de pagamento individuais (por unidade)
        $valorDinheiroUnitario = ($paymentDistribution['valor_dinheiro'] ?? 0) / $cartItem['quantidade'];
        $valorPixUnitario = ($paymentDistribution['valor_pix'] ?? 0) / $cartItem['quantidade'];
        $valorCartaoUnitario = ($paymentDistribution['valor_cartao'] ?? 0) / $cartItem['quantidade'];
        $valorCrediarioUnitario = ($paymentDistribution['valor_crediario'] ?? 0) / $cartItem['quantidade'];

        $salesData = [
            'id_vendedor' => $user->id,
            'id_vendedor_atendente' => $clienteVendedor['vendedor_atendente_id'] ?? null,
            'id_produto' => $cartItem['id'],
            'data_venda' => $now->toDateString(),
            'hora' => $now->toTimeString(),
            'data_estorno' => null,
            'valor_dinheiro' => round($valorDinheiroUnitario, 2), // Valor individual por unidade
            'valor_pix' => round($valorPixUnitario, 2), // Valor individual por unidade
            'valor_cartao' => round($valorCartaoUnitario, 2), // Valor individual por unidade
            'valor_crediario' => round($valorCrediarioUnitario, 2), // Valor individual por unidade
            'preco' => $cartItem['preco'], // Preço original sem desconto
            'preco_venda' => round($precoVenda, 2), // Preço com desconto aplicado
            'desconto' => 0, // FALSE
            'alerta' => 0, // FALSE
            'baixa_fiscal' => 0, // FALSE
            'numeracao' => $cartItem['numeracao'],
            'pedido_devolucao' => null,
            'reposicao' => '',
            'bd' => '',
            'ticket' => ''
        ];

        // Cria um registro para cada quantidade do item
        for ($i = 0; $i < $cartItem['quantidade']; $i++) {
            DB::table($salesTable)->insert($salesData);
        }
    }

    /**
     * Atualiza o estoque do produto na tabela produtos
     */
    private function updateProductStock($productId, $quantitySold)
    {
        \Illuminate\Support\Facades\Log::info("Atualizando estoque do produto ID {$productId}, quantidade vendida: {$quantitySold}");
        
        // Primeiro, verifica o estoque atual
        $produto = DB::table('produtos')->where('id', $productId)->first();
        
        if (!$produto) {
            throw new \Exception("Produto ID {$productId} não encontrado.");
        }
        
        // Converte quantidade para inteiro (pode estar como string)
        $estoqueAtual = (int) $produto->quantidade;
        $quantidadeVendida = (int) $quantitySold;
        
        \Illuminate\Support\Facades\Log::info("Estoque atual: {$estoqueAtual}, Quantidade a vender: {$quantidadeVendida}");
        
        if ($estoqueAtual < $quantidadeVendida) {
            throw new \Exception("Estoque insuficiente para produto ID {$productId}. Disponível: {$estoqueAtual}, Solicitado: {$quantidadeVendida}");
        }
        
        // Atualiza a quantidade na tabela produtos
        $novoEstoque = $estoqueAtual - $quantidadeVendida;
        $updated = DB::table('produtos')
            ->where('id', $productId)
            ->update(['quantidade' => (string) $novoEstoque]); // Mantém como string se necessário
            
        if (!$updated) {
            throw new \Exception("Falha ao atualizar estoque do produto ID {$productId}.");
        }
        
        \Illuminate\Support\Facades\Log::info("Estoque do produto ID {$productId} atualizado: {$estoqueAtual} -> {$novoEstoque}");
    }

    /**
     * Atualiza o estoque específico da numeração na tabela de estoque da cidade
     */
    private function updateCityStock($user, $productId, $numeracao, $quantitySold)
    {
        // Busca o nome da cidade a partir do ID da cidade do usuário
        $nomeCidade = DB::table('cidades')->where('id', $user->cidade)->value('cidade');
        if (!$nomeCidade) {
            throw new \Exception('Cidade do usuário não encontrada para atualização de estoque.');
        }
        
        $nomeTabela = 'estoque_' . strtolower(str_replace(' ', '_', $nomeCidade));
        
        \Illuminate\Support\Facades\Log::info("Atualizando estoque da tabela {$nomeTabela} - Produto ID {$productId}, Numeração {$numeracao}, Quantidade: {$quantitySold}");
        
        // Primeiro, verifica o estoque atual da numeração
        $estoqueItem = DB::table($nomeTabela)
            ->where('id_produto', $productId)
            ->where('numero', $numeracao)
            ->first();
            
        if (!$estoqueItem) {
            throw new \Exception("Estoque não encontrado para produto ID {$productId}, numeração {$numeracao} na tabela {$nomeTabela}.");
        }
        
        $estoqueAtual = (int) $estoqueItem->quantidade;
        $quantidadeVendida = (int) $quantitySold;
        
        \Illuminate\Support\Facades\Log::info("Estoque atual numeração {$numeracao}: {$estoqueAtual}, Quantidade a vender: {$quantidadeVendida}");
        
        if ($estoqueAtual < $quantidadeVendida) {
            throw new \Exception("Estoque insuficiente para numeração {$numeracao} do produto ID {$productId}. Disponível: {$estoqueAtual}, Solicitado: {$quantidadeVendida}");
        }
        
        // Atualiza a quantidade na tabela de estoque específica da cidade
        $novoEstoque = $estoqueAtual - $quantidadeVendida;
        $updated = DB::table($nomeTabela)
            ->where('id_produto', $productId)
            ->where('numero', $numeracao)
            ->update(['quantidade' => $novoEstoque]);
            
        if (!$updated) {
            throw new \Exception("Falha ao atualizar estoque da numeração {$numeracao} do produto ID {$productId} na tabela {$nomeTabela}.");
        }
        
        \Illuminate\Support\Facades\Log::info("Estoque da tabela {$nomeTabela} atualizado: numeração {$numeracao} {$estoqueAtual} -> {$novoEstoque}");
    }

    /**
     * Valida se há estoque suficiente para todos os itens do carrinho
     */
    private function validateCartStock($user, $carrinho)
    {
        // Busca o nome da cidade a partir do ID da cidade do usuário
        $nomeCidade = DB::table('cidades')->where('id', $user->cidade)->value('cidade');
        if (!$nomeCidade) {
            return ['valid' => false, 'message' => 'Cidade do usuário não encontrada para validação de estoque.'];
        }
        
        $nomeTabela = 'estoque_' . strtolower(str_replace(' ', '_', $nomeCidade));
        
        // Agrupa quantidades por produto para validação do estoque geral
        $productQuantities = [];
        foreach ($carrinho as $cartItem) {
            if (!isset($productQuantities[$cartItem['id']])) {
                $productQuantities[$cartItem['id']] = [
                    'total' => 0,
                    'nome' => $cartItem['nome']
                ];
            }
            $productQuantities[$cartItem['id']]['total'] += $cartItem['quantidade'];
        }
        
        // Valida estoque geral por produto
        foreach ($productQuantities as $productId => $data) {
            $produtoEstoque = DB::table('produtos')
                ->where('id', $productId)
                ->value('quantidade');
                
            if (!$produtoEstoque || $produtoEstoque < $data['total']) {
                return [
                    'valid' => false, 
                    'message' => "Estoque insuficiente para o produto {$data['nome']}. Disponível: {$produtoEstoque}, Total solicitado: {$data['total']}"
                ];
            }
        }
        
        // Valida estoque específico por numeração
        foreach ($carrinho as $itemId => $cartItem) {
            $numeracaoEstoque = DB::table($nomeTabela)
                ->where('id_produto', $cartItem['id'])
                ->where('numero', $cartItem['numeracao'])
                ->value('quantidade');
                
            if (!$numeracaoEstoque || $numeracaoEstoque < $cartItem['quantidade']) {
                return [
                    'valid' => false, 
                    'message' => "Estoque insuficiente para o produto {$cartItem['nome']} na numeração {$cartItem['numeracao']}. Disponível: {$numeracaoEstoque}, Solicitado: {$cartItem['quantidade']}"
                ];
            }
        }
        
        return ['valid' => true];
    }

    /**
     * Valida todos os dados necessários para finalizar a compra
     */
    private function validatePurchaseData()
    {
        // Verifica autenticação do usuário
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return ['valid' => false, 'message' => 'Usuário não autenticado.'];
        }

        // Verifica se há itens no carrinho
        $carrinho = Session::get('carrinho', []);
        if (empty($carrinho)) {
            return ['valid' => false, 'message' => 'Carrinho está vazio.'];
        }

        // Valida configuração da cidade
        $cityValidation = $this->validateCityConfiguration($user);
        if (!$cityValidation['valid']) {
            return $cityValidation;
        }

        // Valida dados de pagamento
        $paymentData = $this->convertPaymentSessionData();
        $paymentValidation = $this->validatePaymentData($paymentData);
        if (!$paymentValidation['valid']) {
            return $paymentValidation;
        }

        return [
            'valid' => true,
            'user' => $user,
            'carrinho' => $carrinho,
            'payment_data' => $paymentData,
            'total_payment' => $paymentValidation['total']
        ];
    }

    /**
     * Finaliza a compra processando todos os itens do carrinho
     */
    public function finalizarCompra(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Iniciando processo de finalização de compra');
        
        try {
            // Valida todos os dados necessários
            $validation = $this->validatePurchaseData();
            if (!$validation['valid']) {
                \Illuminate\Support\Facades\Log::error('Validação falhou: ' . $validation['message']);
                return redirect()->back()->with('error', $validation['message']);
            }
            
            \Illuminate\Support\Facades\Log::info('Validação passou com sucesso');

            $user = $validation['user'];
            $carrinho = $validation['carrinho'];
            $paymentData = $validation['payment_data'];

            // Valida se há estoque suficiente para todos os itens
            $stockValidation = $this->validateCartStock($user, $carrinho);
            if (!$stockValidation['valid']) {
                \Illuminate\Support\Facades\Log::error('Validação de estoque falhou: ' . $stockValidation['message']);
                return redirect()->back()->with('error', $stockValidation['message']);
            }

            // Determina a tabela de vendas
            $salesTable = $this->determineSalesTable($user);
            $this->validateSalesTable($salesTable);

            // Recupera dados do cliente/vendedor
            $clienteVendedor = Session::get('cliente_vendedor', []);

            // Calcula total do carrinho
            $cartTotal = $this->calcularTotalCarrinho();

            // Inicia transação do banco de dados
            DB::beginTransaction();

            try {
                // Primeiro, agrupa as quantidades por produto para atualização do estoque geral
                $productQuantities = [];
                foreach ($carrinho as $cartItem) {
                    if (!isset($productQuantities[$cartItem['id']])) {
                        $productQuantities[$cartItem['id']] = 0;
                    }
                    $productQuantities[$cartItem['id']] += $cartItem['quantidade'];
                }
                
                // Atualiza estoque geral dos produtos (uma vez por produto)
                foreach ($productQuantities as $productId => $totalQuantity) {
                    $this->updateProductStock($productId, $totalQuantity);
                }
                
                // Processa cada item do carrinho para vendas e estoque por numeração
                foreach ($carrinho as $itemId => $cartItem) {
                    // Calcula distribuição de pagamento para este item
                    $paymentDistribution = $this->calculateItemPaymentDistribution(
                        $cartItem, 
                        $paymentData, 
                        $cartTotal
                    );

                    // Cria registro de venda
                    $this->createSalesRecord(
                        $cartItem, 
                        $salesTable, 
                        $paymentDistribution, 
                        $user, 
                        $clienteVendedor
                    );
                    
                    // Atualiza estoque específico da numeração na tabela de estoque da cidade
                    $this->updateCityStock($user, $cartItem['id'], $cartItem['numeracao'], $cartItem['quantidade']);
                }

                // Confirma a transação
                DB::commit();

                // Limpa dados da sessão
                $this->clearCartSession();

                return redirect()->route('carrinho.index')->with('success', 'Compra finalizada com sucesso!');

            } catch (\Exception $e) {
                // Desfaz a transação em caso de erro
                DB::rollback();
                \Illuminate\Support\Facades\Log::error('Erro ao criar registros de venda: ' . $e->getMessage());
                \Illuminate\Support\Facades\Log::error('Stack trace: ' . $e->getTraceAsString());
                
                // Mensagem mais específica baseada no tipo de erro
                if (strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
                    return redirect()->back()->with('error', 'Erro de integridade dos dados. Verifique se todos os campos obrigatórios estão preenchidos.');
                } elseif (strpos($e->getMessage(), 'doesn\'t exist') !== false) {
                    return redirect()->back()->with('error', 'Tabela de vendas não encontrada para sua cidade.');
                } else {
                    return redirect()->back()->with('error', 'Erro ao processar a compra: ' . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro na finalização da compra: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Limpa todos os dados da sessão relacionados ao carrinho
     */
    private function clearCartSession()
    {
        Session::forget('carrinho');
        Session::forget('descontos_aplicados');
        Session::forget('cliente_vendedor');
        Session::forget('valor_dinheiro_recebido');
        
        // Limpa também as quantidades disponíveis armazenadas
        $sessionKeys = array_keys(Session::all());
        foreach ($sessionKeys as $key) {
            if (strpos($key, 'quantidade_disponivel_') === 0) {
                Session::forget($key);
            }
        }
    }

    /**
     * Inicia o processo de venda crediário
     */
    public function vendaCrediario()
    {
        // Verifica se há itens no carrinho
        $carrinho = Session::get('carrinho', []);
        if (empty($carrinho)) {
            return redirect()->route('carrinho.index')->with('error', 'Carrinho vazio. Adicione produtos antes de iniciar uma venda crediário.');
        }

        // Redireciona para a página de pesquisa de cliente
        return redirect()->route('carrinho.pesquisar-cliente');
    }

    /**
     * Exibe a página de pesquisa de cliente
     */
    public function exibirPesquisaCliente()
    {
        return view('carrinho.pesquisar-cliente');
    }

    /**
     * Realiza a pesquisa de clientes
     */
    public function pesquisarCliente(Request $request)
    {
        $searchTerm = $request->input('search');
        
        if (empty($searchTerm)) {
            return redirect()->back()->with('error', 'Digite um termo para pesquisar.');
        }

        $clientes = $this->searchCustomers($searchTerm);
        
        if ($clientes->isEmpty()) {
            return redirect()->back()->with('error', 'Nenhum cliente encontrado.');
        }

        // Valida o status de cada cliente
        $clientesComStatus = $clientes->map(function ($cliente) {
            $statusValidation = $this->validateCustomerStatus($cliente);
            $cliente->action_type = $statusValidation['action_type'];
            $cliente->action_enabled = $statusValidation['action_enabled'];
            $cliente->action_text = $statusValidation['action_text'];
            return $cliente;
        });

        return view('carrinho.pesquisar-cliente', compact('clientesComStatus', 'searchTerm'));
    }

    /**
     * Pesquisa clientes por nome, apelido, rg ou cpf
     */
    private function searchCustomers($searchTerm)
    {
        return DB::table('clientes')
            ->where('nome', 'LIKE', "%{$searchTerm}%")
            ->orWhere('apelido', 'LIKE', "%{$searchTerm}%")
            ->orWhere('rg', 'LIKE', "%{$searchTerm}%")
            ->orWhere('cpf', 'LIKE', "%{$searchTerm}%")
            ->get();
    }

    /**
     * Valida o status do cliente e determina a ação disponível
     */
    private function validateCustomerStatus($cliente)
    {
        if ($cliente->status === 'inativo') {
            // Verifica se há registros com SPC = true na tabela tickets
            $temSpc = DB::table('tickets')
                ->where('id_cliente', $cliente->id)
                ->where('spc', 'true')
                ->exists();

            if ($temSpc) {
                return [
                    'action_type' => 'negativado',
                    'action_enabled' => false,
                    'action_text' => 'Cliente Negativado'
                ];
            } else {
                return [
                    'action_type' => 'bloqueado',
                    'action_enabled' => false,
                    'action_text' => 'Cliente Bloqueado'
                ];
            }
        }

        // Cliente ativo - verifica data de atualização
        if ($cliente->status === 'ativo') {
            $hoje = now()->toDateString();
            $dataAtualizacao = $cliente->atualizacao;

            if ($dataAtualizacao === $hoje) {
                return [
                    'action_type' => 'selecionar',
                    'action_enabled' => true,
                    'action_text' => 'Selecionar Cliente'
                ];
            } else {
                return [
                    'action_type' => 'verificar',
                    'action_enabled' => true,
                    'action_text' => 'Verificar Número'
                ];
            }
        }

        // Status desconhecido
        return [
            'action_type' => 'bloqueado',
            'action_enabled' => false,
            'action_text' => 'Status Inválido'
        ];
    }

    /**
     * Seleciona um cliente para venda crediário
     */
    public function selecionarCliente($clienteId)
    {
        // Busca o cliente
        $cliente = DB::table('clientes')->where('id', $clienteId)->first();
        
        if (!$cliente) {
            return redirect()->route('carrinho.pesquisar-cliente')->with('error', 'Cliente não encontrado.');
        }

        // Valida o status do cliente
        $statusValidation = $this->validateCustomerStatus($cliente);
        
        if ($statusValidation['action_type'] !== 'selecionar') {
            return redirect()->route('carrinho.pesquisar-cliente')->with('error', 'Cliente não está disponível para seleção.');
        }

        // Armazena o cliente selecionado na sessão
        Session::put('cliente_crediario', [
            'id' => $cliente->id,
            'nome' => $cliente->nome,
            'rg' => $cliente->rg,
            'cpf' => $cliente->cpf,
            'limite' => $cliente->limite,
            'token' => $cliente->token
        ]);

        // Redireciona para a próxima etapa (que será implementada na próxima task)
        return redirect()->route('carrinho.configurar-venda-crediario');
    }

    /**
     * Calcula o crédito disponível do cliente
     */
    private function calculateAvailableCredit($clienteId)
    {
        // Busca o limite do cliente
        $cliente = DB::table('clientes')->where('id', $clienteId)->first();
        
        if (!$cliente) {
            return [
                'available_credit' => 0,
                'pending_amount' => 0,
                'overdue_payments' => false,
                'overdue_count' => 0,
                'minimum_entry_required' => false,
                'minimum_entry_amount' => 0
            ];
        }

        // Soma todas as parcelas pendentes
        $pendingAmount = DB::table('parcelas')
            ->where('id_cliente', $clienteId)
            ->where('status', 'aguardando pagamento')
            ->sum('valor_parcela');

        // Calcula crédito disponível
        $availableCredit = $cliente->limite - $pendingAmount;

        // Verifica parcelas em atraso
        $overduePayments = $this->checkOverduePayments($clienteId);

        return [
            'available_credit' => max(0, $availableCredit),
            'pending_amount' => $pendingAmount,
            'overdue_payments' => $overduePayments['has_overdue'],
            'overdue_count' => $overduePayments['overdue_count'],
            'minimum_entry_required' => false, // Será calculado quando necessário
            'minimum_entry_amount' => 0
        ];
    }

    /**
     * Verifica se o cliente possui parcelas em atraso
     */
    private function checkOverduePayments($clienteId)
    {
        $hoje = now()->toDateString();
        
        $overdueCount = DB::table('parcelas')
            ->where('id_cliente', $clienteId)
            ->where('status', 'aguardando pagamento')
            ->where('data_vencimento', '<', $hoje)
            ->count();

        return [
            'has_overdue' => $overdueCount > 0,
            'overdue_count' => $overdueCount
        ];
    }

    /**
     * Calcula a entrada mínima necessária
     */
    private function calculateMinimumEntry($purchaseTotal, $availableCredit)
    {
        if ($purchaseTotal <= $availableCredit) {
            return 0;
        }

        $excess = $purchaseTotal - $availableCredit;
        return $excess / 2; // Metade do valor que excede o limite
    }

    /**
     * Valida se a compra pode ser realizada com o crédito disponível
     */
    private function validateCreditPurchase($clienteId, $purchaseTotal)
    {
        $creditInfo = $this->calculateAvailableCredit($clienteId);
        
        // Se há parcelas em atraso, não permite a venda
        if ($creditInfo['overdue_payments']) {
            return [
                'can_proceed' => false,
                'error_type' => 'overdue_payments',
                'message' => "Cliente possui {$creditInfo['overdue_count']} parcela(s) em atraso. Não é possível realizar a venda.",
                'credit_info' => $creditInfo
            ];
        }

        // Se o valor da compra excede o crédito disponível
        if ($purchaseTotal > $creditInfo['available_credit']) {
            $minimumEntry = $this->calculateMinimumEntry($purchaseTotal, $creditInfo['available_credit']);
            
            return [
                'can_proceed' => true,
                'requires_entry' => true,
                'minimum_entry' => $minimumEntry,
                'message' => "Para prosseguir com a compra, é necessária uma entrada mínima de R$ " . number_format($minimumEntry, 2, ',', '.'),
                'credit_info' => $creditInfo
            ];
        }

        // Compra pode ser realizada sem entrada
        return [
            'can_proceed' => true,
            'requires_entry' => false,
            'minimum_entry' => 0,
            'message' => 'Compra pode ser realizada sem entrada.',
            'credit_info' => $creditInfo
        ];
    }

    /**
     * Exibe a página de configuração da venda crediário
     */
    public function configurarVendaCrediario()
    {
        // Verifica se há cliente selecionado na sessão
        $clienteCrediario = Session::get('cliente_crediario');
        if (!$clienteCrediario) {
            return redirect()->route('carrinho.pesquisar-cliente')->with('error', 'Selecione um cliente primeiro.');
        }

        // Verifica se há itens no carrinho
        $carrinho = Session::get('carrinho', []);
        if (empty($carrinho)) {
            return redirect()->route('carrinho.index')->with('error', 'Carrinho vazio.');
        }

        // Calcula o total da compra
        $totalCompra = $this->calcularTotalCarrinho();

        // Valida o crédito do cliente
        $creditValidation = $this->validateCreditPurchase($clienteCrediario['id'], $totalCompra);

        // Se não pode prosseguir devido a parcelas em atraso
        if (!$creditValidation['can_proceed']) {
            Session::forget('cliente_crediario');
            return redirect()->route('carrinho.pesquisar-cliente')->with('error', $creditValidation['message']);
        }

        // Busca compradores autorizados
        $autorizados = DB::table('autorizados')
            ->where('idCliente', $clienteCrediario['id'])
            ->get();

        // Gera opções de data de vencimento
        $datasVencimento = $this->generatePaymentDates();

        return view('carrinho.configurar-venda-crediario', compact(
            'clienteCrediario',
            'totalCompra',
            'creditValidation',
            'autorizados',
            'datasVencimento'
        ));
    }

    /**
     * Gera as opções de data de vencimento
     */
    private function generatePaymentDates()
    {
        $dates = [];
        $currentDate = now();
        
        // Próximo mês
        $nextMonth = $currentDate->copy()->addMonth();
        
        // Dia 10 do próximo mês
        $dates[] = [
            'value' => '10',
            'label' => $nextMonth->copy()->day(10)->format('d/m/Y') . ' (Dia 10)',
            'date' => $nextMonth->copy()->day(10)->toDateString()
        ];
        
        // Dia 20 do próximo mês
        $dates[] = [
            'value' => '20',
            'label' => $nextMonth->copy()->day(20)->format('d/m/Y') . ' (Dia 20)',
            'date' => $nextMonth->copy()->day(20)->toDateString()
        ];
        
        // Último dia do próximo mês
        $lastDay = $nextMonth->copy()->endOfMonth();
        $dates[] = [
            'value' => 'ultimo',
            'label' => $lastDay->format('d/m/Y') . ' (Último dia)',
            'date' => $lastDay->toDateString()
        ];
        
        // Dia 10 do mês seguinte
        $monthAfter = $nextMonth->copy()->addMonth();
        $dates[] = [
            'value' => '10_next',
            'label' => $monthAfter->copy()->day(10)->format('d/m/Y') . ' (Dia 10)',
            'date' => $monthAfter->copy()->day(10)->toDateString()
        ];

        return $dates;
    }

    /**
     * Calcula os valores das parcelas
     */
    private function calculateInstallments($amount, $numberOfInstallments)
    {
        if ($numberOfInstallments <= 0 || $amount <= 0) {
            return [];
        }

        $installmentAmount = ceil($amount / $numberOfInstallments);
        
        // Verifica se o valor da parcela é pelo menos R$ 20
        if ($installmentAmount < 20) {
            throw new \Exception('Valor da parcela deve ser no mínimo R$ 20,00');
        }

        $installments = [];
        $totalCalculated = 0;

        // Cria todas as parcelas exceto a última
        for ($i = 1; $i < $numberOfInstallments; $i++) {
            $installments[] = [
                'numero' => $i,
                'valor' => $installmentAmount
            ];
            $totalCalculated += $installmentAmount;
        }

        // Última parcela é o valor restante
        $lastInstallmentAmount = $amount - $totalCalculated;
        $installments[] = [
            'numero' => $numberOfInstallments,
            'valor' => $lastInstallmentAmount
        ];

        return $installments;
    }

    /**
     * Gera as datas de vencimento das parcelas
     */
    private function generateInstallmentDates($firstPaymentDate, $numberOfInstallments, $dayType)
    {
        $dates = [];
        $currentDate = \Carbon\Carbon::parse($firstPaymentDate);

        for ($i = 0; $i < $numberOfInstallments; $i++) {
            if ($i > 0) {
                // Adiciona um mês preservando o dia
                if ($dayType === 'ultimo') {
                    $currentDate = $currentDate->copy()->addMonth()->endOfMonth();
                } else {
                    $currentDate = $currentDate->copy()->addMonth();
                }
            }

            $dates[] = $currentDate->toDateString();
        }

        return $dates;
    }

    /**
     * Valida se o número de parcelas é válido para o valor
     */
    private function validateInstallmentConfiguration($amount, $numberOfInstallments)
    {
        if ($numberOfInstallments < 1 || $numberOfInstallments > 12) {
            return [
                'valid' => false,
                'message' => 'Número de parcelas deve ser entre 1 e 12.'
            ];
        }

        $installmentAmount = ceil($amount / $numberOfInstallments);
        
        if ($installmentAmount < 20) {
            return [
                'valid' => false,
                'message' => 'Valor da parcela seria menor que R$ 20,00. Reduza o número de parcelas ou aumente a entrada.'
            ];
        }

        return [
            'valid' => true,
            'installment_amount' => $installmentAmount
        ];
    }

    /**
     * Gera um ticket único para a venda com exatamente 20 caracteres
     */
    private function generateUniqueTicket()
    {
        $maxAttempts = 100; // Limite de tentativas para evitar loop infinito
        $attempts = 0;
        
        do {
            $attempts++;
            
            // Formato: TK + YYYYMMDD + HHMMSS + 4 caracteres aleatórios = 20 caracteres
            $timestamp = now();
            $date = $timestamp->format('Ymd'); // 8 caracteres (ex: 20250810)
            $time = $timestamp->format('His'); // 6 caracteres (ex: 143052)
            
            // Gera 4 caracteres aleatórios (números e letras para maior variabilidade)
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $random = '';
            for ($i = 0; $i < 4; $i++) {
                $random .= $characters[rand(0, strlen($characters) - 1)];
            }
            
            $ticket = 'TK' . $date . $time . $random; // Total: 2 + 8 + 6 + 4 = 20 caracteres
            
            // Verifica se o ticket já existe na tabela
            $exists = DB::table('tickets')->where('ticket', $ticket)->exists();
            
            // Se exceder o limite de tentativas, adiciona microsegundos para garantir unicidade
            if ($attempts >= $maxAttempts) {
                $microtime = str_pad(substr(microtime(true) * 1000000, -4), 4, '0', STR_PAD_LEFT);
                $ticket = 'TK' . $date . substr($time, 0, 2) . $microtime . substr($random, 0, 4);
                $exists = DB::table('tickets')->where('ticket', $ticket)->exists();
            }
            
        } while ($exists && $attempts < ($maxAttempts + 10));
        
        // Log para debug se necessário
        if ($attempts > 1) {
            \Illuminate\Support\Facades\Log::info("Ticket gerado após {$attempts} tentativas: {$ticket}");
        }

        return $ticket;
    }

    /**
     * Cria o registro do ticket na tabela tickets
     */
    private function createTicketRecord($ticketData)
    {
        return DB::table('tickets')->insert([
            'id_cliente' => $ticketData['id_cliente'],
            'ticket' => $ticketData['ticket'],
            'data' => now(),
            'valor' => $ticketData['valor'], // valor da compra - entrada
            'entrada' => $ticketData['entrada'],
            'parcelas' => $ticketData['parcelas'],
            'spc' => null
        ]);
    }

    /**
     * Cria os registros das parcelas na tabela parcelas
     */
    private function createInstallmentRecords($installmentData)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Busca o nome da cidade para o campo bd
        $nomeCidade = DB::table('cidades')->where('id', $user->cidade)->value('cidade');
        $bdValue = 'vendas_' . strtolower(str_replace(' ', '_', $nomeCidade));

        foreach ($installmentData['installments'] as $index => $installment) {
            DB::table('parcelas')->insert([
                'ticket' => $installmentData['ticket'],
                'id_cliente' => $installmentData['id_cliente'],
                'id_autorizado' => $installmentData['id_autorizado'],
                'numero' => $installment['numero'] . '/' . $installmentData['total_parcelas'],
                'data_vencimento' => $installmentData['dates'][$index],
                'data_pagamento' => null,
                'hora' => null,
                'valor_parcela' => $installment['valor'],
                'valor_pago' => null,
                'dinheiro' => null,
                'pix' => null,
                'cartao' => null,
                'metodo' => null,
                'id_vendedor' => null,
                'status' => 'aguardando pagamento',
                'bd' => $bdValue,
                'ticket_pagamento' => null,
                'lembrete' => null,
                'primeira' => null,
                'segunda' => null,
                'terceira' => null,
                'quarta' => null,
                'quinta' => null,
                'sexta' => null,
                'setima' => null,
                'oitava' => null,
                'nona' => null
            ]);
        }
    }

    /**
     * Processa os dados do formulário de venda crediário
     */
    private function processInstallmentData($formData, $clienteId, $totalCompra)
    {
        $valorEntrada = $this->converterValorMonetarioParaFloat($formData['valor_entrada'] ?? '0');
        $valorFinanciar = $totalCompra - $valorEntrada;
        $quantidadeParcelas = (int) $formData['quantidade_parcelas'];
        
        // Valida a configuração das parcelas
        $validation = $this->validateInstallmentConfiguration($valorFinanciar, $quantidadeParcelas);
        if (!$validation['valid']) {
            throw new \Exception($validation['message']);
        }

        // Calcula as parcelas
        $installments = $this->calculateInstallments($valorFinanciar, $quantidadeParcelas);

        // Determina a data da primeira parcela
        $dayType = $formData['data_vencimento'];
        $firstDate = $this->getFirstPaymentDate($dayType);
        
        // Gera as datas de vencimento
        $dates = $this->generateInstallmentDates($firstDate, $quantidadeParcelas, $dayType);

        return [
            'valor_entrada' => $valorEntrada,
            'valor_financiar' => $valorFinanciar,
            'installments' => $installments,
            'dates' => $dates,
            'metodo_entrada' => $formData['metodo_entrada'],
            'comprador' => $formData['comprador']
        ];
    }

    /**
     * Obtém a data da primeira parcela baseada na seleção
     */
    private function getFirstPaymentDate($dayType)
    {
        $nextMonth = now()->addMonth();
        
        switch ($dayType) {
            case '10':
                return $nextMonth->day(10)->toDateString();
            case '20':
                return $nextMonth->day(20)->toDateString();
            case 'ultimo':
                return $nextMonth->endOfMonth()->toDateString();
            case '10_next':
                return $nextMonth->addMonth()->day(10)->toDateString();
            default:
                return $nextMonth->day(10)->toDateString();
        }
    }

    /**
     * Processa a venda crediário completa
     */
    public function processarVendaCrediario(Request $request)
    {
        try {
            // Inicia transação
            DB::beginTransaction();

            // Verifica se há cliente selecionado na sessão
            $clienteCrediario = Session::get('cliente_crediario');
            if (!$clienteCrediario) {
                throw new \Exception('Cliente não selecionado.');
            }

            // Verifica se há itens no carrinho
            $carrinho = Session::get('carrinho', []);
            if (empty($carrinho)) {
                throw new \Exception('Carrinho vazio.');
            }

            // Calcula o total da compra
            $totalCompra = $this->calcularTotalCarrinho();

            // Processa os dados das parcelas
            $installmentData = $this->processInstallmentData($request->all(), $clienteCrediario['id'], $totalCompra);

            // Gera ticket único
            $ticket = $this->generateUniqueTicket();

            // Cria registro do ticket
            $this->createTicketRecord([
                'id_cliente' => $clienteCrediario['id'],
                'ticket' => $ticket,
                'valor' => $installmentData['valor_financiar'],
                'entrada' => $installmentData['valor_entrada'],
                'parcelas' => count($installmentData['installments'])
            ]);

            // Cria registros das parcelas
            $this->createInstallmentRecords([
                'ticket' => $ticket,
                'id_cliente' => $clienteCrediario['id'],
                'id_autorizado' => $installmentData['comprador'] === 'titular' ? null : $installmentData['comprador'],
                'installments' => $installmentData['installments'],
                'dates' => $installmentData['dates'],
                'total_parcelas' => count($installmentData['installments'])
            ]);

            // Armazena dados da venda crediário na sessão para integração com vendas
            Session::put('venda_crediario_data', [
                'ticket' => $ticket,
                'valor_entrada' => $installmentData['valor_entrada'],
                'metodo_entrada' => $installmentData['metodo_entrada'],
                'valor_crediario' => $installmentData['valor_financiar']
            ]);

            // Commit da transação
            DB::commit();

            // Redireciona para finalização da venda
            return redirect()->route('carrinho.finalizar-venda-crediario');

        } catch (\Exception $e) {
            // Rollback em caso de erro
            DB::rollback();
            
            \Illuminate\Support\Facades\Log::error('Erro ao processar venda crediário: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Erro ao processar venda crediário: ' . $e->getMessage());
        }
    }

    /**
     * Finaliza a venda crediário integrando com o sistema de vendas existente
     */
    public function finalizarVendaCrediario()
    {
        try {
            // Verifica se há dados da venda crediário na sessão
            $vendaCrediarioData = Session::get('venda_crediario_data');
            if (!$vendaCrediarioData) {
                return redirect()->route('carrinho.index')->with('error', 'Dados da venda crediário não encontrados.');
            }

            // Verifica se há itens no carrinho
            $carrinho = Session::get('carrinho', []);
            if (empty($carrinho)) {
                return redirect()->route('carrinho.index')->with('error', 'Carrinho vazio.');
            }

            // Configura os dados de pagamento para integração com o sistema existente
            $this->setupCreditSalePaymentData($vendaCrediarioData);

            // Chama o método de finalização de compra existente
            $this->finalizarCompraCrediario($vendaCrediarioData);

            // Processa modificações pós-venda
            $this->processPostSaleModifications($vendaCrediarioData);

            // Obter ID do cliente antes de limpar as sessões
            $clienteCrediario = Session::get('cliente_crediario');
            $clienteId = $clienteCrediario['id'] ?? null;
            
            // Limpa as sessões
            $this->clearCreditSaleSessions();

            // Redirecionar para detalhamento da compra
            if ($clienteId) {
                return redirect()->route('clientes.compra', [
                    'id' => $clienteId, 
                    'ticket' => $vendaCrediarioData['ticket']
                ])->with('success', 'Venda crediário realizada com sucesso!');
            } else {
                return redirect()->route('carrinho.index')->with('success', 'Venda crediário realizada com sucesso! Ticket: ' . $vendaCrediarioData['ticket']);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao finalizar venda crediário: ' . $e->getMessage());
            return redirect()->route('carrinho.index')->with('error', 'Erro ao finalizar venda crediário: ' . $e->getMessage());
        }
    }

    /**
     * Configura os dados de pagamento na sessão para integração com vendas
     */
    private function setupCreditSalePaymentData($vendaCrediarioData)
    {
        // Configura os descontos aplicados baseado na entrada e crediário
        $descontosAplicados = [
            'avista' => '0,00',
            'pix' => '0,00',
            'cartao' => '0,00',
            'crediario' => number_format($vendaCrediarioData['valor_crediario'], 2, ',', '.'),
            'tipo_selecionado' => 'crediario',
            'modo_manual' => true
        ];

        // Adiciona o valor da entrada no método correspondente
        switch ($vendaCrediarioData['metodo_entrada']) {
            case 'dinheiro':
                $descontosAplicados['avista'] = number_format($vendaCrediarioData['valor_entrada'], 2, ',', '.');
                break;
            case 'pix':
                $descontosAplicados['pix'] = number_format($vendaCrediarioData['valor_entrada'], 2, ',', '.');
                break;
            case 'cartao':
                $descontosAplicados['cartao'] = number_format($vendaCrediarioData['valor_entrada'], 2, ',', '.');
                break;
        }

        Session::put('descontos_aplicados', $descontosAplicados);
    }

    /**
     * Executa a finalização da compra para venda crediário
     */
    private function finalizarCompraCrediario($vendaCrediarioData)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $carrinho = Session::get('carrinho', []);
        
        // Determina a tabela de vendas
        $salesTable = $this->determineSalesTable($user);
        
        // Valida a tabela de vendas
        $this->validateSalesTable($salesTable);
        
        // Converte dados de pagamento
        $paymentData = $this->convertPaymentSessionData();
        
        // Valida dados de pagamento
        $paymentValidation = $this->validatePaymentData($paymentData);
        if (!$paymentValidation['valid']) {
            throw new \Exception($paymentValidation['message']);
        }
        
        $cartTotal = $this->calcularTotalCarrinho();
        $clienteVendedor = Session::get('cliente_vendedor', []);
        
        // Processa cada item do carrinho
        foreach ($carrinho as $itemId => $cartItem) {
            $paymentDistribution = $this->calculateItemPaymentDistribution($cartItem, $paymentData, $cartTotal);
            
            // Cria registro de venda com o ticket
            $this->createSalesRecordWithTicket($cartItem, $salesTable, $paymentDistribution, $user, $clienteVendedor, $vendaCrediarioData['ticket']);
            
            // Atualiza estoques
            $this->updateProductStock($cartItem['id'], $cartItem['quantidade']);
            $this->updateCityStock($user, $cartItem['id'], $cartItem['numeracao'], $cartItem['quantidade']);
        }
    }

    /**
     * Cria registro de venda com ticket (versão modificada para crediário)
     */
    private function createSalesRecordWithTicket($cartItem, $salesTable, $paymentDistribution, $user, $clienteVendedor, $ticket)
    {
        $now = now();
        $totalOriginal = $cartItem['preco'] * $cartItem['quantidade'];
        
        // Calcula o preço de venda com desconto
        $totalPayments = array_sum($paymentDistribution);
        $precoVenda = $totalPayments / $cartItem['quantidade']; // Preço unitário com desconto
        
        // Calcula valores de pagamento individuais (por unidade)
        $valorDinheiroUnitario = ($paymentDistribution['valor_dinheiro'] ?? 0) / $cartItem['quantidade'];
        $valorPixUnitario = ($paymentDistribution['valor_pix'] ?? 0) / $cartItem['quantidade'];
        $valorCartaoUnitario = ($paymentDistribution['valor_cartao'] ?? 0) / $cartItem['quantidade'];
        $valorCrediarioUnitario = ($paymentDistribution['valor_crediario'] ?? 0) / $cartItem['quantidade'];

        $salesData = [
            'id_vendedor' => $user->id,
            'id_vendedor_atendente' => $clienteVendedor['vendedor_atendente_id'] ?? null,
            'id_produto' => $cartItem['id'],
            'data_venda' => $now->toDateString(),
            'hora' => $now->toTimeString(),
            'data_estorno' => null,
            'valor_dinheiro' => round($valorDinheiroUnitario, 2),
            'valor_pix' => round($valorPixUnitario, 2),
            'valor_cartao' => round($valorCartaoUnitario, 2),
            'valor_crediario' => round($valorCrediarioUnitario, 2),
            'preco' => $cartItem['preco'],
            'preco_venda' => round($precoVenda, 2),
            'desconto' => 0,
            'alerta' => 0,
            'baixa_fiscal' => 0,
            'numeracao' => $cartItem['numeracao'],
            'pedido_devolucao' => null,
            'reposicao' => '',
            'bd' => '',
            'ticket' => $ticket // Adiciona o ticket gerado
        ];

        // Cria um registro para cada quantidade do item
        for ($i = 0; $i < $cartItem['quantidade']; $i++) {
            DB::table($salesTable)->insert($salesData);
        }
    }

    /**
     * Limpa as sessões relacionadas à venda crediário
     */
    private function clearCreditSaleSessions()
    {
        Session::forget('cliente_crediario');
        Session::forget('venda_crediario_data');
        Session::forget('carrinho');
        Session::forget('descontos_aplicados');
        Session::forget('cliente_vendedor');
        Session::forget('valor_dinheiro_recebido');
        
        // Limpa também as quantidades disponíveis armazenadas
        $sessionKeys = array_keys(Session::all());
        foreach ($sessionKeys as $key) {
            if (strpos($key, 'quantidade_disponivel_') === 0) {
                Session::forget($key);
            }
        }
    }

    /**
     * Processa modificações após a finalização da venda crediário
     */
    private function processPostSaleModifications($vendaCrediarioData)
    {
        \Illuminate\Support\Facades\Log::info("ProcessPostSaleModifications - Iniciando modificações pós-venda");
        
        $clienteCrediario = Session::get('cliente_crediario');
        if (!$clienteCrediario || !isset($clienteCrediario['id'])) {
            \Illuminate\Support\Facades\Log::error("ProcessPostSaleModifications - Cliente crediário não encontrado na sessão");
            return;
        }

        $clienteId = $clienteCrediario['id'];
        \Illuminate\Support\Facades\Log::info("ProcessPostSaleModifications - Cliente ID: {$clienteId}");
        
        // 1. Verificar se entrada foi obrigatória e aumentar limite
        \Illuminate\Support\Facades\Log::info("ProcessPostSaleModifications - Processando aumento de limite");
        $this->processLimitIncrease($clienteId, $vendaCrediarioData);
        
        // 2. Definir token como NULL
        \Illuminate\Support\Facades\Log::info("ProcessPostSaleModifications - Limpando token do cliente");
        $this->clearClientToken($clienteId);
        
        \Illuminate\Support\Facades\Log::info("ProcessPostSaleModifications - Modificações pós-venda concluídas");
    }

    /**
     * Processa o aumento de limite quando entrada foi obrigatória
     */
    private function processLimitIncrease($clienteId, $vendaCrediarioData)
    {
        // Verificar se houve entrada
        $valorEntrada = $vendaCrediarioData['valor_entrada'] ?? 0;
        
        \Illuminate\Support\Facades\Log::info("ProcessLimitIncrease - Cliente ID: {$clienteId}, Valor entrada: R$ " . number_format($valorEntrada, 2, ',', '.'));
        
        if ($valorEntrada > 0) {
            // Buscar informações atuais do cliente
            $cliente = DB::table('clientes')->where('id', $clienteId)->first();
            if (!$cliente) {
                \Illuminate\Support\Facades\Log::error("ProcessLimitIncrease - Cliente ID {$clienteId} não encontrado");
                return;
            }
            
            \Illuminate\Support\Facades\Log::info("ProcessLimitIncrease - Cliente encontrado. Limite atual: R$ " . number_format($cliente->limite, 2, ',', '.'));
            
            // SIMPLIFICADO: Se há entrada, sempre aumentar o limite
            // (assumindo que se há entrada, ela era obrigatória)
            $limiteAtual = (float) $cliente->limite;
            $novoLimite = $limiteAtual + $valorEntrada;
            
            \Illuminate\Support\Facades\Log::info("ProcessLimitIncrease - Aumentando limite de R$ " . number_format($limiteAtual, 2, ',', '.') . " para R$ " . number_format($novoLimite, 2, ',', '.'));
            
            $updated = DB::table('clientes')
                ->where('id', $clienteId)
                ->update(['limite' => $novoLimite]);
                
            if ($updated) {
                \Illuminate\Support\Facades\Log::info("ProcessLimitIncrease - Limite do cliente ID {$clienteId} aumentado em R$ " . number_format($valorEntrada, 2, ',', '.') . " após finalização da venda. Novo limite: R$ " . number_format($novoLimite, 2, ',', '.'));
            } else {
                \Illuminate\Support\Facades\Log::error("ProcessLimitIncrease - Falha ao atualizar limite do cliente ID {$clienteId}");
            }
        } else {
            \Illuminate\Support\Facades\Log::info("ProcessLimitIncrease - Não há entrada, limite não será aumentado");
        }
    }

    /**
     * Define o token do cliente como NULL
     */
    private function clearClientToken($clienteId)
    {
        DB::table('clientes')
            ->where('id', $clienteId)
            ->update(['token' => null]);
            
        \Illuminate\Support\Facades\Log::info("Token do cliente ID {$clienteId} foi definido como NULL após finalização da venda.");
    }
}
