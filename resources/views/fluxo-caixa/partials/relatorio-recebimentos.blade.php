<div class="bg-green-50 rounded-lg p-4">
    <h4 class="font-semibold text-green-800 mb-3 flex items-center">
        <i class="fas fa-hand-holding-usd mr-2"></i>Recebimentos de Parcelas
    </h4>

    @if(count($recebimentos) > 0)
        <!-- Resumo dos Recebimentos -->
        <div class="bg-white rounded p-3 mb-4 border border-green-200">
            <div class="grid grid-cols-3 gap-3 text-sm">
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
            </div>
            <div class="border-t border-gray-200 mt-2 pt-2">
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-800">Total:</span>
                    <span class="font-bold text-green-600 text-lg">R$ {{ number_format($resumo['total_geral'], 2, ',', '.') }}</span>
                </div>
                <div class="text-sm text-gray-600 mt-1">
                    <span>Quantidade: {{ $resumo['quantidade_recebimentos'] }}</span>
                </div>
            </div>
        </div>

        <!-- Lista de Recebimentos -->
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @php
                $recebimentosAgrupados = collect($recebimentos)->groupBy('metodo');
            @endphp

            @foreach($recebimentosAgrupados as $metodo => $recebimentosMetodo)
                <div class="mb-4">
                    <h5 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                        @switch($metodo)
                            @case('dinheiro')
                                <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">DINHEIRO</span>
                                @break
                            @case('pix')
                                <i class="fas fa-mobile-alt mr-2 text-blue-600"></i>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">PIX</span>
                                @break
                            @case('cartao')
                                <i class="fas fa-credit-card mr-2 text-purple-600"></i>
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">CARTÃO</span>
                                @break
                            @default
                                <i class="fas fa-question-circle mr-2 text-gray-600"></i>
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">{{ strtoupper($metodo) }}</span>
                        @endswitch
                    </h5>

                    @foreach($recebimentosMetodo as $recebimento)
                        <div class="bg-white rounded p-3 border border-gray-200 ml-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-800">
                                            {{ \Carbon\Carbon::parse($recebimento['data_pagamento'])->format('d/m/Y') }}
                                        </span>
                                        @if(isset($recebimento['hora']))
                                            <span class="text-xs text-gray-500">{{ $recebimento['hora'] }}</span>
                                        @endif
                                    </div>

                                    <!-- Valores por método -->
                                    <div class="grid grid-cols-3 gap-2 mt-2 text-xs">
                                        @if($recebimento['dinheiro'] > 0)
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Dinheiro:</span>
                                                <span class="font-medium">R$ {{ number_format($recebimento['dinheiro'], 2, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        @if($recebimento['pix'] > 0)
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">PIX:</span>
                                                <span class="font-medium">R$ {{ number_format($recebimento['pix'], 2, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        @if($recebimento['cartao'] > 0)
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Cartão:</span>
                                                <span class="font-medium">R$ {{ number_format($recebimento['cartao'], 2, ',', '.') }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if(isset($recebimento['valor_parcela']))
                                        <div class="text-xs text-gray-500 mt-1">
                                            Parcela: R$ {{ number_format($recebimento['valor_parcela'], 2, ',', '.') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="text-right">
                                    @php
                                        $totalRecebimento = $recebimento['dinheiro'] + $recebimento['pix'] + $recebimento['cartao'];
                                    @endphp
                                    <div class="font-bold text-green-600">
                                        R$ {{ number_format($totalRecebimento, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-hand-holding-usd text-2xl mb-2"></i>
            <p>Nenhum recebimento no período</p>
        </div>
    @endif
</div>
