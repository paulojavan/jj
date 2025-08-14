@extends('layouts.base')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-user-chart mr-2"></i>Fluxo de Caixa Individual
            </h1>
            <a href="{{ route('fluxo-caixa.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Voltar ao Geral
            </a>
        </div>

        <!-- Formulário de Filtros -->
        <form method="POST" action="{{ route('fluxo-caixa.relatorio-individualizado') }}" class="bg-gray-50 rounded-lg p-4 mb-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- Data Inicial -->
                <div>
                    <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Data Inicial
                    </label>
                    <input type="date" 
                           id="data_inicio" 
                           name="data_inicio" 
                           value="{{ $dataInicio ?? now()->format('Y-m-d') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <!-- Data Final -->
                <div>
                    <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Data Final
                    </label>
                    <input type="date" 
                           id="data_fim" 
                           name="data_fim" 
                           value="{{ $dataFim ?? now()->format('Y-m-d') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <!-- Vendedor -->
                <div>
                    <label for="vendedor_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i>Vendedor
                    </label>
                    <select id="vendedor_id" 
                            name="vendedor_id" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecione um vendedor</option>
                        @if(isset($vendedoresDisponiveis))
                            @foreach($vendedoresDisponiveis as $vendedor)
                                <option value="{{ $vendedor->id }}" 
                                        {{ (isset($vendedorId) && $vendedorId == $vendedor->id) ? 'selected' : '' }}>
                                    {{ $vendedor->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <!-- Botão Filtrar -->
                <div>
                    <button type="submit" 
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
                        <i class="fas fa-search mr-2"></i>Gerar Relatório
                    </button>
                </div>
            </div>
            
            <!-- Botões de período rápido -->
            <div class="flex flex-wrap gap-2 mt-4">
                <button type="button" onclick="setPeriodoIndividual('hoje')" 
                        class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm transition-colors">
                    Hoje
                </button>
                <button type="button" onclick="setPeriodoIndividual('ontem')" 
                        class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm transition-colors">
                    Ontem
                </button>
                <button type="button" onclick="setPeriodoIndividual('semana')" 
                        class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm transition-colors">
                    Esta Semana
                </button>
                <button type="button" onclick="setPeriodoIndividual('mes')" 
                        class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm transition-colors">
                    Este Mês
                </button>
            </div>
        </form>

        <!-- Resultados -->
        @if(isset($dados))
            <div class="mt-8">
                <!-- Header do Vendedor -->
                <div class="bg-gradient-to-r from-green-600 to-green-800 text-white rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold mb-2">
                                <i class="fas fa-user mr-2"></i>{{ $dados['vendedor']->name }}
                            </h2>
                            <p class="text-green-100">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $dados['cidade'] }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-calendar mr-1"></i>
                                {{ \Carbon\Carbon::parse($dados['periodo']['inicio'])->format('d/m/Y') }} 
                                até 
                                {{ \Carbon\Carbon::parse($dados['periodo']['fim'])->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-green-100">Período Selecionado</div>
                            <div class="text-lg font-bold">
                                {{ \Carbon\Carbon::parse($dados['periodo']['inicio'])->diffInDays(\Carbon\Carbon::parse($dados['periodo']['fim'])) + 1 }} 
                                {{ \Carbon\Carbon::parse($dados['periodo']['inicio'])->diffInDays(\Carbon\Carbon::parse($dados['periodo']['fim'])) == 0 ? 'dia' : 'dias' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Relatório de Vendas Individual -->
                <div class="max-w-4xl mx-auto">
                    @include('fluxo-caixa.partials.relatorio-vendas', [
                        'vendas' => $dados['vendas'],
                        'resumo' => $dados['resumo_vendas'] ?? []
                    ])
                </div>



                <!-- Resumo Final -->
                <div class="mt-6 bg-gray-50 rounded-lg p-4 max-w-4xl mx-auto">
                    <h3 class="font-semibold text-gray-800 mb-3">
                        <i class="fas fa-chart-pie mr-2"></i>Resumo de Vendas do Período
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-sm text-gray-600">Total em Vendas</div>
                            <div class="text-2xl font-bold text-blue-600">
                                R$ {{ number_format($dados['resumo_vendas']['total_geral'] ?? 0, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600">Quantidade de Vendas</div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ $dados['resumo_vendas']['quantidade_vendas'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                    
                    @if(($dados['resumo_vendas']['vendas_estornadas'] ?? 0) > 0)
                        <div class="mt-4 text-center">
                            <div class="text-sm text-gray-600">Vendas Estornadas</div>
                            <div class="text-lg font-bold text-red-600">
                                {{ $dados['resumo_vendas']['vendas_estornadas'] }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function setPeriodoIndividual(tipo) {
    const hoje = new Date();
    let dataInicio, dataFim;
    
    switch(tipo) {
        case 'hoje':
            dataInicio = dataFim = hoje;
            break;
        case 'ontem':
            dataInicio = dataFim = new Date(hoje.getTime() - 24 * 60 * 60 * 1000);
            break;
        case 'semana':
            const inicioSemana = new Date(hoje);
            inicioSemana.setDate(hoje.getDate() - hoje.getDay());
            dataInicio = inicioSemana;
            dataFim = hoje;
            break;
        case 'mes':
            dataInicio = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
            dataFim = hoje;
            break;
    }
    
    document.getElementById('data_inicio').value = dataInicio.toISOString().split('T')[0];
    document.getElementById('data_fim').value = dataFim.toISOString().split('T')[0];
}

// Validação do formulário
document.querySelector('form').addEventListener('submit', function(e) {
    const vendedorId = document.getElementById('vendedor_id').value;
    const dataInicio = document.getElementById('data_inicio').value;
    const dataFim = document.getElementById('data_fim').value;
    
    if (!vendedorId) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Por favor, selecione um vendedor.'
        });
        return;
    }
    
    if (!dataInicio || !dataFim) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Por favor, selecione as datas inicial e final.'
        });
        return;
    }
    
    if (new Date(dataInicio) > new Date(dataFim)) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'A data inicial não pode ser maior que a data final.'
        });
        return;
    }
});
</script>
@endpush
@endsection