<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTE DO FLUXO REAL DE VENDA CREDIÁRIO ===\n\n";

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

echo "\n1. SIMULANDO PROCESSO COMPLETO DE VENDA CREDIÁRIO\n";
echo "=" . str_repeat("=", 50) . "\n";

$clienteAntes = verificarCliente(1);
echo "Cliente ANTES:\n";
echo "  Limite: R$ " . number_format($clienteAntes['limite'], 2, ',', '.') . "\n";
echo "  Token: " . ($clienteAntes['token'] ?? 'NULL') . "\n\n";

try {
    // Simular o fluxo completo
    $controller = new App\Http\Controllers\CarrinhoController();
    
    // 1. Configurar sessões como no fluxo real
    Session::put('cliente_crediario', [
        'id' => 1,
        'nome' => 'João Silva',
        'rg' => '1234567',
        'cpf' => '12345678901',
        'limite' => 500.00,
        'token' => '123456'
    ]);
    
    Session::put('carrinho', [
        '1-40' => [
            'id' => 1,
            'nome' => 'Tênis Nike Air Max',
            'preco' => 800.00,
            'numeracao' => 40,
            'quantidade' => 1,
            'foto' => 'tenis_nike.jpg'
        ]
    ]);
    
    echo "2. PROCESSANDO VENDA CREDIÁRIO\n";
    echo "Dados da venda:\n";
    echo "  Total: R$ 800,00\n";
    echo "  Entrada: R$ 200,00 (dinheiro)\n";
    echo "  Crediário: R$ 600,00\n";
    echo "  Parcelas: 3x\n\n";
    
    // 2. Simular processamento da venda crediário
    $vendaCrediarioData = [
        'ticket' => 'TK20250810000000TEST',
        'valor_entrada' => 200.00,
        'metodo_entrada' => 'dinheiro',
        'valor_crediario' => 600.00
    ];
    
    Session::put('venda_crediario_data', $vendaCrediarioData);
    
    // 3. Simular apenas a parte das modificações pós-venda
    echo "3. EXECUTANDO MODIFICAÇÕES PÓS-VENDA\n";
    
    $reflection = new ReflectionClass($controller);
    $processMethod = $reflection->getMethod('processPostSaleModifications');
    $processMethod->setAccessible(true);
    
    echo "Chamando processPostSaleModifications...\n";
    $processMethod->invoke($controller, $vendaCrediarioData);
    
    // 4. Verificar resultado
    $clienteDepois = verificarCliente(1);
    echo "\nCliente DEPOIS:\n";
    echo "  Limite: R$ " . number_format($clienteDepois['limite'], 2, ',', '.') . "\n";
    echo "  Token: " . ($clienteDepois['token'] ?? 'NULL') . "\n\n";
    
    // 5. Análise
    $aumentoLimite = $clienteDepois['limite'] - $clienteAntes['limite'];
    $tokenLimpo = $clienteDepois['token'] === null;
    
    echo "4. ANÁLISE DOS RESULTADOS\n";
    echo "Aumento do limite: R$ " . number_format($aumentoLimite, 2, ',', '.') . "\n";
    echo "Token limpo: " . ($tokenLimpo ? 'Sim' : 'Não') . "\n\n";
    
    // 6. Verificações detalhadas
    echo "5. VERIFICAÇÕES DETALHADAS\n";
    
    // Verificar se a função foi chamada corretamente
    $clienteCrediario = Session::get('cliente_crediario');
    echo "Cliente na sessão: " . ($clienteCrediario ? 'Sim' : 'Não') . "\n";
    if ($clienteCrediario) {
        echo "  ID do cliente: " . $clienteCrediario['id'] . "\n";
    }
    
    // Verificar logs (se possível)
    echo "\nTentando verificar logs...\n";
    
    // Testar diretamente as funções individuais
    echo "\n6. TESTE DIRETO DAS FUNÇÕES\n";
    
    // Restaurar cliente para testar novamente
    DB::table('clientes')->where('id', 1)->update(['limite' => 500.00, 'token' => '123456']);
    
    $processLimitMethod = $reflection->getMethod('processLimitIncrease');
    $processLimitMethod->setAccessible(true);
    
    $clearTokenMethod = $reflection->getMethod('clearClientToken');
    $clearTokenMethod->setAccessible(true);
    
    echo "Testando processLimitIncrease diretamente...\n";
    $processLimitMethod->invoke($controller, 1, $vendaCrediarioData);
    
    echo "Testando clearClientToken diretamente...\n";
    $clearTokenMethod->invoke($controller, 1);
    
    $clienteFinal = verificarCliente(1);
    echo "\nCliente após testes diretos:\n";
    echo "  Limite: R$ " . number_format($clienteFinal['limite'], 2, ',', '.') . "\n";
    echo "  Token: " . ($clienteFinal['token'] ?? 'NULL') . "\n";
    
    $aumentoFinal = $clienteFinal['limite'] - 500.00;
    $tokenFinalLimpo = $clienteFinal['token'] === null;
    
    echo "\nRESULTADO FINAL:\n";
    echo "Limite aumentado: " . ($aumentoFinal > 0 ? "✅ R$ " . number_format($aumentoFinal, 2, ',', '.') : "❌ Não aumentado") . "\n";
    echo "Token limpo: " . ($tokenFinalLimpo ? "✅ Sim" : "❌ Não") . "\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";