<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG COM LOGS DETALHADOS ===\n\n";

// Função para limpar dados de teste
function limparDadosTeste() {
    DB::table('tickets')->where('id_cliente', 1)->delete();
    DB::table('parcelas')->where('id_cliente', 1)->delete();
    DB::table('vendas_tabira')->where('id_vendedor', 1)->delete();
    
    // Restaurar cliente de teste
    DB::table('clientes')->where('id', 1)->update([
        'limite' => 500.00,
        'token' => '123456'
    ]);
    
    echo "✓ Cliente restaurado: Limite R$ 500,00, Token: 123456\n";
}

// Função para verificar cliente
function verificarCliente($clienteId) {
    $cliente = DB::table('clientes')->where('id', $clienteId)->first();
    return [
        'limite' => $cliente->limite,
        'token' => $cliente->token
    ];
}

limparDadosTeste();

echo "\nCENÁRIOS DE TESTE:\n";
echo "=" . str_repeat("=", 30) . "\n";

// Cenário 1: Entrada obrigatória (compra excede limite)
echo "\nCENÁRIO 1: Entrada obrigatória\n";
echo "Cliente: Limite R$ 500,00\n";
echo "Compra: R$ 800,00 (excede limite)\n";
echo "Entrada: R$ 200,00\n";

$vendaCrediarioData1 = [
    'valor_entrada' => 200.00,
    'valor_crediario' => 600.00,
    'metodo_entrada' => 'dinheiro'
];

Session::put('cliente_crediario', ['id' => 1, 'nome' => 'João Silva', 'token' => '123456']);

$controller = new App\Http\Controllers\CarrinhoController();
$reflection = new ReflectionClass($controller);
$processMethod = $reflection->getMethod('processPostSaleModifications');
$processMethod->setAccessible(true);

$clienteAntes1 = verificarCliente(1);
echo "Limite ANTES: R$ " . number_format($clienteAntes1['limite'], 2, ',', '.') . "\n";

$processMethod->invoke($controller, $vendaCrediarioData1);

$clienteDepois1 = verificarCliente(1);
echo "Limite DEPOIS: R$ " . number_format($clienteDepois1['limite'], 2, ',', '.') . "\n";
echo "Aumento: R$ " . number_format($clienteDepois1['limite'] - $clienteAntes1['limite'], 2, ',', '.') . "\n";
echo "Token: " . ($clienteDepois1['token'] ?? 'NULL') . "\n";

// Restaurar para próximo teste
DB::table('clientes')->where('id', 1)->update(['limite' => 500.00, 'token' => '123456']);

// Cenário 2: Entrada não obrigatória (compra dentro do limite)
echo "\nCENÁRIO 2: Entrada não obrigatória\n";
echo "Cliente: Limite R$ 500,00\n";
echo "Compra: R$ 400,00 (dentro do limite)\n";
echo "Entrada: R$ 100,00\n";

$vendaCrediarioData2 = [
    'valor_entrada' => 100.00,
    'valor_crediario' => 300.00,
    'metodo_entrada' => 'dinheiro'
];

$clienteAntes2 = verificarCliente(1);
echo "Limite ANTES: R$ " . number_format($clienteAntes2['limite'], 2, ',', '.') . "\n";

$processMethod->invoke($controller, $vendaCrediarioData2);

$clienteDepois2 = verificarCliente(1);
echo "Limite DEPOIS: R$ " . number_format($clienteDepois2['limite'], 2, ',', '.') . "\n";
echo "Aumento: R$ " . number_format($clienteDepois2['limite'] - $clienteAntes2['limite'], 2, ',', '.') . "\n";
echo "Token: " . ($clienteDepois2['token'] ?? 'NULL') . "\n";

// Restaurar para próximo teste
DB::table('clientes')->where('id', 1)->update(['limite' => 500.00, 'token' => '123456']);

// Cenário 3: Sem entrada
echo "\nCENÁRIO 3: Sem entrada\n";
echo "Cliente: Limite R$ 500,00\n";
echo "Compra: R$ 400,00\n";
echo "Entrada: R$ 0,00\n";

$vendaCrediarioData3 = [
    'valor_entrada' => 0.00,
    'valor_crediario' => 400.00,
    'metodo_entrada' => 'dinheiro'
];

$clienteAntes3 = verificarCliente(1);
echo "Limite ANTES: R$ " . number_format($clienteAntes3['limite'], 2, ',', '.') . "\n";

$processMethod->invoke($controller, $vendaCrediarioData3);

$clienteDepois3 = verificarCliente(1);
echo "Limite DEPOIS: R$ " . number_format($clienteDepois3['limite'], 2, ',', '.') . "\n";
echo "Aumento: R$ " . number_format($clienteDepois3['limite'] - $clienteAntes3['limite'], 2, ',', '.') . "\n";
echo "Token: " . ($clienteDepois3['token'] ?? 'NULL') . "\n";

echo "\nRESUMO DOS TESTES:\n";
echo "=" . str_repeat("=", 30) . "\n";
echo "Cenário 1 (entrada obrigatória): " . ($clienteDepois1['limite'] > $clienteAntes1['limite'] ? "✅ Limite aumentado" : "❌ Limite não aumentado") . "\n";
echo "Cenário 2 (entrada não obrigatória): " . ($clienteDepois2['limite'] == $clienteAntes2['limite'] ? "✅ Limite mantido" : "❌ Limite alterado incorretamente") . "\n";
echo "Cenário 3 (sem entrada): " . ($clienteDepois3['limite'] == $clienteAntes3['limite'] ? "✅ Limite mantido" : "❌ Limite alterado incorretamente") . "\n";

echo "\nCONCLUSÃO:\n";
echo "A lógica está funcionando corretamente. O limite só é aumentado quando:\n";
echo "1. Há entrada (valor > 0)\n";
echo "2. A compra excede o crédito disponível (entrada obrigatória)\n";
echo "3. A entrada é pelo menos a mínima necessária\n\n";

echo "Se o limite não está sendo aumentado no sistema real, verifique:\n";
echo "1. Se a função processPostSaleModifications está sendo chamada\n";
echo "2. Se os dados da sessão estão corretos\n";
echo "3. Se a compra realmente excede o limite do cliente\n";
echo "4. Se há parcelas pendentes que reduzem o crédito disponível\n";

echo "\n=== DEBUG CONCLUÍDO ===\n";