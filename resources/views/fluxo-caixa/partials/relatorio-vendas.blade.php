<div class="bg-blue-50 rounded-lg p-4">
    <h4 class="font-semibold text-blue-800 mb-3 flex items-center">
        <i class="fas fa-shopping-cart mr-2"></i>Vendas
    </h4>
    
    @if(count($vendas) > 0)
        <!-- Resumo das Vendas -->
        <div class="bg-white rounded p-3 mb-4 border border-blue-200">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Dinheiro:</span>
                    <span class="font-semibold">R$ {{ number_format($resumo['total_dinheiro'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">PIX:</span>
                    <span class="font-semibold">R$ {{ number_format($resumo['total_pix'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Cartão:</span>
                    <span class="font-semibold">R$ {{ number_format($resumo['total_cartao'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Crediário:</span>
                    <span class="font-semibold">R$ {{ number_format($resumo['total_crediario'], 2, ',', '.') }}</span>
                </div>
            </div>
            <div class="border-t border-gray-200 mt-2 pt-2">
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-800">Total:</span>
                    <span class="font-bold text-blue-600 text-lg">R$ {{ number_format($resumo['total_geral'], 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600 mt-1">
                    <span>Quantidade: {{ $resumo['quantidade_vendas'] }}</span>
                    @if($resumo['vendas_estornadas'] > 0)
                        <span class="text-red-600">Estornadas: {{ $resumo['vendas_estornadas'] }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Lista de Vendas -->
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($vendas as $venda)
                <div class="bg-white rounded p-3 border {{ isset($venda->data_estorno) && $venda->data_estorno ? 'border-red-300 bg-red-50' : 'border-gray-200' }}">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-800">
                                    {{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}
                                </span>
                                @if(isset($venda->hora))
                                    <span class="text-xs text-gray-500">{{ $venda->hora }}</span>
                                @endif
                                @if(isset($venda->data_estorno) && $venda->data_estorno)
                                    <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">ESTORNADA</span>
                                @endif
                            </div>
                            
                            <!-- Valores por método -->
                            <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                                @if($venda->valor_dinheiro > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Dinheiro:</span>
                                        <span class="font-medium">R$ {{ number_format($venda->valor_dinheiro, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($venda->valor_pix > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">PIX:</span>
                                        <span class="font-medium">R$ {{ number_format($venda->valor_pix, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($venda->valor_cartao > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Cartão:</span>
                                        <span class="font-medium">R$ {{ number_format($venda->valor_cartao, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($venda->valor_crediario > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Crediário:</span>
                                        <span class="font-medium">R$ {{ number_format($venda->valor_crediario, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="text-right">
                            @php
                                $total = $venda->valor_dinheiro + $venda->valor_pix + $venda->valor_cartao + $venda->valor_crediario;
                                $multiplicador = (isset($venda->data_estorno) && $venda->data_estorno) ? -1 : 1;
                                $totalFinal = $total * $multiplicador;
                            @endphp
                            <div class="font-bold {{ $totalFinal >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                R$ {{ number_format(abs($totalFinal), 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-shopping-cart text-2xl mb-2"></i>
            <p>Nenhuma venda no período</p>
        </div>
    @endif
</div>