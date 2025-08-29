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

        <!-- Lista de Recebimentos Agrupados por Data e Cliente -->
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @php
                $recebimentosAgrupados = collect($recebimentos)->groupBy('data_pagamento');
            @endphp

            @foreach($recebimentosAgrupados as $data => $recebimentosData)
                <div class="mb-4">
                    <h5 class="text-sm font-semibold text-gray-700 mb-3 flex items-center bg-gray-100 p-2 rounded">
                        <i class="fas fa-calendar-day mr-2 text-blue-600"></i>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                            {{ \Carbon\Carbon::parse($data)->format('d/m/Y') }}
                        </span>
                    </h5>

                    @foreach($recebimentosData as $recebimento)
                        <div class="bg-white rounded p-3 border border-gray-200 ml-4 cursor-pointer hover:bg-gray-50 transition-colors duration-200 shadow-sm" 
                             onclick="mostrarDetalhesRecebimentoCliente({{ json_encode($recebimento) }})">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <i class="fas fa-user text-gray-600"></i>
                                        <span class="text-sm font-medium text-gray-800">
                                            {{ $recebimento['cliente_nome'] }}
                                        </span>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            {{ count($recebimento['parcelas']) }} parcela(s)
                                        </span>
                                    </div>

                                    <!-- Valores totais por método -->
                                    <div class="grid grid-cols-3 gap-2 mt-2 text-xs">
                                        @if($recebimento['total_dinheiro'] > 0)
                                            <div class="flex justify-between">
                                                <span class="text-gray-600 flex items-center">
                                                    <i class="fas fa-money-bill-wave mr-1 text-green-600"></i>Dinheiro:
                                                </span>
                                                <span class="font-medium text-green-600">R$ {{ number_format($recebimento['total_dinheiro'], 2, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        @if($recebimento['total_pix'] > 0)
                                            <div class="flex justify-between">
                                                <span class="text-gray-600 flex items-center">
                                                    <i class="fas fa-mobile-alt mr-1 text-blue-600"></i>PIX:
                                                </span>
                                                <span class="font-medium text-blue-600">R$ {{ number_format($recebimento['total_pix'], 2, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        @if($recebimento['total_cartao'] > 0)
                                            <div class="flex justify-between">
                                                <span class="text-gray-600 flex items-center">
                                                    <i class="fas fa-credit-card mr-1 text-purple-600"></i>Cartão:
                                                </span>
                                                <span class="font-medium text-purple-600">R$ {{ number_format($recebimento['total_cartao'], 2, ',', '.') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right">
                                    <div class="font-bold text-green-600 text-lg">
                                        R$ {{ number_format($recebimento['total_geral'], 2, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-eye"></i> Clique para detalhes
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

<script>
function mostrarDetalhesRecebimentoCliente(recebimento) {
    // Preparar dados do cliente
    const nomeCliente = recebimento.cliente_nome || 'Cliente não identificado';
    const dataFormatada = new Date(recebimento.data_pagamento).toLocaleDateString('pt-BR');
    
    // Preparar lista de parcelas individuais
    let parcelasHtml = '';
    if (recebimento.parcelas && recebimento.parcelas.length > 0) {
        recebimento.parcelas.forEach((parcela, index) => {
            let metodosHtml = [];
            
            if (parcela.dinheiro && parcela.dinheiro > 0) {
                metodosHtml.push(`<span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800">
                    <i class="fas fa-money-bill-wave mr-1"></i>Dinheiro: R$ ${parseFloat(parcela.dinheiro).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </span>`);
            }
            if (parcela.pix && parcela.pix > 0) {
                metodosHtml.push(`<span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                    <i class="fas fa-mobile-alt mr-1"></i>PIX: R$ ${parseFloat(parcela.pix).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </span>`);
            }
            if (parcela.cartao && parcela.cartao > 0) {
                metodosHtml.push(`<span class="inline-flex items-center px-2 py-1 rounded text-xs bg-purple-100 text-purple-800">
                    <i class="fas fa-credit-card mr-1"></i>Cartão: R$ ${parseFloat(parcela.cartao).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </span>`);
            }
            
            parcelasHtml += `
                <div class="bg-gray-50 rounded-lg p-3 mb-3 border border-gray-200">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center">
                            <span class="font-semibold text-gray-700">Parcela ${index + 1}</span>
                            <span class="ml-2 text-xs text-gray-500">${parcela.hora || 'Hora não informada'}</span>
                        </div>
                        <div class="font-bold text-green-600">
                            R$ ${parseFloat(parcela.valor_parcela).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        ${metodosHtml.join('')}
                    </div>
                </div>
            `;
        });
    }
    
    // Preparar resumo dos totais
    let resumoTotais = [];
    if (recebimento.total_dinheiro > 0) {
        resumoTotais.push(`<div class="flex justify-between items-center mb-2">
            <span class="flex items-center"><i class="fas fa-money-bill-wave mr-2 text-green-600"></i>Total Dinheiro:</span>
            <span class="font-bold text-green-600">R$ ${parseFloat(recebimento.total_dinheiro).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
        </div>`);
    }
    if (recebimento.total_pix > 0) {
        resumoTotais.push(`<div class="flex justify-between items-center mb-2">
            <span class="flex items-center"><i class="fas fa-mobile-alt mr-2 text-blue-600"></i>Total PIX:</span>
            <span class="font-bold text-blue-600">R$ ${parseFloat(recebimento.total_pix).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
        </div>`);
    }
    if (recebimento.total_cartao > 0) {
        resumoTotais.push(`<div class="flex justify-between items-center mb-2">
            <span class="flex items-center"><i class="fas fa-credit-card mr-2 text-purple-600"></i>Total Cartão:</span>
            <span class="font-bold text-purple-600">R$ ${parseFloat(recebimento.total_cartao).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
        </div>`);
    }
    
    // Exibir SweetAlert
    Swal.fire({
        title: `<strong><i class="fas fa-user-check text-green-600"></i> Recebimentos de ${nomeCliente}</strong>`,
        html: `
            <div class="text-left">
                <div class="bg-blue-50 rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-semibold text-gray-700">Data:</span>
                            <div class="text-gray-900 font-medium">${dataFormatada}</div>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold text-gray-700">Total de Parcelas:</span>
                            <div class="text-gray-900 font-medium">${recebimento.parcelas.length}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-list mr-2"></i>Parcelas Detalhadas
                    </h4>
                    <div class="max-h-64 overflow-y-auto">
                        ${parcelasHtml}
                    </div>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-calculator mr-2"></i>Resumo Total
                    </h4>
                    ${resumoTotais.join('')}
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-800 text-lg">Total Geral:</span>
                            <span class="font-bold text-green-600 text-xl">R$ ${parseFloat(recebimento.total_geral).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                        </div>
                    </div>
                </div>
            </div>
        `,
        icon: 'info',
        confirmButtonText: '<i class="fas fa-check mr-2"></i>Fechar',
        confirmButtonColor: '#16a34a',
        customClass: {
            popup: 'swal2-popup-custom',
            title: 'swal2-title-custom',
            confirmButton: 'swal2-confirm-custom'
        },
        buttonsStyling: false,
        width: '700px'
    });
}
</script>
