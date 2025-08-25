@extends('layouts.base')

@section('title', 'Detalhes do Cliente - Negativação')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalhes para Negativação</h1>
            <p class="text-gray-600 mt-1">{{ $cliente->nome }} - CPF: {{ $cliente->cpf }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('negativacao.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Cliente Info Card -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informações do Cliente</h3>
        </div>
        <div class="p-6">
            <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                    @if($cliente->foto)
                        <img class="h-20 w-20 rounded-full object-cover" 
                             src="{{ asset('storage/fotos_clientes/' . $cliente->foto) }}" 
                             alt="{{ $cliente->nome }}">
                    @else
                        <div class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-user text-gray-600 text-2xl"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <h4 class="text-xl font-bold text-gray-900">{{ $cliente->nome }}</h4>
                    <p class="text-gray-600">CPF: {{ $cliente->cpf }}</p>
                    @if($cliente->telefone)
                        <p class="text-gray-600">Telefone: {{ $cliente->telefone }}</p>
                    @endif
                </div>
                <div class="flex space-x-3">
                    @if($cliente->telefone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $cliente->telefone) }}" 
                           target="_blank"
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fab fa-whatsapp mr-2"></i>
                            WhatsApp
                        </a>
                    @endif
                    <button onclick="confirmarNegativacao()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Negativar Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-user text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Parcelas do Titular</p>
                    <p class="text-2xl font-bold text-gray-900">
                        R$ {{ number_format($valorTotalTitular, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Parcelas dos Autorizados</p>
                    <p class="text-2xl font-bold text-gray-900">
                        R$ {{ number_format($valorTotalAutorizados, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-calculator text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Geral</p>
                    <p class="text-2xl font-bold text-gray-900">
                        R$ {{ number_format($valorTotalGeral, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Parcelas do Titular -->
    @if($parcelasTitular->count() > 0)
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-user mr-2"></i>
                    Parcelas do Titular ({{ $parcelasTitular->count() }})
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ticket
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Parcela
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Vencimento
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Valor Original
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Juros/Multa
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total a Pagar
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dias de Atraso
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($parcelasTitular as $parcela)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $parcela->ticket }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $parcela->numero }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $parcela->data_vencimento->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    R$ {{ number_format($parcela->valor_calculado->valor_original, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    R$ {{ number_format($parcela->valor_calculado->valor_juros + $parcela->valor_calculado->valor_multa, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    R$ {{ number_format($parcela->valor_calculado->valor_total, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $parcela->valor_calculado->dias_atraso }} dias
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Parcelas dos Autorizados -->
    @if($parcelasAutorizados->count() > 0)
        @foreach($parcelasAutorizados as $autorizadoId => $parcelas)
            @php $autorizado = $parcelas->first()->autorizado; @endphp
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-user-friends mr-2"></i>
                        Parcelas de {{ $autorizado->nome }} ({{ $parcelas->count() }})
                    </h3>
                    <p class="text-sm text-gray-600">CPF: {{ $autorizado->cpf }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ticket
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Parcela
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Vencimento
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Valor Original
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Juros/Multa
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total a Pagar
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dias de Atraso
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($parcelas as $parcela)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $parcela->ticket }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $parcela->numero }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $parcela->data_vencimento->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        R$ {{ number_format($parcela->valor_calculado->valor_original, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        R$ {{ number_format($parcela->valor_calculado->valor_juros + $parcela->valor_calculado->valor_multa, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        R$ {{ number_format($parcela->valor_calculado->valor_total, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $parcela->valor_calculado->dias_atraso }} dias
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- Modal de Confirmação -->
<div id="modalNegativacao" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Confirmar Negativação</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Tem certeza que deseja negativar o cliente <strong>{{ $cliente->nome }}</strong>?
                    Esta ação irá marcar todas as compras pendentes no SPC e inativar o cliente.
                </p>
                <p class="text-sm text-red-600 mt-2 font-medium">
                    Valor total: R$ {{ number_format($valorTotalGeral, 2, ',', '.') }}
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form action="{{ route('negativacao.negativar', $cliente) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Sim
                    </button>
                </form>
                <button onclick="fecharModal()" 
                        class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarNegativacao() {
    document.getElementById('modalNegativacao').classList.remove('hidden');
}

function fecharModal() {
    document.getElementById('modalNegativacao').classList.add('hidden');
}

// Fechar modal ao clicar fora
document.getElementById('modalNegativacao').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModal();
    }
});
</script>
@endsection