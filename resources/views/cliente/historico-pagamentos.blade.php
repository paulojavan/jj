@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/purchase-history.css') }}">
<style>
.payment-card {
    transition: all 0.3s ease;
}

.payment-card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.payment-card .card-toggle {
    cursor: pointer;
}

.payment-card .card-toggle:hover {
    background-color: #f9fafb;
}

.payment-card .rotate-180 {
    transform: rotate(180deg);
}

.payment-card .hidden {
    display: none;
}

.payment-card .installment-grid {
    display: grid;
    gap: 0.75rem;
}

.payment-card .installment-item {
    display: flex;
    flex-direction: column;
}

@media (min-width: 768px) {
    .payment-card .installment-item {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}
</style>
@endpush

@section('content')

<div class="content">
    <!-- Header com informações do cliente -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Histórico de Pagamentos</h1>
                <p class="text-gray-600">Cliente: <span class="font-semibold">{{ $cliente->nome }}</span></p>
                <p class="text-gray-600">CPF: <span class="font-semibold">{{ $cliente->cpf }}</span></p>
            </div>
            <div class="text-right">
                <a href="{{ route('clientes.index') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <x-alert />

    <!-- Navegação entre históricos -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('clientes.historico.compras', $cliente->id) }}" class="btn-blue">
                <i class="fas fa-shopping-cart mr-2"></i>Histórico de Compras
            </a>
            <a href="{{ route('clientes.historico.pagamentos', $cliente->id) }}" class="btn-green">
                <i class="fas fa-receipt mr-2"></i>Histórico de Pagamentos
            </a>
        </div>
    </div>

    <!-- Histórico de Pagamentos -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Histórico de Pagamentos</h2>
        
        @if($pagamentos->isNotEmpty())
            <div class="space-y-4">
                @foreach ($pagamentos as $pagamento)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden payment-card">
                        <!-- Card Header - Sempre visível -->
                        <div class="p-4 md:p-6 cursor-pointer hover:bg-gray-50 transition-colors duration-200 card-toggle" 
                             onclick="toggleCard('payment-{{ $pagamento->id_pagamento }}')">
                            <div class="flex items-center justify-between">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1">
                                    <div>
                                        <p class="text-sm text-gray-500">Ticket</p>
                                        <p class="font-semibold text-gray-800">{{ $pagamento->ticket }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Data do Pagamento</p>
                                        <p class="font-semibold text-gray-800">{{ $pagamento->data->format('d/m/Y H:i:s') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Valor Total Pago</p>
                                        <p class="font-semibold text-green-600">
                                            R$ {{ number_format($pagamento->parcelas->sum('valor_pago'), 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <i class="fas fa-chevron-down transform transition-transform duration-200" 
                                       id="payment-icon-{{ $pagamento->id_pagamento }}"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body - Expandível -->
                        <div id="payment-{{ $pagamento->id_pagamento }}" class="hidden border-t border-gray-200">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Parcelas Pagas</h3>
                                
                                <!-- Lista de Parcelas Pagas -->
                                <div class="space-y-3 mb-6 installment-grid">
                                    @foreach ($pagamento->parcelas as $parcela)
                                        <div class="flex flex-col md:flex-row md:items-center justify-between p-3 rounded-lg bg-green-50">
                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 md:gap-4 flex-1">
                                                <div>
                                                    <p class="text-sm font-medium">Parcela {{ $parcela->numero }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm">Vencimento: {{ \Carbon\Carbon::parse($parcela->data_vencimento)->format('d/m/Y') }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm">Valor Pago: R$ {{ number_format($parcela->valor_pago, 2, ',', '.') }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm">Método: {{ ucfirst($parcela->metodo) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Botões de Ação -->
                                <div class="flex flex-col sm:flex-row justify-center gap-4 mt-6">
                                    <button class="btn-green" onclick="imprimirComprovante({{ $cliente->id }}, {{ $pagamento->id_pagamento }})">
                                        <i class="fas fa-print mr-2"></i>Imprimir Comprovante
                                    </button>
                                    <button class="btn-blue" onclick="enviarWhatsapp({{ $cliente->id }}, {{ $pagamento->id_pagamento }})">
                                        <i class="fab fa-whatsapp mr-2"></i>Enviar WhatsApp
                                    </button>
                                    <button class="btn-red" onclick="cancelarPagamento({{ $cliente->id }}, {{ $pagamento->id_pagamento }})">
                                        <i class="fas fa-times mr-2"></i>Cancelar Pagamento
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-receipt text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Nenhum pagamento encontrado</h3>
                <p class="text-gray-500">Este cliente ainda não realizou nenhum pagamento.</p>
            </div>
        @endif
    </div>
</div>

<script>
function toggleCard(cardId) {
    const card = document.getElementById(cardId);
    const cardIdParts = cardId.split('-');
    const baseId = cardIdParts[0];
    let iconId;
    
    if (baseId === 'card') {
        iconId = 'icon-' + cardIdParts[1];
    } else if (baseId === 'payment') {
        iconId = 'payment-icon-' + cardIdParts[1];
    }
    
    const icon = document.getElementById(iconId);
    
    if (card.classList.contains('hidden')) {
        card.classList.remove('hidden');
        if (icon) {
            icon.classList.add('rotate-180');
        }
    } else {
        card.classList.add('hidden');
        if (icon) {
            icon.classList.remove('rotate-180');
        }
    }
}

// Função para enviar WhatsApp
function enviarWhatsapp(clienteId, pagamentoId) {
    // Desabilitar botão temporariamente
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando link...';
    
    fetch(`/clientes/${clienteId}/pagamentos/${pagamentoId}/whatsapp`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Abrir WhatsApp em nova aba
            window.open(data.whatsapp_url, '_blank');
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'Link do WhatsApp gerado com sucesso!',
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: data.message || 'Erro ao gerar link do WhatsApp. Verifique o número do cliente.'
            });
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao abrir WhatsApp. Verifique o número do cliente.'
        });
    })
    .finally(() => {
        // Reabilitar botão
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Função para imprimir comprovante
function imprimirComprovante(clienteId, pagamentoId) {
    try {
        // Abrir página de impressão em nova janela
        const printUrl = `/clientes/${clienteId}/pagamentos/${pagamentoId}/comprovante`;
        window.open(printUrl, '_blank', 'width=800,height=600');
        
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: 'Comprovante aberto para impressão!',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao gerar comprovante para impressão.'
        });
    }
}

// Função para cancelar pagamento
function cancelarPagamento(clienteId, pagamentoId) {
    Swal.fire({
        title: 'Confirmar Cancelamento',
        text: 'Tem certeza que deseja cancelar este pagamento? Esta ação irá deletar o pagamento e resetar as parcelas para "aguardando pagamento".',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Sim, cancelar!',
        cancelButtonText: 'Não, manter'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Cancelando...',
                text: 'Processando cancelamento do pagamento',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`/clientes/${clienteId}/pagamentos/${pagamentoId}/cancelar`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelado!',
                        text: data.message || 'Pagamento cancelado com sucesso!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Recarregar a página para atualizar a lista
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.message || 'Erro ao cancelar pagamento.'
                    });
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao cancelar pagamento. Tente novamente.'
                });
            });
        }
    });
}
</script>

@endsection
