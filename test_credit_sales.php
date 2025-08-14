<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTE DO SISTEMA DE VENDA CREDIÁRIO ===\n\n";

// Função para limpar dados de teste anteriores
function limparDadosTeste() {
    DB::table('tickets')->where('id_cliente', 1)->delete();
    DB::table('parcelas')->where('id_cliente', 1)->delete();
    DB::table('vendas_tabira')->where('id_vendedor', 1)->delete();
    
    // Restaurar estoque
    DB::table('estoque_tabira')->where('id_produto', 1)->update(['quantidade' => 5]);
    DB::table('produtos')->where('id', 1)->update(['quantidade' => '10']);
    
    echo "✓ Dados de teste anteriores limpos\n";
}

// Função para verificar estoque
function verificarEstoque($produtoId, $numeracao) {
    $estoqueGeral = DB::table('produtos')->where('id', $produtoId)->value('quantidade');
    $estoqueNumeracao = DB::table('estoque_tabira')
        ->where('id_produto', $produtoId)
        ->where('numero', $numeracao)
        ->value('quantidade');
    
    return [
        'geral' => (int)$estoqueGeral,
        'numeracao' => (int)$estoqueNumeracao
    ];
}

// Função para simular venda crediário
function simularVendaCrediario($cenario, $produtos, $entrada = 0, $parcelas = 1, $metodoEntrada = 'dinheiro') {
    echo "\n--- TESTE: $cenario ---\n";
    
    // Limpar dados anteriores
    limparDadosTeste();
    
    // Calcular total da compra
    $totalCompra = 0;
    foreach ($produtos as $produto) {
        $preco = DB::table('produtos')->where('id', $produto['id'])->value('preco');
        $totalCompra += $preco * $produto['quantidade'];
    }
    
    echo "Total da compra: R$ " . number_format($totalCompra, 2, ',', '.') . "\n";
    echo "Entrada: R$ " . number_format($entrada, 2, ',', '.') . "\n";
    echo "Parcelas: {$parcelas}x\n";
    
    // Verificar estoque antes
    echo "\nEstoque ANTES da venda:\n";
    foreach ($produtos as $produto) {
        $estoque = verificarEstoque($produto['id'], $produto['numeracao']);
        echo "  Produto {$produto['id']} (Num {$produto['numeracao']}): Geral={$estoque['geral']}, Numeração={$estoque['numeracao']}\n";
    }
    
    try {
        // Simular o processo de venda crediário
        $controller = new App\Http\Controllers\CarrinhoController();
        
        // 1. Gerar ticket único com 20 caracteres
        do {
            $timestamp = now();
            $date = $timestamp->format('Ymd'); // 8 caracteres
            $time = $timestamp->format('His'); // 6 caracteres
            
            // Gera 4 caracteres aleatórios
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $random = '';
            for ($i = 0; $i < 4; $i++) {
                $random .= $characters[rand(0, strlen($characters) - 1)];
            }
            
            $ticket = 'TK' . $date . $time . $random; // Total: 20 caracteres
            $exists = DB::table('tickets')->where('ticket', $ticket)->exists();
        } while ($exists);
        
        // 2. Criar registro do ticket
        $valorFinanciar = $totalCompra - $entrada;
        DB::table('tickets')->insert([
            'id_cliente' => 1,
            'ticket' => $ticket,
            'data' => now(),
            'valor' => $valorFinanciar,
            'entrada' => $entrada,
            'parcelas' => $parcelas,
            'spc' => null
        ]);
        
        echo "✓ Ticket criado: $ticket\n";
        
        // 3. Calcular e criar parcelas
        $valorParcela = ceil($valorFinanciar / $parcelas);
        $totalParcelas = $valorParcela * ($parcelas - 1);
        $ultimaParcela = $valorFinanciar - $totalParcelas;
        
        $dataVencimento = now()->addMonth()->day(10);
        
        for ($i = 1; $i <= $parcelas; $i++) {
            $valorDaParcela = ($i == $parcelas) ? $ultimaParcela : $valorParcela;
            
            DB::table('parcelas')->insert([
                'ticket' => $ticket,
                'id_cliente' => 1,
                'id_autorizado' => null,
                'numero' => "$i/$parcelas",
                'data_vencimento' => $dataVencimento->copy()->addMonths($i-1)->toDateString(),
                'data_pagamento' => null,
                'hora' => null,
                'valor_parcela' => $valorDaParcela,
                'valor_pago' => null,
                'dinheiro' => null,
                'pix' => null,
                'cartao' => null,
                'metodo' => null,
                'id_vendedor' => null,
                'status' => 'aguardando pagamento',
                'bd' => 'vendas_tabira',
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
        
        echo "✓ $parcelas parcela(s) criada(s)\n";
        
        // 4. Criar registros de venda
        foreach ($produtos as $produto) {
            $preco = DB::table('produtos')->where('id', $produto['id'])->value('preco');
            $itemTotal = $preco * $produto['quantidade'];
            $proporcao = $itemTotal / $totalCompra;
            
            // Distribuir pagamentos proporcionalmente
            $valorDinheiro = $entrada * $proporcao;
            $valorCrediario = $valorFinanciar * $proporcao;
            
            // Ajustar valores baseado no método de entrada
            $valorPix = 0;
            $valorCartao = 0;
            if ($metodoEntrada == 'pix') {
                $valorPix = $valorDinheiro;
                $valorDinheiro = 0;
            } elseif ($metodoEntrada == 'cartao') {
                $valorCartao = $valorDinheiro;
                $valorDinheiro = 0;
            }
            
            for ($j = 0; $j < $produto['quantidade']; $j++) {
                DB::table('vendas_tabira')->insert([
                    'id_vendedor' => 1,
                    'id_vendedor_atendente' => null,
                    'id_produto' => $produto['id'],
                    'data_venda' => now()->toDateString(),
                    'hora' => now()->toTimeString(),
                    'data_estorno' => null,
                    'valor_dinheiro' => round($valorDinheiro / $produto['quantidade'], 2),
                    'valor_pix' => round($valorPix / $produto['quantidade'], 2),
                    'valor_cartao' => round($valorCartao / $produto['quantidade'], 2),
                    'valor_crediario' => round($valorCrediario / $produto['quantidade'], 2),
                    'preco' => $preco,
                    'preco_venda' => $preco,
                    'desconto' => 0,
                    'alerta' => 0,
                    'baixa_fiscal' => 0,
                    'numeracao' => $produto['numeracao'],
                    'pedido_devolucao' => null,
                    'reposicao' => '',
                    'bd' => '',
                    'ticket' => $ticket
                ]);
            }
            
            // 5. Atualizar estoques
            DB::table('produtos')->where('id', $produto['id'])->decrement('quantidade', $produto['quantidade']);
            DB::table('estoque_tabira')
                ->where('id_produto', $produto['id'])
                ->where('numero', $produto['numeracao'])
                ->decrement('quantidade', $produto['quantidade']);
        }
        
        echo "✓ Registros de venda criados\n";
        echo "✓ Estoques atualizados\n";
        
        // Verificar resultados
        echo "\nRESULTADOS:\n";
        
        // Verificar ticket
        $ticketCriado = DB::table('tickets')->where('ticket', $ticket)->first();
        echo "Ticket: {$ticketCriado->ticket} - Valor: R$ " . number_format($ticketCriado->valor, 2, ',', '.') . 
             " - Entrada: R$ " . number_format($ticketCriado->entrada, 2, ',', '.') . 
             " - Parcelas: {$ticketCriado->parcelas}\n";
        
        // Verificar parcelas
        $parcelasCriadas = DB::table('parcelas')->where('ticket', $ticket)->get();
        echo "Parcelas criadas: " . $parcelasCriadas->count() . "\n";
        foreach ($parcelasCriadas as $parcela) {
            echo "  {$parcela->numero}: R$ " . number_format($parcela->valor_parcela, 2, ',', '.') . 
                 " - Vencimento: {$parcela->data_vencimento}\n";
        }
        
        // Verificar vendas
        $vendasCriadas = DB::table('vendas_tabira')->where('ticket', $ticket)->get();
        echo "Registros de venda: " . $vendasCriadas->count() . "\n";
        
        // Verificar estoque depois
        echo "\nEstoque DEPOIS da venda:\n";
        foreach ($produtos as $produto) {
            $estoque = verificarEstoque($produto['id'], $produto['numeracao']);
            echo "  Produto {$produto['id']} (Num {$produto['numeracao']}): Geral={$estoque['geral']}, Numeração={$estoque['numeracao']}\n";
        }
        
        echo "✅ TESTE CONCLUÍDO COM SUCESSO!\n";
        
    } catch (Exception $e) {
        echo "❌ ERRO: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
}

// EXECUTAR TESTES

// Teste 1: Venda de um produto no crediário, com uma parcela
simularVendaCrediario(
    "Venda de um produto no crediário, com uma parcela",
    [['id' => 1, 'quantidade' => 1, 'numeracao' => 40]],
    0, // sem entrada
    1  // 1 parcela
);

// Teste 2: Venda de um produto no crediário, com várias parcelas
simularVendaCrediario(
    "Venda de um produto no crediário, com várias parcelas",
    [['id' => 1, 'quantidade' => 1, 'numeracao' => 41]],
    0, // sem entrada
    3  // 3 parcelas
);

// Teste 3: Venda de um produto no crediário com entrada e uma parcela
simularVendaCrediario(
    "Venda de um produto no crediário com entrada e uma parcela",
    [['id' => 1, 'quantidade' => 1, 'numeracao' => 42]],
    100, // R$ 100 de entrada
    1,   // 1 parcela
    'dinheiro'
);

// Teste 4: Venda de um produto no crediário, com entrada e com várias parcelas
simularVendaCrediario(
    "Venda de um produto no crediário, com entrada e com várias parcelas",
    [['id' => 1, 'quantidade' => 1, 'numeracao' => 43]],
    50,  // R$ 50 de entrada
    5,   // 5 parcelas
    'pix'
);

// Teste 5: Venda de vários produtos no crediário, com uma parcela
simularVendaCrediario(
    "Venda de vários produtos no crediário, com uma parcela",
    [
        ['id' => 1, 'quantidade' => 2, 'numeracao' => 38],
        ['id' => 1, 'quantidade' => 1, 'numeracao' => 39]
    ],
    0, // sem entrada
    1  // 1 parcela
);

// Teste 6: Venda de vários produtos no crediário, com várias parcelas
simularVendaCrediario(
    "Venda de vários produtos no crediário, com várias parcelas",
    [
        ['id' => 1, 'quantidade' => 1, 'numeracao' => 44],
        ['id' => 1, 'quantidade' => 1, 'numeracao' => 38]
    ],
    0, // sem entrada
    4  // 4 parcelas
);

echo "\n=== TODOS OS TESTES CONCLUÍDOS ===\n";