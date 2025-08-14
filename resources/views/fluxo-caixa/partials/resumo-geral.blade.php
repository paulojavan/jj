<div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">
        <i class="fas fa-chart-line mr-2"></i>Resumo Geral - Todas as Cidades
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Vendas -->
        <div class="bg-cyan-500 rounded-lg p-4">
            <h3 class="font-semibold mb-3 flex items-center text-white">
                <i class="fas fa-shopping-cart mr-2"></i>Vendas
            </h3>
            <div class="space-y-1 text-sm text-blue-100">
                <div class="flex justify-between">
                    <span>Dinheiro:</span>
                    <span class="font-medium text-white">R$ {{ number_format($resumo['vendas']['total_dinheiro'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>PIX:</span>
                    <span class="font-medium text-white">R$ {{ number_format($resumo['vendas']['total_pix'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Cartão:</span>
                    <span class="font-medium text-white">R$ {{ number_format($resumo['vendas']['total_cartao'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Crediário:</span>
                    <span class="font-medium text-white">R$ {{ number_format($resumo['vendas']['total_crediario'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-white border-opacity-30 pt-1 mt-2">
                    <span class="font-semibold text-white">Total:</span>
                    <span class="font-bold text-yellow-200">R$ {{ number_format($resumo['vendas']['total_geral'], 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Recebimentos -->
        <div class="bg-cyan-500 rounded-lg p-4">
            <h3 class="font-semibold mb-3 flex items-center text-white">
                <i class="fas fa-hand-holding-usd mr-2"></i>Recebimentos
            </h3>
            <div class="space-y-1 text-sm text-blue-100">
                <div class="flex justify-between">
                    <span>Dinheiro:</span>
                    <span class="font-medium text-white">R$ {{ number_format($resumo['recebimentos']['total_dinheiro'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>PIX:</span>
                    <span class="font-medium text-white">R$ {{ number_format($resumo['recebimentos']['total_pix'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Cartão:</span>
                    <span class="font-medium text-white">R$ {{ number_format($resumo['recebimentos']['total_cartao'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-white border-opacity-30 pt-1 mt-2">
                    <span class="font-semibold text-white">Total:</span>
                    <span class="font-bold text-green-200">R$ {{ number_format($resumo['recebimentos']['total_geral'], 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Despesas -->
        <div class="bg-cyan-500 rounded-lg p-4">
            <h3 class="font-semibold mb-3 flex items-center text-white">
                <i class="fas fa-minus-circle mr-2"></i>Despesas
            </h3>
            <div class="space-y-1 text-sm text-blue-100">
                <div class="flex justify-between">
                    <span>Total Despesas:</span>
                    <span class="font-medium text-red-200">R$ {{ number_format($resumo['despesas']['total'], 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Saldo Líquido -->
        <div class="bg-cyan-500 rounded-lg p-4">
            <h3 class="font-semibold mb-3 flex items-center text-white">
                <i class="fas fa-balance-scale mr-2"></i>Saldo Líquido
            </h3>
            <div class="space-y-1 text-sm text-blue-100">
                <div class="flex justify-between">
                    <span>Dinheiro Líquido:</span>
                    <span class="font-bold {{ $resumo['recebimentos']['total_dinheiro_liquido'] >= 0 ? 'text-green-200' : 'text-red-200' }}">
                        R$ {{ number_format($resumo['recebimentos']['total_dinheiro_liquido'], 2, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Total Recebido:</span>
                    <span class="font-medium text-white">R$ {{ number_format($resumo['recebimentos']['total_geral'], 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>