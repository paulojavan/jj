<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJ Calçados - Login</title>
    <link rel="icon" href="{{ asset('storage/uploads/sistema/icon.png') }}">
    @vite('resources/css/app.css')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen  flex items-center justify-center p-4">
 
<div class="container mx-auto px-4 py-8">
    <!-- Header com informações do cliente -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="flex items-center mb-4 md:mb-0">
                @if($cliente->foto)
                    <img 
                        src="{{ asset('storage/uploads/clientes/' . $cliente->pasta . '/' . $cliente->foto) }}" 
                        alt="Foto do cliente" 
                        class="w-16 h-16 rounded-full object-cover mr-4"
                        onerror="this.src='{{ asset('storage/uploads/sistema/default-avatar.png') }}'"
                    >
                @else
                    <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center mr-4">
                        <i class="fas fa-user text-gray-600 text-2xl"></i>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $cliente->nome }}</h1>
                    <p class="text-gray-600">CPF: {{ $cliente->cpf }}</p>
                </div>
            </div>
            
            <a 
                href="{{ route('parcelas.index') }}" 
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-200"
            >
                <i class="fas fa-search mr-2"></i>
                Nova Consulta
            </a>
        </div>
    </div>

    @if(isset($mensagem))
        <!-- Mensagem quando não há parcelas -->
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg text-center">
            <i class="fas fa-info-circle mr-2"></i>
            {{ $mensagem }}
        </div>
    @else
        

        <!-- Parcelas do Titular -->
        @if(!empty($parcelasData['titular']))
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user mr-2 text-blue-600"></i>
                    Parcelas do Titular
                </h2>
                
                <div class="grid gap-4">
                    @foreach($parcelasData['titular'] as $parcela)
                        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        class="parcela-checkbox w-5 h-5 text-yellow-600 bg-gray-100 border-gray-300 rounded focus:ring-yellow-500 focus:ring-2 mr-4"
                                        data-valor="{{ $parcela['valor_a_pagar'] }}"
                                        data-id="{{ $parcela['id'] }}"
                                        aria-label="Selecionar parcela {{ $parcela['numero'] }} do ticket {{ $parcela['ticket'] }}"
                                    >
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                Ticket: {{ $parcela['ticket'] }}
                                            </span>
                                            <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                Parcela: {{ $parcela['numero'] }}
                                            </span>
                                            @if($parcela['dias_atraso'] > 0)
                                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                    {{ $parcela['dias_atraso'] }} dias de atraso
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            <p>Vencimento: {{ $parcela['data_vencimento'] }}</p>
                                            <p>Valor original: {{ $parcela['valor_parcela_formatado'] }}</p>
                                            @if($parcela['tem_multa_juros'])
                                                <p class="text-red-600">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    Inclui multa e juros
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold {{ $parcela['tem_multa_juros'] ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $parcela['valor_a_pagar_formatado'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Parcelas de Clientes Autorizados -->
        @if(!empty($parcelasData['autorizados']))
            @foreach($parcelasData['autorizados'] as $idAutorizado => $dadosAutorizado)
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-friends mr-2 text-green-600"></i>
                        Parcelas de {{ $dadosAutorizado['nome'] }}
                    </h2>
                    
                    <div class="grid gap-4">
                        @foreach($dadosAutorizado['parcelas'] as $parcela)
                            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-500">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            class="parcela-checkbox w-5 h-5 text-yellow-600 bg-gray-100 border-gray-300 rounded focus:ring-yellow-500 focus:ring-2 mr-4"
                                            data-valor="{{ $parcela['valor_a_pagar'] }}"
                                            data-id="{{ $parcela['id'] }}"
                                            aria-label="Selecionar parcela {{ $parcela['numero'] }} do ticket {{ $parcela['ticket'] }} de {{ $dadosAutorizado['nome'] }}"
                                        >
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                    Ticket: {{ $parcela['ticket'] }}
                                                </span>
                                                <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                    Parcela: {{ $parcela['numero'] }}
                                                </span>
                                                @if($parcela['dias_atraso'] > 0)
                                                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                        {{ $parcela['dias_atraso'] }} dias de atraso
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                <p>Vencimento: {{ $parcela['data_vencimento'] }}</p>
                                                <p>Valor original: {{ $parcela['valor_parcela_formatado'] }}</p>
                                                @if($parcela['tem_multa_juros'])
                                                    <p class="text-red-600">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        Inclui multa e juros
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold {{ $parcela['tem_multa_juros'] ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $parcela['valor_a_pagar_formatado'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Resumo de seleção -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6" id="resumoSelecao">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <span class="text-gray-700">Parcelas selecionadas: </span>
                    <span class="font-bold text-yellow-700" id="totalSelecionadas">0</span>
                </div>
                <div>
                    <span class="text-gray-700">Total a pagar: </span>
                    <span class="font-bold text-yellow-700 text-xl" id="totalAPagar">R$ 0,00</span>
                </div>
            </div>
        </div>

        <!-- Botões de ação -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <div class="flex flex-col md:flex-row gap-4 justify-center">
                <button 
                    id="selecionarTodas" 
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded transition duration-200"
                >
                    <i class="fas fa-check-double mr-2"></i>
                    Selecionar Todas
                </button>
                
                <button 
                    id="desmarcarTodas" 
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-200"
                >
                    <i class="fas fa-times mr-2"></i>
                    Desmarcar Todas
                </button>
                

            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.parcela-checkbox');
    const totalSelecionadas = document.getElementById('totalSelecionadas');
    const totalAPagar = document.getElementById('totalAPagar');
    const selecionarTodas = document.getElementById('selecionarTodas');
    const desmarcarTodas = document.getElementById('desmarcarTodas');

    
    // Função para formatar valor monetário
    function formatarMoeda(valor) {
        return 'R$ ' + valor.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    
    // Função para atualizar totais
    function atualizarTotais() {
        let totalSelecionadasCount = 0;
        let totalValor = 0;
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                totalSelecionadasCount++;
                totalValor += parseFloat(checkbox.dataset.valor);
            }
        });
        
        totalSelecionadas.textContent = totalSelecionadasCount;
        totalAPagar.textContent = formatarMoeda(totalValor);

    }
    
    // Event listeners para checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', atualizarTotais);
    });
    
    // Selecionar todas
    selecionarTodas.addEventListener('click', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        atualizarTotais();
    });
    
    // Desmarcar todas
    desmarcarTodas.addEventListener('click', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        atualizarTotais();
    });

    
    // Inicializar totais
    atualizarTotais();
});
</script>
</body>
</html>