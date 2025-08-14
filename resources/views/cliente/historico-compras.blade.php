@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/purchase-history.css') }}">
@endpush

@section('content')

<div class="content">
    <!-- Header com informações do cliente -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Histórico de Compras</h1>
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
            <a href="{{ route('clientes.historico.compras', $cliente->id) }}" class="btn-green">
                <i class="fas fa-shopping-cart mr-2"></i>Histórico de Compras
            </a>
            <a href="{{ route('clientes.historico.pagamentos', $cliente->id) }}" class="btn-blue">
                <i class="fas fa-receipt mr-2"></i>Histórico de Pagamentos
            </a>
        </div>
    </div>

    <!-- Cards de Compras -->
    <div class="space-y-4 mb-8">
        @forelse ($tickets as $ticket)
            <div class="bg-white rounded-lg shadow-md overflow-hidden purchase-card">
                <!-- Card Header - Sempre visível -->
                <div class="p-4 md:p-6 cursor-pointer hover:bg-gray-50 transition-colors duration-200 card-toggle" 
                     onclick="toggleCard('card-{{ $ticket->id_ticket }}')">
                    <div class="flex items-center justify-between">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 flex-1">
                            <div>
                                <p class="text-sm text-gray-500">Ticket</p>
                                <p class="font-semibold text-gray-800">{{ $ticket->ticket }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Data da Compra</p>
                                <p class="font-semibold text-gray-800">{{ $ticket->data_formatada }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Valor Total</p>
                                <p class="font-semibold text-green-600">{{ $ticket->valor_formatado }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Entrada</p>
                                <p class="font-semibold text-blue-600">{{ $ticket->entrada_formatada }}</p>
                            </div>
                        </div>
                        <div class="ml-4">
                            <i class="fas fa-chevron-down transform transition-transform duration-200" 
                               id="icon-{{ $ticket->id_ticket }}"></i>
                        </div>
                    </div>
                </div>

                <!-- Card Body - Expandível -->
                <div id="card-{{ $ticket->id_ticket }}" class="hidden border-t border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Parcelas</h3>
                        
                        <!-- Lista de Parcelas -->
                        <div class="space-y-3 mb-6 installment-grid">
                            @foreach ($ticket->parcelasRelacao as $parcela)
                                <div class="flex flex-col md:flex-row md:items-center justify-between p-3 rounded-lg {{ $parcela->status_color }}">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4 flex-1">
                                        <div>
                                            <p class="text-sm font-medium">Parcela {{ $parcela->numero }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm">Vencimento: {{ $parcela->vencimento_formatado }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm">Valor: {{ $parcela->valor_formatado }}</p>
                                        </div>
                                        <div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                {{ $parcela->status_texto }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Botão Ver Compra Completa -->
                        <div class="text-center">
                            <a href="{{ route('clientes.compra', [$cliente->id, $ticket->ticket]) }}" 
                               class="btn-green">
                                <i class="fas fa-eye mr-2"></i>Ver Compra Completa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <i class="fas fa-shopping-cart text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Nenhuma compra encontrada</h3>
                <p class="text-gray-500">Este cliente ainda não realizou nenhuma compra.</p>
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    @if ($tickets->hasPages())
        <div class="bg-white rounded-lg shadow-md p-4 mb-8">
            {{ $tickets->links() }}
        </div>
    @endif

    <!-- Perfil de Pagamento -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Perfil de Pagamento</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 payment-profile-grid">
            <!-- Comportamento de Pagamento -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Comportamento de Pagamento</h3>
                <p class="text-sm text-gray-600 mb-2">
                    @php
                        $service = new \App\Services\PaymentProfileService();
                        echo $service->getPaymentBehaviorDescription($paymentProfile['payment_behavior']);
                    @endphp
                </p>
                @if ($paymentProfile['payment_statistics']['total_parcelas_pagas'] > 0)
                    <div class="text-xs text-gray-500">
                        <p>Média de atraso: {{ $paymentProfile['payment_statistics']['media_dias_atraso'] }} dias</p>
                        <p>Parcelas pagas: {{ $paymentProfile['payment_statistics']['total_parcelas_pagas'] }}</p>
                    </div>
                @endif
            </div>

            <!-- Taxa de Devolução -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Taxa de Devolução</h3>
                <p class="text-2xl font-bold {{ $paymentProfile['return_rate'] > 15 ? 'text-red-600' : 'text-green-600' }}">
                    {{ number_format($paymentProfile['return_rate'], 1) }}%
                </p>
                <p class="text-xs text-gray-500">
                    {{ $paymentProfile['returned_purchases'] }} de {{ $paymentProfile['total_purchases'] }} compras
                </p>
                @if ($paymentProfile['return_rate'] > 15)
                    <p class="text-xs text-red-600 mt-1">⚠️ Taxa alta de devolução</p>
                @endif
            </div>

            <!-- Total Comprado -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Total Comprado</h3>
                <p class="text-2xl font-bold text-green-600">
                    R$ {{ number_format($paymentProfile['total_purchased'], 2, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500">
                    Valor médio: R$ {{ number_format($paymentProfile['average_purchase_value'], 2, ',', '.') }}
                </p>
            </div>

            <!-- Primeira Compra -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Primeira Compra</h3>
                <p class="text-lg font-semibold text-gray-800">
                    {{ $paymentProfile['first_purchase'] ?? 'N/A' }}
                </p>
            </div>

            <!-- Frequência de Compras -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Frequência de Compras</h3>
                <p class="text-sm text-gray-600">
                    @php
                        echo $service->getPurchaseFrequencyDescription($paymentProfile['purchase_frequency']);
                    @endphp
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Total de compras: {{ $paymentProfile['total_purchases'] }}
                </p>
            </div>

            <!-- Estatísticas de Pagamento -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Estatísticas</h3>
                <div class="text-xs text-gray-600 space-y-1">
                    <p>Em atraso: {{ $paymentProfile['payment_statistics']['parcelas_em_atraso'] }}</p>
                    <p>No prazo: {{ $paymentProfile['payment_statistics']['parcelas_no_dia'] }}</p>
                    <p>Antecipadas: {{ $paymentProfile['payment_statistics']['parcelas_adiantadas'] }}</p>
                    @if ($paymentProfile['payment_statistics']['maior_atraso'] > 0)
                        <p class="text-red-600">Maior atraso: {{ $paymentProfile['payment_statistics']['maior_atraso'] }} dias</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCard(cardId) {
    const card = document.getElementById(cardId);
    const icon = document.getElementById('icon-' + cardId.split('-')[1]);
    
    if (card.classList.contains('hidden')) {
        card.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        card.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}
</script>

@endsection