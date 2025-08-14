<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTE DE ATUALIZAÇÃO DE ESTOQUE ===\n\n";

// Função para verificar estoque
function verificarEstoque($produtoId, $numeracao = null) {
    $estoqueGeral = DB::table('produtos')->where('id', $produtoId)->value('quantidade');
    
    $result = [
        'geral' => $estoqueGeral,
        'tipo_geral' => gettype($estoqueGeral)
    ];
    
    if ($numeracao) {
        $estoqueNumeracao = DB::table('estoque_tabira')
            ->where('id_produto', $produtoId)
            ->where('numero', $numeracao)
            ->value('quantidade');
        
        $result['numeracao'] = $estoqueNumeracao;
        $result['tipo_numeracao'] = gettype($estoqueNumeracao);
    }
    
    return $result;
}

// Verificar estoque antes
echo "1. Verificando estoque inicial...\n";
$estoqueAntes = verificarEstoque(1, 40);
echo "   Produto 1 - Estoque geral: {$estoqueAntes['geral']} (tipo: {$estoqueAntes['tipo_geral']})\n";
echo "   Produto 1 - Numeração 40: {$estoqueAntes['numeracao']} (tipo: {$estoqueAntes['tipo_numeracao']})\n\n";

// Testar atualização de estoque usando a mesma lógica do controller
echo "2. Testando atualização de estoque...\n";

try {
    // Simular a função updateProductStock
    $productId = 1;
    $quantitySold = 1;
    
    echo "   Atualizando estoque do produto ID {$productId}, quantidade vendida: {$quantitySold}\n";
    
    // Primeiro, verifica o estoque atual
    $produto = DB::table('produtos')->where('id', $productId)->first();
    
    if (!$produto) {
        throw new Exception("Produto ID {$productId} não encontrado.");
    }
    
    // Converte quantidade para inteiro (pode estar como string)
    $estoqueAtual = (int) $produto->quantidade;
    $quantidadeVendida = (int) $quantitySold;
    
    echo "   Estoque atual: {$estoqueAtual}, Quantidade a vender: {$quantidadeVendida}\n";
    
    if ($estoqueAtual < $quantidadeVendida) {
        throw new Exception("Estoque insuficiente para produto ID {$productId}. Disponível: {$estoqueAtual}, Solicitado: {$quantidadeVendida}");
    }
    
    // Atualiza a quantidade na tabela produtos
    $novoEstoque = $estoqueAtual - $quantidadeVendida;
    $updated = DB::table('produtos')
        ->where('id', $productId)
        ->update(['quantidade' => (string) $novoEstoque]); // Mantém como string se necessário
        
    if (!$updated) {
        throw new Exception("Falha ao atualizar estoque do produto ID {$productId}.");
    }
    
    echo "   ✅ Estoque geral atualizado: {$estoqueAtual} -> {$novoEstoque}\n";
    
    // Agora testar estoque da numeração
    $numeracao = 40;
    $nomeTabela = 'estoque_tabira';
    
    echo "   Atualizando estoque da numeração {$numeracao}...\n";
    
    // Primeiro, verifica o estoque atual da numeração
    $estoqueItem = DB::table($nomeTabela)
        ->where('id_produto', $productId)
        ->where('numero', $numeracao)
        ->first();
        
    if (!$estoqueItem) {
        throw new Exception("Estoque não encontrado para produto ID {$productId}, numeração {$numeracao}.");
    }
    
    $estoqueAtualNum = (int) $estoqueItem->quantidade;
    
    echo "   Estoque atual numeração {$numeracao}: {$estoqueAtualNum}\n";
    
    if ($estoqueAtualNum < $quantidadeVendida) {
        throw new Exception("Estoque insuficiente para numeração {$numeracao}. Disponível: {$estoqueAtualNum}, Solicitado: {$quantidadeVendida}");
    }
    
    // Atualiza a quantidade na tabela de estoque específica da cidade
    $novoEstoqueNum = $estoqueAtualNum - $quantidadeVendida;
    $updatedNum = DB::table($nomeTabela)
        ->where('id_produto', $productId)
        ->where('numero', $numeracao)
        ->update(['quantidade' => $novoEstoqueNum]);
        
    if (!$updatedNum) {
        throw new Exception("Falha ao atualizar estoque da numeração {$numeracao}.");
    }
    
    echo "   ✅ Estoque numeração atualizado: {$estoqueAtualNum} -> {$novoEstoqueNum}\n\n";
    
    // Verificar estoque depois
    echo "3. Verificando estoque após atualização...\n";
    $estoqueDepois = verificarEstoque(1, 40);
    echo "   Produto 1 - Estoque geral: {$estoqueDepois['geral']} (tipo: {$estoqueDepois['tipo_geral']})\n";
    echo "   Produto 1 - Numeração 40: {$estoqueDepois['numeracao']} (tipo: {$estoqueDepois['tipo_numeracao']})\n\n";
    
    echo "✅ TESTE DE ATUALIZAÇÃO DE ESTOQUE CONCLUÍDO COM SUCESSO!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    
    // Restaurar estoque para próximos testes
    DB::table('produtos')->where('id', 1)->update(['quantidade' => '10']);
    DB::table('estoque_tabira')->where('id_produto', 1)->where('numero', 40)->update(['quantidade' => 5]);
    echo "   Estoque restaurado para próximos testes.\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";