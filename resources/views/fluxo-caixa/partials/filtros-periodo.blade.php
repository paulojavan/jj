<form id="form-filtros" method="POST" action="{{ route('fluxo-caixa.relatorio-geral') }}" class="bg-gray-50 rounded-lg p-4 mb-6">
    @csrf
    
    @if($permiteEscolherPeriodo ?? false)
        <!-- Filtros para Administrador -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt mr-1"></i>Data Inicial
                </label>
                <input type="date" 
                       id="data_inicio" 
                       name="data_inicio" 
                       value="{{ $dataInicio ?? now()->format('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt mr-1"></i>Data Final
                </label>
                <input type="date" 
                       id="data_fim" 
                       name="data_fim" 
                       value="{{ $dataFim ?? now()->format('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <button type="submit" 
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
            </div>
        </div>
        
        <!-- Botões de período rápido -->
        <div class="flex flex-wrap gap-2 mt-4">
            <button type="button" onclick="setPeriodo('hoje')" 
                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm transition-colors">
                Hoje
            </button>
            <button type="button" onclick="setPeriodo('ontem')" 
                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm transition-colors">
                Ontem
            </button>
            <button type="button" onclick="setPeriodo('semana')" 
                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm transition-colors">
                Esta Semana
            </button>
            <button type="button" onclick="setPeriodo('mes')" 
                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm transition-colors">
                Este Mês
            </button>
        </div>
    @else
        <!-- Informação para Vendedor -->
        <div class="text-center">
            <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-lg">
                <i class="fas fa-info-circle mr-2"></i>
                <span class="font-medium">Visualizando dados de hoje: {{ now()->format('d/m/Y') }}</span>
            </div>
            <button type="submit" 
                    class="ml-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>Atualizar
            </button>
        </div>
    @endif
</form>

@if($permiteEscolherPeriodo ?? false)
<script>
function setPeriodo(tipo) {
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
    
    document.getElementById('form-filtros').submit();
}
</script>
@endif