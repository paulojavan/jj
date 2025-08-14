<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTE DAS MODIFICAÇÕES NA FINALIZAÇÃO ===\n\n";

// Função para limpar dados de teste
function limparDadosTeste() {
    DB::table('tickets')->where('id_cliente', 1)->delete();
    DB::table('parcelas')->where('id_cliente', 1)->delete();
    DB::table('vendas_tabira')->where('id_vendedor', 1)->delete();
    
    // Restaurar cliente de teste
    DB::table('clientes')->where('id', 1)->update([
        'limite' => 500.00, // Limite baixo para forçar entrada obrigatória
        'token' => '123456'  // Token inicial
    ]);
    
    echo "✓ Dados de teste limpos e cliente restaurado\n";
}

// Função para verificar cliente
function verificarCliente($clienteId) {
    $cliente = DB::table('clientes')->where('id', $clienteId)->first();
    return [
        'limite' => $cliente->limite,
        'token' => $cliente->token
    ];
}

echo "TESTE: Modificações na finalização da venda crediário\n";
echo "=" . str_repeat("=", 55) . "\n";

// Limpar dados anteriores
limparDadosTeste();

// Verificar cliente antes
$clienteAntes = verificarCliente(1);
echo "Cliente ANTES da finalização:\n";
echo "  Limite: R$ " . number_format($clienteAntes['limite'], 2, ',', '.') . "\n";
echo "  Token: " . ($clienteAntes['token'] ?? 'NULL') . "\n\n";

try {
    // Simular dados da venda crediário com entrada obrigatória
    $controller = new App\Http\Controllers\CarrinhoController();
    
    // Simular dados da sessão
    Session::put('cliente_crediario', [
        'id' => 1,
        'nome' => 'João Silva',
        'token' => '123456'
    ]);
    
    // Simular venda com entrada obrigatória
    // Cliente tem limite de R$ 500, compra de R$ 800 (excede em R$ 300)
    // Entrada de R$ 200 (maior que mínima de R$ 150)
    $vendaCrediarioData = [
        'ticket' => 'TK20250810000000TEST',
        'valor_entrada' => 200.00,  // Entrada obrigatória
        'metodo_entrada' => 'dinheiro',
        'valor_crediario' => 600.00
    ];
    
    $totalCompra = $vendaCrediarioData['valor_entrada'] + $vendaCrediarioData['valor_crediario'];
    
    echo "Simulando finalização de venda:\n";
    echo "  Total da compra: R$ " . number_format($totalCompra, 2, ',', '.') . "\n";
    echo "  Entrada: R$ " . number_format($vendaCrediarioData['valor_entrada'], 2, ',', '.') . "\n";
    echo "  Crediário: R$ " . number_format($vendaCrediarioData['valor_crediario'], 2, ',', '.') . "\n";
    echo "  Limite atual: R$ " . number_format($clienteAntes['limite'], 2, ',', '.') . "\n";
    echo "  Excesso: R$ " . number_format($totalCompra - $clienteAntes['limite'], 2, ',', '.') . "\n\n";
    
    // Usar reflexão para acessar método privado
    $reflection = new ReflectionClass($controller);
    $processMethod = $reflection->getMethod('processPostSaleModifications');
    $processMethod->setAccessible(true);
    
    // Executar as modificações pós-venda
    echo "Executando modificações pós-venda...\n";
    $processMethod->invoke($controller, $vendaCrediarioData);
    
    // Verificar cliente depois
    $clienteDepois = verificarCliente(1);
    echo "\nCliente DEPOIS das modificações:\n";
    echo "  Limite: R$ " . number_format($clienteDepois['limite'], 2, ',', '.') . "\n";
    echo "  Token: " . ($clienteDepois['token'] ?? 'NULL') . "\n\n";
    
    // Análise dos resultados
    $aumentoLimite = $clienteDepois['limite'] - $clienteAntes['limite'];
    $tokenLimpo = $clienteDepois['token'] === null;
    
    echo "RESULTADOS:\n";
    echo "  Aumento do limite: R$ " . number_format($aumentoLimite, 2, ',', '.') . "\n";
    echo "  Token limpo: " . ($tokenLimpo ? 'Sim' : 'Não') . "\n\n";
    
    // Verificações
    $teste1Passou = $aumentoLimite == $vendaCrediarioData['valor_entrada'];
    $teste2Passou = $tokenLimpo;
    
    echo "VERIFICAÇÕES:\n";
    echo "1. Limite aumentado no valor da entrada: " . ($teste1Passou ? "✅ PASSOU" : "❌ FALHOU") . "\n";
    echo "   Esperado: R$ " . number_format($vendaCrediarioData['valor_entrada'], 2, ',', '.') . "\n";
    echo "   Obtido: R$ " . number_format($aumentoLimite, 2, ',', '.') . "\n\n";
    
    echo "2. Token definido como NULL: " . ($teste2Passou ? "✅ PASSOU" : "❌ FALHOU") . "\n";
    echo "   Esperado: NULL\n";
    echo "   Obtido: " . ($clienteDepois['token'] ?? 'NULL') . "\n\n";
    
    if ($teste1Passou && $teste2Passou) {
        echo "🎉 TODOS OS TESTES PASSARAM! As modificações estão funcionando corretamente.\n";
    } else {
        echo "❌ ALGUNS TESTES FALHARAM. Verifique a implementação.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO durante o teste: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";