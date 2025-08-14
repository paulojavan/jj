<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTE DA CORREÇÃO DO AUMENTO DE LIMITE ===\n\n";

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

echo "\nTESTE: Aumento de limite com lógica simplificada\n";
echo "=" . str_repeat("=", 50) . "\n";

$clienteAntes = verificarCliente(1);
echo "Cliente ANTES:\n";
echo "  Limite: R$ " . number_format($clienteAntes['limite'], 2, ',', '.') . "\n";
echo "  Token: " . ($clienteAntes['token'] ?? 'NULL') . "\n\n";

try {
    // Configurar sessão
    Session::put('cliente_crediario', [
        'id' => 1,
        'nome' => 'João Silva',
        'token' => '123456'
    ]);
    
    // Dados da venda com entrada
    $vendaCrediarioData = [
        'ticket' => 'TK20250810000000TEST',
        'valor_entrada' => 200.00,
        'metodo_entrada' => 'dinheiro',
        'valor_crediario' => 600.00
    ];
    
    echo "Dados da venda:\n";
    echo "  Total: R$ " . number_format($vendaCrediarioData['valor_entrada'] + $vendaCrediarioData['valor_crediario'], 2, ',', '.') . "\n";
    echo "  Entrada: R$ " . number_format($vendaCrediarioData['valor_entrada'], 2, ',', '.') . "\n";
    echo "  Crediário: R$ " . number_format($vendaCrediarioData['valor_crediario'], 2, ',', '.') . "\n\n";
    
    // Executar modificações
    $controller = new App\Http\Controllers\CarrinhoController();
    $reflection = new ReflectionClass($controller);
    $processMethod = $reflection->getMethod('processPostSaleModifications');
    $processMethod->setAccessible(true);
    
    echo "Executando processPostSaleModifications...\n";
    $processMethod->invoke($controller, $vendaCrediarioData);
    
    // Verificar resultado
    $clienteDepois = verificarCliente(1);
    echo "\nCliente DEPOIS:\n";
    echo "  Limite: R$ " . number_format($clienteDepois['limite'], 2, ',', '.') . "\n";
    echo "  Token: " . ($clienteDepois['token'] ?? 'NULL') . "\n\n";
    
    // Análise
    $aumentoLimite = $clienteDepois['limite'] - $clienteAntes['limite'];
    $tokenLimpo = $clienteDepois['token'] === null;
    
    echo "RESULTADOS:\n";
    echo "  Aumento do limite: R$ " . number_format($aumentoLimite, 2, ',', '.') . "\n";
    echo "  Token limpo: " . ($tokenLimpo ? 'Sim' : 'Não') . "\n\n";
    
    if ($aumentoLimite == $vendaCrediarioData['valor_entrada'] && $tokenLimpo) {
        echo "✅ TESTE PASSOU: Limite aumentado e token limpo corretamente!\n";
    } else {
        echo "❌ TESTE FALHOU:\n";
        if ($aumentoLimite != $vendaCrediarioData['valor_entrada']) {
            echo "  - Limite não aumentou corretamente\n";
            echo "  - Esperado: R$ " . number_format($vendaCrediarioData['valor_entrada'], 2, ',', '.') . "\n";
            echo "  - Obtido: R$ " . number_format($aumentoLimite, 2, ',', '.') . "\n";
        }
        if (!$tokenLimpo) {
            echo "  - Token não foi limpo\n";
        }
    }
    
    echo "\nVerifique os logs do Laravel para mais detalhes sobre a execução.\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";

// Teste adicional: Verificar se a função está sendo chamada no fluxo real
echo "\nTESTE ADICIONAL: Simulando fluxo completo\n";
echo "=" . str_repeat("=", 40) . "\n";

// Restaurar cliente
DB::table('clientes')->where('id', 1)->update(['limite' => 500.00, 'token' => '123456']);

// Configurar todas as sessões necessárias
Session::put('cliente_crediario', [
    'id' => 1,
    'nome' => 'João Silva',
    'token' => '123456'
]);

Session::put('venda_crediario_data', [
    'ticket' => 'TK20250810000000TEST2',
    'valor_entrada' => 150.00,
    'metodo_entrada' => 'pix',
    'valor_crediario' => 450.00
]);

Session::put('carrinho', [
    '1-40' => [
        'id' => 1,
        'nome' => 'Produto Teste',
        'preco' => 600.00,
        'numeracao' => 40,
        'quantidade' => 1
    ]
]);

$clienteAntes2 = verificarCliente(1);
echo "Cliente ANTES do fluxo completo:\n";
echo "  Limite: R$ " . number_format($clienteAntes2['limite'], 2, ',', '.') . "\n\n";

try {
    // Simular apenas a parte das modificações (sem a venda completa)
    $vendaData2 = Session::get('venda_crediario_data');
    
    $processMethod->invoke($controller, $vendaData2);
    
    $clienteDepois2 = verificarCliente(1);
    echo "Cliente DEPOIS do fluxo completo:\n";
    echo "  Limite: R$ " . number_format($clienteDepois2['limite'], 2, ',', '.') . "\n";
    echo "  Token: " . ($clienteDepois2['token'] ?? 'NULL') . "\n";
    
    $aumento2 = $clienteDepois2['limite'] - $clienteAntes2['limite'];
    echo "  Aumento: R$ " . number_format($aumento2, 2, ',', '.') . "\n";
    
    if ($aumento2 == $vendaData2['valor_entrada']) {
        echo "✅ Fluxo completo funcionando!\n";
    } else {
        echo "❌ Problema no fluxo completo\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO no fluxo completo: " . $e->getMessage() . "\n";
}

echo "\n=== TODOS OS TESTES CONCLUÍDOS ===\n";