<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTE FINAL DA CORREÇÃO ===\n\n";

// Limpar e restaurar cliente
DB::table('clientes')->where('id', 1)->update([
    'limite' => 1000.00,  // Limite maior para testar cenário real
    'token' => '123456'   // Token numérico
]);

echo "Cliente configurado: Limite R$ 1.000,00, Token: 123456\n\n";

// Configurar sessão como no sistema real
Session::put('cliente_crediario', [
    'id' => 1,
    'nome' => 'Cliente Teste',
    'rg' => '1234567',
    'cpf' => '12345678901',
    'limite' => 1000.00,
    'token' => '123456'
]);

// Simular venda com entrada
$vendaCrediarioData = [
    'ticket' => 'TK20250810123456ABCD',
    'valor_entrada' => 300.00,  // Entrada de R$ 300
    'metodo_entrada' => 'dinheiro',
    'valor_crediario' => 700.00  // Total da compra: R$ 1.000
];

echo "SIMULANDO VENDA CREDIÁRIO:\n";
echo "Valor total: R$ " . number_format($vendaCrediarioData['valor_entrada'] + $vendaCrediarioData['valor_crediario'], 2, ',', '.') . "\n";
echo "Entrada: R$ " . number_format($vendaCrediarioData['valor_entrada'], 2, ',', '.') . "\n";
echo "Crediário: R$ " . number_format($vendaCrediarioData['valor_crediario'], 2, ',', '.') . "\n\n";

// Verificar cliente antes
$clienteAntes = DB::table('clientes')->where('id', 1)->first();
echo "ANTES DA FINALIZAÇÃO:\n";
echo "Limite: R$ " . number_format($clienteAntes->limite, 2, ',', '.') . "\n";
echo "Token: " . ($clienteAntes->token ?? 'NULL') . "\n\n";

try {
    // Executar as modificações
    $controller = new App\Http\Controllers\CarrinhoController();
    $reflection = new ReflectionClass($controller);
    $processMethod = $reflection->getMethod('processPostSaleModifications');
    $processMethod->setAccessible(true);
    
    echo "EXECUTANDO MODIFICAÇÕES PÓS-VENDA...\n";
    $processMethod->invoke($controller, $vendaCrediarioData);
    
    // Verificar cliente depois
    $clienteDepois = DB::table('clientes')->where('id', 1)->first();
    echo "\nAPÓS A FINALIZAÇÃO:\n";
    echo "Limite: R$ " . number_format($clienteDepois->limite, 2, ',', '.') . "\n";
    echo "Token: " . ($clienteDepois->token ?? 'NULL') . "\n\n";
    
    // Calcular diferenças
    $aumentoLimite = $clienteDepois->limite - $clienteAntes->limite;
    $tokenLimpo = $clienteDepois->token === null;
    
    echo "RESULTADO:\n";
    echo "Aumento do limite: R$ " . number_format($aumentoLimite, 2, ',', '.') . "\n";
    echo "Token limpo: " . ($tokenLimpo ? 'SIM' : 'NÃO') . "\n\n";
    
    // Verificação final
    if ($aumentoLimite == $vendaCrediarioData['valor_entrada'] && $tokenLimpo) {
        echo "🎉 SUCESSO! A correção está funcionando perfeitamente!\n";
        echo "✅ Limite aumentado no valor da entrada: R$ " . number_format($vendaCrediarioData['valor_entrada'], 2, ',', '.') . "\n";
        echo "✅ Token definido como NULL\n";
        echo "✅ Novo limite: R$ " . number_format($clienteDepois->limite, 2, ',', '.') . "\n";
    } else {
        echo "❌ PROBLEMA DETECTADO:\n";
        if ($aumentoLimite != $vendaCrediarioData['valor_entrada']) {
            echo "- Limite não aumentou corretamente\n";
        }
        if (!$tokenLimpo) {
            echo "- Token não foi limpo\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "INSTRUÇÕES PARA O SISTEMA REAL:\n";
echo "1. A função processPostSaleModifications deve ser chamada após finalizarCompraCrediario\n";
echo "2. Verifique os logs do Laravel para mensagens como:\n";
echo "   - 'ProcessPostSaleModifications - Iniciando modificações pós-venda'\n";
echo "   - 'ProcessLimitIncrease - Limite do cliente ID X aumentado'\n";
echo "3. Se não vir essas mensagens, a função não está sendo chamada\n";
echo "4. Agora a lógica é simples: se há entrada > 0, o limite é aumentado\n";
echo str_repeat("=", 50) . "\n";

echo "\n=== TESTE FINAL CONCLUÍDO ===\n";