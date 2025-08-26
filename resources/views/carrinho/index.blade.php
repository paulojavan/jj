@extends('layouts.base')
@section('content')
<div class="content">
    <!-- Header do Carrinho -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row items-center justify-between mb-4">
            <div class="flex items-center mb-4 sm:mb-0">
                <i class="fas fa-shopping-cart text-red-600 text-3xl mr-3"></i>
                <div>
                    <h1 class="text-3xl font-bold text-red-600">Carrinho de Compras</h1>
                    <p class="text-gray-600">Funcionário: <span class="font-semibold text-red-600">{{ Auth::user()->name }}</span></p>
                </div>
            </div>
            @if(count($carrinho) > 0)
                <div class="bg-yellow-100 px-4 py-2 rounded-lg border border-yellow-300">
                    <span class="text-red-600 font-semibold">
                        <i class="fas fa-box mr-1"></i>{{ count($carrinho) }} item(s) no carrinho
                    </span>
                </div>
            @endif
        </div>
    </div>

    <x-alert />

    @if(count($carrinho) > 0)
        <!-- Tabela do Carrinho -->
        <div class="table-container mb-6">
            <table class="table">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-3">Produto</th>
                        <th class="px-4 py-3 center">Numeração</th>
                        <th class="px-4 py-3 center">Preço</th>
                        <th class="px-4 py-3 center">Quantidade</th>
                        <th class="px-4 py-3 center">Subtotal</th>
                        <th class="px-4 py-3 center">Ações</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @foreach($carrinho as $itemId => $item)
                        <tr class="table-row">
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <img src="{{ asset('storage/uploads/produtos/' . $item['foto']) }}"
                                         alt="{{ $item['nome'] }}"
                                         class="w-16 h-16 object-cover rounded-lg shadow-sm border border-yellow-200">
                                    <div class="flex-1">
                                        <span class="font-semibold text-red-600">{{ $item['nome'] }}</span>
                                        <div class="text-sm text-gray-500 sm:hidden">
                                            Nº {{ $item['numeracao'] }} | R$ {{ number_format($item['preco'], 2, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="table-cell text-center hidden sm:table-cell">
                                <span class="bg-yellow-100 text-red-600 px-2 py-1 rounded-full text-sm font-semibold">
                                    {{ $item['numeracao'] }}
                                </span>
                            </td>
                            <td class="table-cell text-center hidden sm:table-cell">
                                <span class="font-semibold text-green-600">
                                    R$ {{ number_format($item['preco'], 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="table-cell text-center">
                                <form action="{{ route('carrinho.atualizar', $itemId) }}" method="POST" class="flex items-center justify-center">
                                    @csrf
                                    @method('PATCH')
                                    <select name="quantidade" class="form-input w-16 text-center border-yellow-300 focus:border-yellow-500" onchange="this.form.submit()">
                                        @for($i = 1; $i <= Session::get('quantidade_disponivel_' . $itemId, 1); $i++)
                                            <option value="{{ $i }}" {{ $item['quantidade'] == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </form>
                            </td>
                            <td class="table-cell text-center">
                                <span class="font-bold text-red-600 text-lg">
                                    R$ {{ number_format($item['preco'] * $item['quantidade'], 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="table-actions">
                                <form action="{{ route('carrinho.remover', $itemId) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                                        <i class="fas fa-trash mr-1"></i>
                                        <span class="hidden sm:inline">Remover</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

        <!-- Seção de Pagamento e Desconto -->
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl border-2 border-yellow-300 p-6 mb-6">
            <div class="flex items-center mb-6">
                <i class="fas fa-calculator text-red-600 text-2xl mr-3"></i>
                <h3 class="text-xl font-bold text-red-600">Pagamento e Desconto</h3>
            </div>

            <form action="{{ route('carrinho.aplicarDesconto') }}" method="POST">
                @csrf
                
                <!-- Tipo de Desconto -->
                <div class="mb-6">
                    <label for="tipo_desconto" class="block text-sm font-semibold text-red-600 mb-2">
                        <i class="fas fa-percentage mr-1"></i>Tipo de Desconto
                    </label>
                    <select name="tipo_desconto" id="tipo_desconto" class="form-input border-yellow-300 focus:border-yellow-500" onchange="toggleManualMode()">
                        <option value="sem desconto">Sem Desconto</option>
                        <option value="manual" {{ Session::get('descontos_aplicados.tipo_selecionado') == 'manual' ? 'selected' : '' }}>Preenchimento Manual</option>
                        @if($descontos)
                            <option value="avista" {{ Session::get('descontos_aplicados.tipo_selecionado') == 'avista' ? 'selected' : '' }}>À vista ({{ $descontos->avista ?? 0 }}%)</option>
                            <option value="pix" {{ Session::get('descontos_aplicados.tipo_selecionado') == 'pix' ? 'selected' : '' }}>PIX ({{ $descontos->pix ?? 0 }}%)</option>
                            <option value="debito" {{ Session::get('descontos_aplicados.tipo_selecionado') == 'debito' ? 'selected' : '' }}>Débito ({{ $descontos->debito ?? 0 }}%)</option>
                            <option value="credito" {{ Session::get('descontos_aplicados.tipo_selecionado') == 'credito' ? 'selected' : '' }}>Crédito ({{ $descontos->credito ?? 0 }}%)</option>
                            <option value="crediario" {{ Session::get('descontos_aplicados.tipo_selecionado') == 'crediario' ? 'selected' : '' }}>Crediário</option>
                        @endif
                    </select>
                </div>

                <!-- Valores de Pagamento -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- À Vista -->
                    <div class="bg-white p-4 rounded-lg border border-yellow-200">
                        <label for="valor_avista" class="flex items-center text-sm font-semibold text-yellow-700 mb-2">
                            <i class="fas fa-money-bill-wave mr-2"></i>Valor à Vista
                        </label>
                        <input type="text" name="valor_avista" id="valor_avista" 
                               class="form-input payment-field border-yellow-300 focus:border-yellow-500" 
                               value="{{ Session::get('descontos_aplicados.avista', '0,00') }}" readonly>
                        <input type="hidden" name="valor_avista_manual" id="valor_avista_manual" value="{{ Session::get('descontos_aplicados.avista', '0,00') }}">
                    </div>

                    <!-- PIX -->
                    <div class="bg-white p-4 rounded-lg border border-green-200">
                        <label for="valor_pix" class="flex items-center text-sm font-semibold text-green-700 mb-2">
                            <i class="fas fa-mobile-alt mr-2"></i>Valor PIX
                        </label>
                        <input type="text" name="valor_pix" id="valor_pix" 
                               class="form-input payment-field border-green-300 focus:border-green-500" 
                               value="{{ Session::get('descontos_aplicados.pix', '0,00') }}" readonly>
                        <input type="hidden" name="valor_pix_manual" id="valor_pix_manual" value="{{ Session::get('descontos_aplicados.pix', '0,00') }}">
                    </div>

                    <!-- Cartão -->
                    <div class="bg-white p-4 rounded-lg border border-blue-200">
                        <label for="valor_cartao" class="flex items-center text-sm font-semibold text-blue-700 mb-2">
                            <i class="fas fa-credit-card mr-2"></i>Valor Cartão
                            @if(Session::get('descontos_aplicados.tipo_selecionado') == 'credito')
                                <span class="ml-1 text-xs">(Crédito {{ $descontos->credito ?? 0 }}%)</span>
                            @elseif(Session::get('descontos_aplicados.tipo_selecionado') == 'debito')
                                <span class="ml-1 text-xs">(Débito {{ $descontos->debito ?? 0 }}%)</span>
                            @endif
                        </label>
                        <input type="text" name="valor_cartao" id="valor_cartao" 
                               class="form-input payment-field border-blue-300 focus:border-blue-500" 
                               value="{{ Session::get('descontos_aplicados.cartao', '0,00') }}" readonly>
                        <input type="hidden" name="valor_cartao_manual" id="valor_cartao_manual" value="{{ Session::get('descontos_aplicados.cartao', '0,00') }}">
                    </div>

                    <!-- Crediário -->
                    <div class="bg-white p-4 rounded-lg border border-purple-200">
                        <label for="valor_crediario" class="flex items-center text-sm font-semibold text-purple-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2"></i>Valor Crediário
                        </label>
                        <input type="text" name="valor_crediario" id="valor_crediario" 
                               class="form-input payment-field border-purple-300 focus:border-purple-500" 
                               value="{{ Session::get('descontos_aplicados.crediario', '0,00') }}" readonly>
                        <input type="hidden" name="valor_crediario_manual" id="valor_crediario_manual" value="{{ Session::get('descontos_aplicados.crediario', '0,00') }}">
                    </div>
                </div>

                <!-- Informações do Cliente -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg border border-yellow-200">
                        <label for="valor_dinheiro" class="flex items-center text-sm font-semibold text-red-600 mb-2">
                            <i class="fas fa-hand-holding-usd mr-2"></i>Dinheiro Recebido
                        </label>
                        <input type="text" name="valor_dinheiro" id="valor_dinheiro" 
                               class="form-input border-red-300 focus:border-red-500" 
                               value="{{ Session::get('valor_dinheiro_recebido', '') }}" 
                               placeholder="R$ 0,00">
                    </div>

                    <div class="bg-white p-4 rounded-lg border border-yellow-200">
                        <label for="nome_cliente" class="flex items-center text-sm font-semibold text-red-600 mb-2">
                            <i class="fas fa-user mr-2"></i>Nome do Cliente
                        </label>
                        <input type="text" name="nome_cliente" id="nome_cliente" 
                               class="form-input border-red-300 focus:border-red-500" 
                               value="{{ $clienteVendedor['nome_cliente'] ?? '' }}"
                               placeholder="Digite o nome do cliente">
                    </div>
                </div>

                <!-- Vendedor Atendente -->
                <div class="bg-white p-4 rounded-lg border border-yellow-200 mb-6">
                    <label for="vendedor_atendente" class="flex items-center text-sm font-semibold text-red-600 mb-2">
                        <i class="fas fa-user-tie mr-2"></i>Vendedor Atendente
                    </label>
                    <select name="vendedor_atendente" id="vendedor_atendente" 
                            class="form-input border-red-300 focus:border-red-500" required>
                        <option value="">Selecione o vendedor atendente</option>
                        @foreach($vendedores as $vendedor)
                            <option value="{{ $vendedor->id }}" {{ ($clienteVendedor['vendedor_atendente_id'] ?? '') == $vendedor->id ? 'selected' : '' }}>
                                {{ $vendedor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botão de Ajustar -->
                <div class="text-center">
                    <button type="submit" class="btn-yellow px-8 py-3 text-lg">
                        <i class="fas fa-calculator mr-2"></i>Aplicar Desconto
                    </button>
                </div>
            </form>
        </div>

        <!-- Resumo do Total -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl border-2 border-red-300 p-6 mb-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-receipt text-red-600 text-2xl mr-3"></i>
                <h3 class="text-xl font-bold text-red-600">Resumo da Compra</h3>
            </div>

            <div class="space-y-3">
                <!-- Total Original -->
                <div class="flex justify-between items-center py-2 border-b border-red-200">
                    <span class="text-gray-700 font-medium">Subtotal:</span>
                    <span class="text-lg font-semibold text-gray-800">R$ {{ number_format($total, 2, ',', '.') }}</span>
                </div>

                @php
                    $tipoSelecionado = Session::get('descontos_aplicados.tipo_selecionado');
                    $totalExibir = $total;
                    $descontoAplicado = 0;
                    
                    if ($tipoSelecionado) {
                        if ($tipoSelecionado === 'manual') {
                            // No modo manual, soma todos os valores preenchidos
                            $descontosAplicados = Session::get('descontos_aplicados', []);
                            $somaManual = 0;
                            foreach (['avista', 'pix', 'cartao', 'crediario'] as $tipo) {
                                if (isset($descontosAplicados[$tipo])) {
                                    $valor = floatval(str_replace(',', '.', str_replace('.', '', $descontosAplicados[$tipo])));
                                    $somaManual += $valor;
                                }
                            }
                            $totalExibir = $somaManual;
                            $descontoAplicado = $total - $totalExibir;
                        } elseif (in_array($tipoSelecionado, ['credito', 'debito'])) {
                            $totalExibir = floatval(str_replace(',', '.', str_replace('.', '', Session::get('descontos_aplicados.cartao', number_format($total, 2, ',', '.')))));
                            $descontoAplicado = $total - $totalExibir;
                        } else {
                            $totalExibir = floatval(str_replace(',', '.', str_replace('.', '', Session::get('descontos_aplicados.' . $tipoSelecionado, number_format($total, 2, ',', '.')))));
                            $descontoAplicado = $total - $totalExibir;
                        }
                    }
                @endphp

                <!-- Desconto Aplicado -->
                @if($descontoAplicado > 0)
                <div class="flex justify-between items-center py-2 border-b border-red-200">
                    <span class="text-green-600 font-medium">
                        <i class="fas fa-tag mr-1"></i>Desconto:
                    </span>
                    <span class="text-lg font-semibold text-green-600">- R$ {{ number_format($descontoAplicado, 2, ',', '.') }}</span>
                </div>
                @endif

                <!-- Total Final -->
                <div class="flex justify-between items-center py-3 bg-yellow-100 rounded-lg px-4 border-2 border-yellow-300">
                    <span class="text-xl font-bold text-red-600">TOTAL:</span>
                    <span class="text-2xl font-bold text-red-600">R$ {{ number_format($totalExibir, 2, ',', '.') }}</span>
                </div>

                <!-- Troco -->
                @if($dadosTroco['tem_troco'])
                <div class="flex justify-between items-center py-3 {{ $dadosTroco['troco_positivo'] ? 'bg-green-100 border-green-300' : 'bg-red-100 border-red-300' }} rounded-lg px-4 border-2">
                    <span class="font-bold {{ $dadosTroco['troco_positivo'] ? 'text-green-700' : 'text-red-700' }}">
                        <i class="fas fa-{{ $dadosTroco['troco_positivo'] ? 'hand-holding-usd' : 'exclamation-triangle' }} mr-2"></i>
                        {{ $dadosTroco['troco_positivo'] ? 'TROCO:' : 'FALTAM:' }}
                    </span>
                    <span class="text-xl font-bold {{ $dadosTroco['troco_positivo'] ? 'text-green-700' : 'text-red-700' }}">
                        R$ {{ $dadosTroco['troco_formatado'] }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <!-- Botão Limpar Carrinho -->
            <form action="{{ route('carrinho.limpar') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-red w-full sm:w-auto" onclick="return confirm('Tem certeza que deseja limpar o carrinho?')">
                    <i class="fas fa-trash mr-2"></i>Limpar Carrinho
                </button>
            </form>
            
            @php
                // Verifica se há algum desconto aplicado com valor maior que 0
                $descontosAplicados = Session::get('descontos_aplicados', []);
                $temDescontoAplicado = false;
                $valorCrediario = 0;
                
                if (!empty($descontosAplicados)) {
                    foreach (['avista', 'pix', 'cartao', 'crediario'] as $tipo) {
                        if (isset($descontosAplicados[$tipo])) {
                            $valor = floatval(str_replace(',', '.', str_replace('.', '', $descontosAplicados[$tipo])));
                            if ($valor > 0) {
                                $temDescontoAplicado = true;
                                if ($tipo === 'crediario') {
                                    $valorCrediario = $valor;
                                }
                            }
                        }
                    }
                }
            @endphp
            
            <!-- Botões de Finalização -->
            @if($temDescontoAplicado)
                <div class="flex flex-col sm:flex-row gap-3">
                    <form id="finalize-form" action="{{ route('carrinho.finalizarCompra') }}" method="POST" class="inline">
                        @csrf
                        <button type="button" id="finalize-button" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 shadow-md hover:shadow-lg w-full sm:w-auto">
                            <i class="fas fa-check-circle mr-2"></i>Finalizar Compra
                        </button>
                    </form>
                    <a href="{{ route('carrinho.venda-crediario') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 shadow-md hover:shadow-lg text-center">
                        <i class="fas fa-credit-card mr-2"></i>Venda Crediário
                    </a>
                </div>
            @else
                <div class="bg-yellow-100 border border-yellow-300 rounded-lg p-4 text-center">
                    <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                    <span class="text-yellow-700 font-medium">Configure o pagamento para finalizar a compra</span>
                </div>
            @endif
        </div>
    @else
        <!-- Carrinho Vazio -->
        <div class="text-center py-16">
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl border-2 border-yellow-300 p-8 max-w-md mx-auto">
                <div class="mb-6">
                    <i class="fas fa-shopping-cart text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-2xl font-bold text-red-600 mb-2">Carrinho Vazio</h3>
                    <p class="text-gray-600">Nenhum item foi adicionado ao carrinho ainda.</p>
                </div>
                
                <div class="space-y-3">
                    <a href="{{ route('produtos.procurar') }}" class="btn-yellow w-full inline-block">
                        <i class="fas fa-search mr-2"></i>Buscar Produtos
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn-red w-full inline-block">
                        <i class="fas fa-home mr-2"></i>Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const valorDinheiroInput = document.getElementById('valor_dinheiro');
    const paymentFields = document.querySelectorAll('.payment-field');
    
    // Função para aplicar máscara monetária
    function aplicarMascaraMonetaria(valor) {
        // Remove tudo que não é dígito
        valor = valor.replace(/\D/g, '');
        
        // Converte para centavos
        valor = (valor / 100).toFixed(2) + '';
        
        // Aplica a formatação brasileira
        valor = valor.replace(".", ",");
        valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        
        return valor;
    }
    
    // Função para aplicar máscara monetária simples (sem R$)
    function aplicarMascaraSimples(valor) {
        // Remove tudo que não é dígito
        valor = valor.replace(/\D/g, '');
        
        // Se vazio, retorna 0,00
        if (valor === '') return '0,00';
        
        // Converte para centavos
        valor = (valor / 100).toFixed(2) + '';
        
        // Aplica a formatação brasileira
        valor = valor.replace(".", ",");
        
        return valor;
    }
    
    // Função para remover máscara e obter valor numérico
    function removerMascara(valor) {
        return valor.replace(/[^\d,]/g, '').replace(',', '.');
    }
    
    // Configura máscara para o campo de dinheiro recebido
    if (valorDinheiroInput) {
        valorDinheiroInput.addEventListener('input', function(e) {
            let valor = e.target.value;
            valor = valor.replace(/[^\d]/g, '');
            e.target.value = 'R$ ' + aplicarMascaraMonetaria(valor);
        });
        
        // Aplica máscara ao campo se já tiver valor
        if (valorDinheiroInput.value && valorDinheiroInput.value !== '') {
            let valorAtual = valorDinheiroInput.value.replace(/[^\d,]/g, '');
            if (valorAtual) {
                valorAtual = valorAtual.replace(',', '').replace(/\D/g, '');
                valorDinheiroInput.value = 'R$ ' + aplicarMascaraMonetaria(valorAtual);
            }
        }
    }
    
    // Configura máscara para os campos de pagamento
    paymentFields.forEach(function(field) {
        field.addEventListener('input', function(e) {
            if (!e.target.readOnly) {
                let valor = e.target.value;
                valor = valor.replace(/[^\d]/g, '');
                e.target.value = aplicarMascaraSimples(valor);
                
                // Atualiza o campo hidden correspondente
                const hiddenField = document.getElementById(e.target.id + '_manual');
                if (hiddenField) {
                    hiddenField.value = e.target.value;
                }
            }
        });
    });
    
    // Inicializa o modo baseado na seleção atual
    toggleManualMode();
    
    // Remove máscara antes de enviar o formulário
    const form = document.querySelector('form[action*="aplicarDesconto"]');
    if (form) {
        form.addEventListener('submit', function() {
            if (valorDinheiroInput) {
                let valor = valorDinheiroInput.value;
                valor = valor.replace('R$ ', '').replace(/\./g, '');
                valorDinheiroInput.value = valor;
            }
            
            // Atualiza campos hidden com valores dos campos visíveis
            paymentFields.forEach(function(field) {
                const hiddenField = document.getElementById(field.id + '_manual');
                if (hiddenField) {
                    hiddenField.value = field.value;
                }
            });
        });
    }
});

function toggleManualMode() {
    const tipoDesconto = document.getElementById('tipo_desconto').value;
    const paymentFields = document.querySelectorAll('.payment-field');
    const isManual = tipoDesconto === 'manual';
    
    paymentFields.forEach(function(field) {
        if (isManual) {
            field.readOnly = false;
            field.classList.remove('bg-gray-100');
            field.classList.add('bg-white');
            field.style.cursor = 'text';
            
            // Se estiver mudando para manual e o campo estiver zerado, limpa para facilitar a digitação
            if (field.value === '0,00') {
                field.value = '';
            }
        } else {
            field.readOnly = true;
            field.classList.remove('bg-white');
            field.classList.add('bg-gray-100');
            field.style.cursor = 'not-allowed';
        }
    });
    
    // Se não for manual, zera os campos hidden
    if (!isManual) {
        document.querySelectorAll('input[name$="_manual"]').forEach(function(hiddenField) {
            hiddenField.value = '0,00';
        });
    }
}

// SweetAlert2 para confirmação de finalização de compra
document.addEventListener('DOMContentLoaded', function() {
    const finalizeButton = document.getElementById('finalize-button');
    const finalizeForm = document.getElementById('finalize-form');
    
    if (finalizeButton && finalizeForm) {
        finalizeButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Calcular informações da compra para exibir no SweetAlert
            const totalElement = document.querySelector('.text-2xl.font-bold.text-red-600');
            const totalValue = totalElement ? totalElement.textContent.trim() : 'R$ 0,00';
            
            // Contar itens no carrinho
            const cartItems = document.querySelectorAll('.table-row').length;
            
            JJAlert.finalizarCompra(cartItems, totalValue)tons: true,
                focusCancel: true,
                customClass: {
                    popup: 'swal2-popup-custom',
                    title: 'swal2-title-custom',
                    confirmButton: 'swal2-confirm-custom',
                    cancelButton: 'swal2-cancel-custom'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Processando...',
                        html: '<i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i><br><br>Finalizando sua compra, aguarde...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'swal2-popup-custom'
                        }
                    });
                    
                    // Submeter o formulário
                    finalizeForm.submit();
                }
            });
        });
    }
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
    background-color: #16a34a !important;
    color: white !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
}

.swal2-confirm-custom:hover {
    background-color: #15803d !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4) !important;
}

.swal2-cancel-custom {
    background-color: #dc2626 !important;
    color: white !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    margin-right: 10px !important;
}

.swal2-cancel-custom:hover {
    background-color: #b91c1c !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4) !important;
}
</style>
