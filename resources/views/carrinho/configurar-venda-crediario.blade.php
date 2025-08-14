@extends('layouts.base')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Configurar Venda Crediário</h1>

            <!-- Mensagens de Alerta -->
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($creditValidation['requires_entry'])
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ $creditValidation['message'] }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Lado Esquerdo - Informações do Cliente -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        <i class="fas fa-user mr-2"></i>Informações do Cliente
                    </h2>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Nome:</span>
                            <span class="text-gray-800">{{ $clienteCrediario['nome'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">RG:</span>
                            <span class="text-gray-800">{{ $clienteCrediario['rg'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">CPF:</span>
                            <span class="text-gray-800">{{ $clienteCrediario['cpf'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Limite Total:</span>
                            <span class="text-green-600 font-semibold">R$ {{ number_format($clienteCrediario['limite'], 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Limite Disponível:</span>
                            <span class="text-blue-600 font-semibold">R$ {{ number_format($creditValidation['credit_info']['available_credit'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Lado Direito - Configuração da Compra -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        <i class="fas fa-shopping-cart mr-2"></i>Configuração da Compra
                    </h2>

                    <form id="credit-sale-form" method="POST" action="{{ route('carrinho.processar-venda-crediario') }}">
                        @csrf

                        <!-- Valor Total da Compra -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valor Total da Compra</label>
                            <div class="text-2xl font-bold text-green-600">
                                R$ {{ number_format($totalCompra, 2, ',', '.') }}
                            </div>
                        </div>

                        <!-- Entrada -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valor da Entrada</label>
                            <div class="flex gap-2 mb-2">
                                <input
                                    type="text"
                                    id="valor_entrada"
                                    name="valor_entrada"
                                    placeholder="0,00"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    @if($creditValidation['requires_entry'])
                                        min="{{ $creditValidation['minimum_entry'] }}"
                                        required
                                    @endif
                                >
                                <select name="metodo_entrada" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="pix">PIX</option>
                                    <option value="cartao">Cartão</option>
                                </select>
                            </div>
                            <button type="button" id="aplicar-entrada" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Aplicar Valor da Entrada
                            </button>
                            @if($creditValidation['requires_entry'])
                                <div class="mt-2 text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded p-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Entrada mínima necessária:</strong> R$ {{ number_format($creditValidation['minimum_entry'], 2, ',', '.') }}
                                </div>
                            @endif
                        </div>

                        <!-- Token -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Token de Autorização</label>
                            <input
                                type="text"
                                id="token"
                                name="token"
                                placeholder="Digite o token do cliente"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-2"
                                required
                            >
                            <div class="flex gap-2">
                                @php
                                    // Extrair os dois primeiros nomes do cliente
                                    $nomeCompleto = $clienteCrediario['nome'];
                                    $partesNome = explode(' ', $nomeCompleto);
                                    $doisPrimeirosNomes = implode(' ', array_slice($partesNome, 0, 2));

                                    // Limpar o número do telefone (remover caracteres não numéricos)
                                    $telefoneCliente = preg_replace('/\D/', '', DB::table('clientes')->where('id', $clienteCrediario['id'])->value('telefone') ?? '');

                                    // Criar a mensagem do WhatsApp
                                    $mensagemWhatsApp = "Olá {$doisPrimeirosNomes}, seu token para finalizar a compra na loja Joécio calçados é: {$clienteCrediario['token']}";

                                    // Codificar a mensagem para URL
                                    $mensagemCodificada = urlencode($mensagemWhatsApp);

                                    // Criar o link do WhatsApp
                                    $linkWhatsApp = "https://wa.me/55{$telefoneCliente}?text={$mensagemCodificada}";
                                @endphp

                                <a href="{{ $linkWhatsApp }}"
                                   target="_blank"
                                   class="inline-flex items-center bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition-colors">
                                    <i class="fab fa-whatsapp mr-2"></i>
                                    Enviar Token
                                </a>
                                <button type="button" id="verificar-token" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                    Verificar Token
                                </button>
                            </div>
                            <div id="token-status" class="mt-2 text-sm"></div>
                        </div>

                        <!-- Data de Vencimento -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Primeira Data de Vencimento</label>
                            <select name="data_vencimento" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Selecione a data</option>
                                @foreach($datasVencimento as $data)
                                    <option value="{{ $data['value'] }}" data-date="{{ $data['date'] }}">{{ $data['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Quantidade de Parcelas -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade de Parcelas</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" id="parcelas-container">
                                <!-- As opções de parcelas serão geradas dinamicamente pelo JavaScript -->
                            </div>
                            <input type="hidden" name="quantidade_parcelas" id="quantidade_parcelas_hidden" required>
                            <div id="valor-parcela" class="mt-4 text-sm"></div>
                        </div>

                        <!-- Comprador -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Comprador</label>
                            <select name="comprador" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                                <option value="titular">{{ $clienteCrediario['nome'] }} (Titular)</option>
                                @foreach($autorizados as $autorizado)
                                    <option value="{{ $autorizado->id }}">{{ $autorizado->nome }} (Autorizado)</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Botão Finalizar -->
                        <button
                            type="submit"
                            id="finalizar-venda"
                            class="w-full bg-green-600 text-white px-6 py-3 rounded-md font-semibold hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                            disabled
                        >
                            <i class="fas fa-check-circle mr-2"></i>Finalizar Venda
                        </button>
                    </form>
                </div>
            </div>

            <!-- Botão Voltar -->
            <div class="mt-6">
                <a href="{{ route('carrinho.pesquisar-cliente') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    ← Voltar à Pesquisa
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tokenInput = document.getElementById('token');
    const verificarTokenBtn = document.getElementById('verificar-token');
    const finalizarVendaBtn = document.getElementById('finalizar-venda');
    const tokenStatus = document.getElementById('token-status');
    const parcelasContainer = document.getElementById('parcelas-container');
    const quantidadeParcelasHidden = document.getElementById('quantidade_parcelas_hidden');
    const valorParcelaDiv = document.getElementById('valor-parcela');
    const valorEntradaInput = document.getElementById('valor_entrada');
    const aplicarEntradaBtn = document.getElementById('aplicar-entrada');
    const dataVencimentoSelect = document.querySelector('select[name="data_vencimento"]');

    let tokenVerificado = false;
    let valorEntrada = 0;
    let entradaAplicada = false;
    let dataSelecionada = false;
    const totalCompra = {{ $totalCompra }};
    const tokenCorreto = '{{ $clienteCrediario['token'] }}';
    const minimumEntry = {{ $creditValidation['minimum_entry'] ?? 0 }};
    const requiresEntry = {{ $creditValidation['requires_entry'] ? 'true' : 'false' }};

    // Formatação monetária para o campo de entrada
    valorEntradaInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = (value / 100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        e.target.value = value;
    });

    // Verificação de token
    verificarTokenBtn.addEventListener('click', function() {
        const token = tokenInput.value.trim();

        if (!token) {
            tokenStatus.innerHTML = '<span class="text-red-600">Digite o token primeiro.</span>';
            return;
        }

        if (token === tokenCorreto) {
            tokenVerificado = true;
            tokenStatus.innerHTML = '<span class="text-green-600"><i class="fas fa-check mr-1"></i>Token verificado com sucesso!</span>';
            updateFinalizarButton();
        } else {
            tokenVerificado = false;
            tokenStatus.innerHTML = '<span class="text-red-600"><i class="fas fa-times mr-1"></i>Token inválido.</span>';
            updateFinalizarButton();
        }
    });

    // Aplicar entrada
    aplicarEntradaBtn.addEventListener('click', function() {
        const valorFormatado = valorEntradaInput.value;

        if (!valorFormatado || valorFormatado === '0,00') {
            alert('Digite um valor de entrada válido.');
            return;
        }

        // Converte valor formatado para número
        valorEntrada = parseFloat(valorFormatado.replace(/\./g, '').replace(',', '.')) || 0;

        // Verifica entrada mínima se necessária
        if (minimumEntry > 0 && valorEntrada < minimumEntry) {
            const diferenca = minimumEntry - valorEntrada;
            const percentualFaltante = ((diferenca / minimumEntry) * 100).toFixed(1);

            Swal.fire({
                title: '<strong><i class="fas fa-exclamation-triangle text-red-500"></i> Entrada Insuficiente</strong>',
                html: `
                    <div class="text-left">
                        <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-400 rounded">
                            <p class="text-red-800 font-medium">
                                <i class="fas fa-times-circle mr-2"></i>
                                O valor informado é menor que a entrada mínima necessária
                            </p>
                        </div>

                        <div class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-red-800 mb-3 flex items-center">
                                <i class="fas fa-calculator mr-2"></i>Detalhes da Validação
                            </h4>
                            <div class="grid grid-cols-1 gap-3">
                                <div class="flex justify-between items-center p-2 bg-white rounded border">
                                    <span class="font-medium text-gray-700 flex items-center">
                                        <i class="fas fa-arrow-down text-red-500 mr-2"></i>Entrada informada:
                                    </span>
                                    <span class="font-bold text-red-600">R$ ${valorEntrada.toFixed(2).replace('.', ',')}</span>
                                </div>
                                <div class="flex justify-between items-center p-2 bg-white rounded border">
                                    <span class="font-medium text-gray-700 flex items-center">
                                        <i class="fas fa-arrow-up text-green-500 mr-2"></i>Entrada mínima:
                                    </span>
                                    <span class="font-bold text-green-600">R$ ${minimumEntry.toFixed(2).replace('.', ',')}</span>
                                </div>
                                <div class="flex justify-between items-center p-2 bg-yellow-50 rounded border border-yellow-300">
                                    <span class="font-medium text-gray-700 flex items-center">
                                        <i class="fas fa-plus text-blue-500 mr-2"></i>Valor faltante:
                                    </span>
                                    <span class="font-bold text-blue-600">R$ ${diferenca.toFixed(2).replace('.', ',')}</span>
                                </div>
                                <div class="text-center p-2 bg-orange-50 rounded border border-orange-300">
                                    <span class="text-sm text-orange-700">
                                        <i class="fas fa-percentage mr-1"></i>
                                        Faltam ${percentualFaltante}% do valor mínimo
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-lightbulb mr-2"></i>
                                <strong>Dica:</strong> Clique em "Ajustar Automaticamente" para preencher o valor mínimo necessário.
                            </p>
                        </div>

                        <div class="text-center text-xs text-gray-500 mt-3">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Esta validação garante que o limite de crédito seja respeitado
                        </div>
                    </div>
                `,
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-magic mr-2"></i>Ajustar Automaticamente',
                cancelButtonText: '<i class="fas fa-edit mr-2"></i>Corrigir Manualmente',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#dc2626',
                allowOutsideClick: false,
                customClass: {
                    popup: 'swal2-popup-entrada-minima',
                    title: 'swal2-title-entrada-minima',
                    confirmButton: 'swal2-confirm-entrada-minima',
                    cancelButton: 'swal2-cancel-entrada-minima'
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ajusta automaticamente para o valor mínimo
                    valorEntradaInput.value = minimumEntry.toFixed(2).replace('.', ',');
                    valorEntradaInput.dispatchEvent(new Event('input'));

                    // Mostra confirmação de ajuste
                    Swal.fire({
                        title: 'Valor Ajustado!',
                        html: `
                            <div class="text-center">
                                <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
                                <p class="mb-2">Entrada ajustada para o valor mínimo:</p>
                                <p class="text-2xl font-bold text-green-600">R$ ${minimumEntry.toFixed(2).replace('.', ',')}</p>
                            </div>
                        `,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'swal2-popup-sucesso'
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Foca no campo para correção manual
                    valorEntradaInput.focus();
                    valorEntradaInput.select();
                }
            });
            return;
        }

        // Verifica se entrada não excede o total
        if (valorEntrada >= totalCompra) {
            alert('Valor da entrada não pode ser maior ou igual ao total da compra.');
            return;
        }

        entradaAplicada = true;
        aplicarEntradaBtn.innerHTML = '<i class="fas fa-check mr-1"></i>Entrada Aplicada';
        aplicarEntradaBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        aplicarEntradaBtn.classList.add('bg-green-600', 'hover:bg-green-700');

        generateParcelasOptions();
        updateEntradaDisplay();
    });

    let selectedParcelas = 0;

    // Event listener para data de vencimento
    dataVencimentoSelect.addEventListener('change', function() {
        dataSelecionada = this.value !== '';
        updateFinalizarButton();
    });

    function generateParcelasOptions() {

        const valorFinanciar = totalCompra - valorEntrada;
        parcelasContainer.innerHTML = '';

        for (let i = 1; i <= 12; i++) {
            const valorParcela = Math.ceil(valorFinanciar / i);
            const isValid = valorParcela >= 20;

            if (!isValid && i > 1) {
                // Se a parcela fica menor que R$ 20, para de gerar opções
                break;
            }

            const parcelaOption = document.createElement('div');
            parcelaOption.className = `parcela-option border-2 rounded-lg p-3 cursor-pointer transition-all ${
                isValid ? 'border-gray-300 hover:border-blue-500 hover:bg-blue-50' : 'border-red-300 bg-red-50 cursor-not-allowed opacity-50'
            }`;

            parcelaOption.innerHTML = `
                <div class="text-center">
                    <div class="font-semibold text-lg">${i}x</div>
                    <div class="text-sm ${isValid ? 'text-green-600' : 'text-red-600'}">
                        R$ ${valorParcela.toFixed(2).replace('.', ',')}
                    </div>
                    ${!isValid ? '<div class="text-xs text-red-500 mt-1">Mín. R$ 20,00</div>' : ''}
                </div>
            `;

            if (isValid) {
                parcelaOption.addEventListener('click', function() {
                    selectParcelas(i, valorParcela);
                });
            }

            parcelasContainer.appendChild(parcelaOption);
        }
    }

    function selectParcelas(quantidade, valorParcela) {
        // Remove seleção anterior
        document.querySelectorAll('.parcela-option').forEach(option => {
            option.classList.remove('border-blue-500', 'bg-blue-100');
            option.classList.add('border-gray-300');
        });

        // Adiciona seleção atual
        event.target.closest('.parcela-option').classList.remove('border-gray-300');
        event.target.closest('.parcela-option').classList.add('border-blue-500', 'bg-blue-100');

        selectedParcelas = quantidade;
        quantidadeParcelasHidden.value = quantidade;

        updateParcelaInfo(quantidade, valorParcela);
        updateFinalizarButton();
    }

    function updateParcelaInfo(quantidade, valorParcela) {
        const valorFinanciar = totalCompra - valorEntrada;
        const totalParcelas = valorParcela * (quantidade - 1);
        const ultimaParcela = valorFinanciar - totalParcelas;

        valorParcelaDiv.innerHTML = `
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-blue-800 mb-2">Resumo do Financiamento</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong>Valor a financiar:</strong><br>
                        <span class="text-blue-600">R$ ${valorFinanciar.toFixed(2).replace('.', ',')}</span>
                    </div>
                    <div>
                        <strong>Quantidade de parcelas:</strong><br>
                        <span class="text-blue-600">${quantidade}x</span>
                    </div>
                    <div>
                        <strong>Parcelas 1 a ${quantidade - 1}:</strong><br>
                        <span class="text-blue-600">R$ ${valorParcela.toFixed(2).replace('.', ',')}</span>
                    </div>
                    <div>
                        <strong>Última parcela:</strong><br>
                        <span class="text-blue-600">R$ ${ultimaParcela.toFixed(2).replace('.', ',')}</span>
                    </div>
                </div>
            </div>
        `;
    }

    function updateEntradaDisplay() {
        if (entradaAplicada) {
            const valorRestante = totalCompra - valorEntrada;
            const entradaInfo = document.createElement('div');
            entradaInfo.className = 'mt-2 p-2 bg-green-50 border border-green-200 rounded text-sm';
            entradaInfo.innerHTML = `
                <strong>Entrada aplicada:</strong> R$ ${valorEntrada.toFixed(2).replace('.', ',')}<br>
                <strong>Valor a financiar:</strong> R$ ${valorRestante.toFixed(2).replace('.', ',')}
            `;

            // Remove info anterior se existir
            const existingInfo = aplicarEntradaBtn.parentNode.querySelector('.mt-2');
            if (existingInfo) {
                existingInfo.remove();
            }

            aplicarEntradaBtn.parentNode.appendChild(entradaInfo);
        }
    }

    function updateFinalizarButton() {
        const todasCondicoesAtendidas = tokenVerificado && dataSelecionada && selectedParcelas > 0;

        finalizarVendaBtn.disabled = !todasCondicoesAtendidas;

        // Atualiza o texto do botão baseado no status
        if (todasCondicoesAtendidas) {
            finalizarVendaBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Finalizar Venda';
            finalizarVendaBtn.classList.remove('bg-gray-400');
            finalizarVendaBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        } else {
            let mensagem = 'Finalizar Venda';
            let faltando = [];

            if (!tokenVerificado) faltando.push('token');
            if (!dataSelecionada) faltando.push('data');
            if (selectedParcelas === 0) faltando.push('parcelas');

            if (faltando.length > 0) {
                mensagem = `Faltam: ${faltando.join(', ')}`;
            }

            finalizarVendaBtn.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i>${mensagem}`;
            finalizarVendaBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            finalizarVendaBtn.classList.add('bg-gray-400');
        }
    }

    // Reset entrada quando valor muda
    valorEntradaInput.addEventListener('input', function() {
        if (entradaAplicada) {
            entradaAplicada = false;
            valorEntrada = 0;
            selectedParcelas = 0;
            quantidadeParcelasHidden.value = '';

            aplicarEntradaBtn.innerHTML = 'Aplicar Valor da Entrada';
            aplicarEntradaBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            aplicarEntradaBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');

            // Remove info da entrada
            const existingInfo = aplicarEntradaBtn.parentNode.querySelector('.mt-2');
            if (existingInfo) {
                existingInfo.remove();
            }

            // Reset parcelas
            parcelasContainer.innerHTML = '<div class="col-span-full text-center text-gray-500 py-4">Aplique o valor da entrada primeiro</div>';
            valorParcelaDiv.innerHTML = '';

            // Atualiza botão finalizar
            updateFinalizarButton();
        }
    });

    if (!requiresEntry) {
        entradaAplicada = true;
        valorEntrada = 0;
        generateParcelasOptions();
    } else {
        parcelasContainer.innerHTML = '<div class="col-span-full text-center text-gray-500 py-4">Preencha o valor da entrada</div>';
        valorEntradaInput.addEventListener('input', function() {
            const valorFormatado = this.value;
            if (valorFormatado && valorFormatado !== '0,00') {
                valorEntrada = parseFloat(valorFormatado.replace(/\./g, '').replace(',', '.')) || 0;
                if (valorEntrada >= minimumEntry && valorEntrada < totalCompra) {
                    entradaAplicada = true;
                    generateParcelasOptions();
                }
            } else {
                entradaAplicada = false;
                parcelasContainer.innerHTML = '<div class="col-span-full text-center text-gray-500 py-4">Preencha o valor da entrada</div>';
                valorParcelaDiv.innerHTML = '';
            }
        });
    }

    // Inicializa o estado do botão finalizar
    updateFinalizarButton();

    // Exibe SweetAlert se entrada for necessária
    @if($creditValidation['requires_entry'])
        Swal.fire({
            title: '<strong><i class="fas fa-exclamation-triangle text-yellow-500"></i> Entrada Necessária</strong>',
            html: `
                <div class="text-left">
                    <p class="mb-3">Para prosseguir com esta venda crediário, é necessário uma entrada mínima.</p>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium">Valor da compra:</span>
                            <span class="font-bold text-green-600">R$ {{ number_format($totalCompra, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium">Limite disponível:</span>
                            <span class="font-bold text-blue-600">R$ {{ number_format($creditValidation['credit_info']['available_credit'], 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium">Entrada mínima:</span>
                            <span class="font-bold text-red-600">R$ {{ number_format($creditValidation['minimum_entry'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Configure a entrada no formulário abaixo para continuar.
                    </p>
                </div>
            `,
            icon: 'warning',
            confirmButtonText: '<i class="fas fa-check mr-2"></i>Entendi',
            confirmButtonColor: '#f59e0b',
            allowOutsideClick: false,
            customClass: {
                popup: 'swal2-popup-custom',
                title: 'swal2-title-custom',
                confirmButton: 'swal2-confirm-custom'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Foca no campo de entrada
                valorEntradaInput.focus();
            }
        });
    @endif
});
</script>

<!-- SweetAlert2 CSS e JS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<!-- Estilos customizados para SweetAlert2 -->
<style>
.swal2-popup-custom {
    border-radius: 15px !important;
    padding: 2rem !important;
}

.swal2-title-custom {
    color: #dc2626 !important;
    font-size: 1.5rem !important;
}

.swal2-confirm-custom {
    background-color: #f59e0b !important;
    color: white !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 0.75rem 1.5rem !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
}

.swal2-confirm-custom:hover {
    background-color: #d97706 !important;
    transform: translateY(-1px) !important;
}

/* Estilos específicos para validação de entrada mínima */
.swal2-popup-entrada-minima {
    border-radius: 20px !important;
    padding: 2rem !important;
    max-width: 600px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
}

.swal2-title-entrada-minima {
    color: #dc2626 !important;
    font-size: 1.6rem !important;
    margin-bottom: 1rem !important;
}

.swal2-confirm-entrada-minima {
    background: linear-gradient(135deg, #059669, #047857) !important;
    color: white !important;
    border: none !important;
    border-radius: 10px !important;
    padding: 0.875rem 1.75rem !important;
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3) !important;
}

.swal2-confirm-entrada-minima:hover {
    background: linear-gradient(135deg, #047857, #065f46) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4) !important;
}

.swal2-cancel-entrada-minima {
    background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
    color: white !important;
    border: none !important;
    border-radius: 10px !important;
    padding: 0.875rem 1.75rem !important;
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3) !important;
    margin-right: 0.75rem !important;
}

.swal2-cancel-entrada-minima:hover {
    background: linear-gradient(135deg, #b91c1c, #991b1b) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4) !important;
}

.swal2-popup-sucesso {
    border-radius: 20px !important;
    padding: 2rem !important;
    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.2) !important;
}

/* Animação para os ícones */
@keyframes pulse-icon {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.swal2-popup-entrada-minima .fas {
    animation: pulse-icon 2s infinite;
}

/* Efeito hover nos cards de informação */
.swal2-popup-entrada-minima .bg-white:hover {
    transform: translateX(2px);
    transition: transform 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4) !important;
}
</style>
@endsection
