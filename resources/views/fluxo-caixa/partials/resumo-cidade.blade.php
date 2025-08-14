<div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
    <!-- Header da Cidade -->
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800 capitalize">
                <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                {{ $cidadeData['cidade']['nome'] }}
            </h2>
            <button type="button" 
                    class="toggle-vendedor text-gray-500 hover:text-gray-700 transition-colors"
                    data-target="cidade-{{ $loop->index }}">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        
        <!-- Resumo da Cidade -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
            <div class="text-center">
                <div class="text-sm text-gray-600">Vendas Total</div>
                <div class="text-lg font-bold text-blue-600">
                    R$ {{ number_format($cidadeData['resumo_cidade']['vendas']['total_geral'], 2, ',', '.') }}
                </div>
            </div>
            <div class="text-center">
                <div class="text-sm text-gray-600">Recebimentos</div>
                <div class="text-lg font-bold text-green-600">
                    R$ {{ number_format($cidadeData['resumo_cidade']['recebimentos']['total_geral'], 2, ',', '.') }}
                </div>
            </div>
            <div class="text-center">
                <div class="text-sm text-gray-600">Despesas</div>
                <div class="text-lg font-bold text-red-600">
                    R$ {{ number_format($cidadeData['resumo_cidade']['despesas']['total'], 2, ',', '.') }}
                </div>
            </div>
            <div class="text-center">
                <div class="text-sm text-gray-600">Dinheiro Líquido</div>
                <div class="text-lg font-bold {{ $cidadeData['resumo_cidade']['recebimentos']['total_dinheiro_liquido'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    R$ {{ number_format($cidadeData['resumo_cidade']['recebimentos']['total_dinheiro_liquido'], 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Detalhes dos Vendedores -->
    <div id="cidade-{{ $loop->index }}" class="hidden">
        @if(count($cidadeData['vendedores']) > 0)
            @foreach($cidadeData['vendedores'] as $vendedorData)
                <div class="border-b border-gray-100 last:border-b-0">
                    <!-- Header do Vendedor -->
                    <div class="px-6 py-4 bg-gray-25">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">
                                <i class="fas fa-user mr-2 text-green-600"></i>
                                {{ $vendedorData['vendedor']->name }}
                            </h3>
                            <button type="button" 
                                    class="toggle-vendedor text-gray-400 hover:text-gray-600 transition-colors"
                                    data-target="vendedor-{{ $loop->parent->index }}-{{ $loop->index }}">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        
                        <!-- Resumo do Vendedor -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3 text-sm">
                            <div>
                                <span class="text-gray-600">Vendas:</span>
                                <span class="font-semibold ml-2">R$ {{ number_format($vendedorData['resumo_vendas']['total_geral'], 2, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Recebimentos:</span>
                                <span class="font-semibold ml-2">R$ {{ number_format($vendedorData['resumo_recebimentos']['total_geral'], 2, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Qtd Vendas:</span>
                                <span class="font-semibold ml-2">{{ $vendedorData['resumo_vendas']['quantidade_vendas'] }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Qtd Recebimentos:</span>
                                <span class="font-semibold ml-2">{{ $vendedorData['resumo_recebimentos']['quantidade_recebimentos'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes do Vendedor -->
                    <div id="vendedor-{{ $loop->parent->index }}-{{ $loop->index }}" class="hidden px-6 py-4">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Relatório de Vendas -->
                            @include('fluxo-caixa.partials.relatorio-vendas', [
                                'vendas' => $vendedorData['vendas'],
                                'resumo' => $vendedorData['resumo_vendas']
                            ])

                            <!-- Relatório de Recebimentos -->
                            @include('fluxo-caixa.partials.relatorio-recebimentos', [
                                'recebimentos' => $vendedorData['recebimentos'],
                                'resumo' => $vendedorData['resumo_recebimentos']
                            ])
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="px-6 py-8 text-center text-gray-500">
                <i class="fas fa-info-circle text-2xl mb-2"></i>
                <p>Nenhum vendedor com atividade no período selecionado.</p>
            </div>
        @endif

        <!-- Despesas da Cidade -->
        @if(count($cidadeData['despesas']) > 0)
            <div class="px-6 py-4 bg-red-50 border-t border-red-100">
                <h4 class="font-semibold text-red-800 mb-3">
                    <i class="fas fa-minus-circle mr-2"></i>Despesas do Período
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($cidadeData['despesas'] as $despesa)
                        <div class="bg-white rounded p-3 border border-red-200">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-800">{{ $despesa->empresa ?? 'Despesa' }}</div>
                                    <div class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($despesa->data)->format('d/m/Y') }}</div>
                                    @if($despesa->numero)
                                        <div class="text-xs text-gray-500">Nº {{ $despesa->numero }}</div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-red-600">R$ {{ number_format($despesa->valor, 2, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 pt-3 border-t border-red-200">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-red-800">Total de Despesas:</span>
                        <span class="font-bold text-red-600 text-lg">
                            R$ {{ number_format($cidadeData['resumo_cidade']['despesas']['total'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>