<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG: AUMENTO DE LIMITE ===\n\n";

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

$clienteId = 1;
$vendaCrediarioData = [
    'valor_entrada' => 200.00,
    'valor_crediario' => 600.00,
    'metodo_entrada' => 'dinheiro'
];

echo "\nDados da venda:\n";
echo "  Entrada: R$ " . number_format($vendaCrediarioData['valor_entrada'], 2, ',', '.') . "\n";
echo "  Crediário: R$ " . number_format($vendaCrediarioData['valor_crediario'], 2, ',', '.') . "\n";
echo "  Total: R$ " . number_format($vendaCrediarioData['valor_entrada'] + $vendaCrediarioData['valor_crediario'], 2, ',', '.') . "\n\n";

try {
    $controller = new App\Http\Controllers\CarrinhoController();
    $reflection = new ReflectionClass($controller);
    
    // Debug passo a passo
    echo "PASSO 1: Verificando se houve entrada\n";
    $valorEntrada = $vendaCrediarioData['valor_entrada'] ?? 0;
    echo "  Valor da entrada: R$ " . number_format($valorEntrada, 2, ',', '.') . "\n";
    echo "  Entrada > 0? " . ($valorEntrada > 0 ? 'Sim' : 'Não') . "\n\n";
    
    if ($valorEntrada > 0) {
        echo "PASSO 2: Calculando total da compra\n";
        $totalCompra = $valorEntrada + $vendaCrediarioData['valor_crediario'];
        echo "  Total da compra: R$ " . number_format($totalCompra, 2, ',', '.') . "\n\n";
        
        echo "PASSO 3: Buscando informações do cliente\n";
        $cliente = DB::table('clientes')->where('id', $clienteId)->first();
        if (!$cliente) {
            echo "  ❌ Cliente não encontrado!\n";
            return;
        }
        echo "  Cliente encontrado - Limite atual: R$ " . number_format($cliente->limite, 2, ',', '.') . "\n\n";
        
        echo "PASSO 4: Calculando crédito disponível\n";
        $calculateMethod = $reflection->getMethod('calculateAvailableCredit');
        $calculateMethod->setAccessible(true);
        $creditInfo = $calculateMethod->invoke($controller, $clienteId);
        
        echo "  Crédito disponível: R$ " . number_format($creditInfo['available_credit'], 2, ',', '.') . "\n";
        echo "  Parcelas pendentes: R$ " . number_format($creditInfo['pending_amount'], 2, ',', '.') . "\n\n";
        
        echo "PASSO 5: Verificando se entrada era obrigatória\n";
        echo "  Total compra (R$ " . number_format($totalCompra, 2, ',', '.') . ") > Crédito disponível (R$ " . number_format($creditInfo['available_credit'], 2, ',', '.') . ")?\n";
        $entradaObrigatoria = $totalCompra > $creditInfo['available_credit'];
        echo "  Entrada obrigatória: " . ($entradaObrigatoria ? 'Sim' : 'Não') . "\n\n";
        
        if ($entradaObrigatoria) {
            echo "PASSO 6: Calculando entrada mínima\n";
            $calculateMinMethod = $reflection->getMethod('calculateMinimumEntry');
            $calculateMinMethod->setAccessible(true);
            $minimumEntry = $calculateMinMethod->invoke($controller, $totalCompra, $creditInfo['available_credit']);
            
            echo "  Entrada mínima necessária: R$ " . number_format($minimumEntry, 2, ',', '.') . "\n";
            echo "  Entrada fornecida: R$ " . number_format($valorEntrada, 2, ',', '.') . "\n";
            echo "  Entrada >= Mínima? " . ($valorEntrada >= $minimumEntry ? 'Sim' : 'Não') . "\n\n";
            
            if ($valorEntrada >= $minimumEntry) {
                echo "PASSO 7: Aumentando limite\n";
                $novoLimite = $cliente->limite + $valorEntrada;
                echo "  Limite atual: R$ " . number_format($cliente->limite, 2, ',', '.') . "\n";
                echo "  Aumento: R$ " . number_format($valorEntrada, 2, ',', '.') . "\n";
                echo "  Novo limite: R$ " . number_format($novoLimite, 2, ',', '.') . "\n";
                
                $updated = DB::table('clientes')
                    ->where('id', $clienteId)
                    ->update(['limite' => $novoLimite]);
                    
                echo "  Registros atualizados: " . $updated . "\n\n";
                
                // Verificar se realmente foi atualizado
                $clienteAtualizado = verificarCliente($clienteId);
                echo "VERIFICAÇÃO FINAL:\n";
                echo "  Limite no banco: R$ " . number_format($clienteAtualizado['limite'], 2, ',', '.') . "\n";
                
                if ($clienteAtualizado['limite'] == $novoLimite) {
                    echo "  ✅ Limite atualizado com sucesso!\n";
                } else {
                    echo "  ❌ Limite NÃO foi atualizado no banco!\n";
                }
            } else {
                echo "  ❌ Entrada insuficiente para aumentar limite\n";
            }
        } else {
            echo "  ❌ Entrada não era obrigatória, limite não será aumentado\n";
        }
    } else {
        echo "  ❌ Não houve entrada, limite não será aumentado\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG CONCLUÍDO ===\n";