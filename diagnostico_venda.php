<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DIAGNÓSTICO DE VENDA CREDIÁRIO ===\n\n";

// Solicitar dados da venda para diagnóstico
echo "Para diagnosticar por que o limite não foi aumentado, forneça os dados:\n\n";

// Você pode alterar estes valores para testar uma venda específica
$clienteId = 1; // ID do cliente
$valorCompra = 800.00; // Valor total da compra
$valorEntrada = 200.00; // Valor da entrada

echo "DADOS DA VENDA:\n";
echo "Cliente ID: {$clienteId}\n";
echo "Valor da compra: R$ " . number_format($valorCompra, 2, ',', '.') . "\n";
echo "Valor da entrada: R$ " . number_format($valorEntrada, 2, ',', '.') . "\n\n";

try {
    // 1. Verificar dados do cliente
    echo "1. VERIFICANDO DADOS DO CLIENTE\n";
    echo "=" . str_repeat("=", 35) . "\n";
    
    $cliente = DB::table('clientes')->where('id', $clienteId)->first();
    if (!$cliente) {
        echo "❌ Cliente não encontrado!\n";
        exit;
    }
    
    echo "Nome: {$cliente->nome}\n";
    echo "Limite: R$ " . number_format($cliente->limite, 2, ',', '.') . "\n";
    echo "Status: {$cliente->status}\n";
    echo "Token: " . ($cliente->token ?? 'NULL') . "\n\n";
    
    // 2. Verificar parcelas pendentes
    echo "2. VERIFICANDO PARCELAS PENDENTES\n";
    echo "=" . str_repeat("=", 35) . "\n";
    
    $parcelasPendentes = DB::table('parcelas')
        ->where('id_cliente', $clienteId)
        ->where('status', 'aguardando pagamento')
        ->get();
    
    $totalPendente = $parcelasPendentes->sum('valor_parcela');
    
    echo "Parcelas pendentes: " . $parcelasPendentes->count() . "\n";
    echo "Valor total pendente: R$ " . number_format($totalPendente, 2, ',', '.') . "\n";
    
    if ($parcelasPendentes->count() > 0) {
        echo "Detalhes das parcelas pendentes:\n";
        foreach ($parcelasPendentes as $parcela) {
            echo "  - Ticket: {$parcela->ticket}, Valor: R$ " . number_format($parcela->valor_parcela, 2, ',', '.') . ", Vencimento: {$parcela->data_vencimento}\n";
        }
    }
    echo "\n";
    
    // 3. Calcular crédito disponível
    echo "3. CALCULANDO CRÉDITO DISPONÍVEL\n";
    echo "=" . str_repeat("=", 35) . "\n";
    
    $creditoDisponivel = $cliente->limite - $totalPendente;
    
    echo "Limite do cliente: R$ " . number_format($cliente->limite, 2, ',', '.') . "\n";
    echo "Parcelas pendentes: R$ " . number_format($totalPendente, 2, ',', '.') . "\n";
    echo "Crédito disponível: R$ " . number_format($creditoDisponivel, 2, ',', '.') . "\n\n";
    
    // 4. Verificar se entrada é obrigatória
    echo "4. VERIFICANDO SE ENTRADA É OBRIGATÓRIA\n";
    echo "=" . str_repeat("=", 40) . "\n";
    
    $entradaObrigatoria = $valorCompra > $creditoDisponivel;
    
    echo "Valor da compra: R$ " . number_format($valorCompra, 2, ',', '.') . "\n";
    echo "Crédito disponível: R$ " . number_format($creditoDisponivel, 2, ',', '.') . "\n";
    echo "Entrada obrigatória: " . ($entradaObrigatoria ? 'SIM' : 'NÃO') . "\n";
    
    if ($entradaObrigatoria) {
        $excesso = $valorCompra - $creditoDisponivel;
        $entradaMinima = $excesso / 2;
        
        echo "Excesso: R$ " . number_format($excesso, 2, ',', '.') . "\n";
        echo "Entrada mínima: R$ " . number_format($entradaMinima, 2, ',', '.') . "\n";
        echo "Entrada fornecida: R$ " . number_format($valorEntrada, 2, ',', '.') . "\n";
        echo "Entrada suficiente: " . ($valorEntrada >= $entradaMinima ? 'SIM' : 'NÃO') . "\n";
    }
    echo "\n";
    
    // 5. Conclusão
    echo "5. CONCLUSÃO\n";
    echo "=" . str_repeat("=", 15) . "\n";
    
    if ($valorEntrada <= 0) {
        echo "❌ LIMITE NÃO SERÁ AUMENTADO: Não houve entrada\n";
    } elseif (!$entradaObrigatoria) {
        echo "❌ LIMITE NÃO SERÁ AUMENTADO: Entrada não era obrigatória\n";
        echo "   A compra (R$ " . number_format($valorCompra, 2, ',', '.') . ") não excede o crédito disponível (R$ " . number_format($creditoDisponivel, 2, ',', '.') . ")\n";
    } elseif ($valorEntrada < $entradaMinima) {
        echo "❌ LIMITE NÃO SERÁ AUMENTADO: Entrada insuficiente\n";
        echo "   Entrada mínima: R$ " . number_format($entradaMinima, 2, ',', '.') . "\n";
        echo "   Entrada fornecida: R$ " . number_format($valorEntrada, 2, ',', '.') . "\n";
    } else {
        echo "✅ LIMITE DEVE SER AUMENTADO: Todas as condições atendidas\n";
        echo "   Aumento esperado: R$ " . number_format($valorEntrada, 2, ',', '.') . "\n";
        echo "   Novo limite: R$ " . number_format($cliente->limite + $valorEntrada, 2, ',', '.') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNÓSTICO CONCLUÍDO ===\n";