<!DOCTYPE html>
@extends('layouts.base')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center mb-6">
                @if ($cliente->pasta == null)
                    @php
                        $pasta = $cliente->cpf;
                    @endphp
                @else
                    @php
                        $pasta = $cliente->pasta;
                    @endphp
                @endif
               <img class="h-60 max-w-xs img-preview" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $cliente->foto) }}" alt="{{ $cliente->name }}">
                <h1 class="text-3xl font-bold text-gray-800 flex-grow text-center">{{ $cliente->nome }}</h1>
            </div>

            @if(session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            html: '{{ session('error') }}',
                            confirmButtonText: 'OK'
                        });
                    });
                </script>
            @endif

            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            html: '{{ session('success') }}',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Redirecionar para a mesma página para atualizar os dados
                                window.location.href = window.location.href;
                            }
                        });
                    });
                </script>
            @endif

            <!-- Botões de Seleção -->
            <div class="mb-6 flex gap-4 justify-center">
                <button type="button" id="btn-selecionar-todas" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-check-square mr-2"></i>Selecionar Todas
                </button>
                <button type="button" id="btn-desselecionar-todas" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-square mr-2"></i>Desselecionar Todas
                </button>
            </div>

            <form action="{{ route('pagamentos.store', $cliente) }}" method="POST" id="payment-form">
                @csrf
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Parcelas do Titular</h2>
                    @include('pagamentos._tabela_parcelas', ['parcelas' => $parcelasTitular])
                </div>

                @if($parcelasAutorizados->isNotEmpty())
                    <div class="mb-6">
                        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Parcelas de Autorizados</h2>
                        @foreach($parcelasAutorizados as $autorizadoId => $parcelasDoAutorizado)
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">{{ $parcelasDoAutorizado->first()->autorizado->nome }}</h3>
                            @include('pagamentos._tabela_parcelas', ['parcelas' => $parcelasDoAutorizado])
                        @endforeach
                    </div>
                @endif

                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                    <div class="flex justify-between items-center mb-4">
                    <div>
                        <span class="text-xl font-bold text-gray-800">Total Selecionado:</span>
                        <span class="text-sm text-gray-600 ml-2">(<span id="selected-count">0</span> parcelas)</span>
                    </div>
                    <span id="total-selecionado" class="text-2xl font-bold text-blue-600">R$ 0,00</span>
                </div>
                <div class="mb-4 text-sm text-gray-600">
                    <p>Obs: O valor total inclui juros e multa para parcelas vencidas.</p>
                </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="dinheiro" class="block text-sm font-medium text-gray-700">Dinheiro</label>
                            <input type="text" name="dinheiro" id="dinheiro" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm money">
                        </div>
                        <div>
                            <label for="pix" class="block text-sm font-medium text-gray-700">PIX</label>
                            <input type="text" name="pix" id="pix" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm money">
                        </div>
                        <div>
                            <label for="cartao" class="block text-sm font-medium text-gray-700">Cartão</label>
                            <input type="text" name="cartao" id="cartao" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm money">
                        </div>
                    </div>

                    <button type="submit" id="btn-pagar" class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-gray-400" disabled>Realizar Pagamento</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $('.money').mask('000.000.000.000.000,00', {reverse: true});

            function parseCurrency(value) {
                return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
            }

            function formatCurrency(value) {
                return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            }

            function updateTotal() {
                let total = 0;
                let selectedCount = 0;
                $('.parcela-checkbox:checked').each(function(){
                    total += parseFloat($(this).data('valor'));
                    selectedCount++;
                });
                $('#total-selecionado').text(formatCurrency(total));
                $('#selected-count').text(selectedCount);
                checkPaymentAbility();
            }

            function checkPaymentAbility() {
                const totalSelecionado = parseFloat($('#total-selecionado').text().replace(/[^0-9,-]+/g, '').replace(',', '.'));
                const dinheiro = parseCurrency($('#dinheiro').val());
                const pix = parseCurrency($('#pix').val());
                const cartao = parseCurrency($('#cartao').val());
                const totalPago = dinheiro + pix + cartao;

                if (totalSelecionado > 0 && Math.abs(totalSelecionado - totalPago) < 0.01) {
                    $('#btn-pagar').prop('disabled', false);
                } else {
                    $('#btn-pagar').prop('disabled', true);
                }
            }

            function clearPayments() {
                $('#dinheiro, #pix, #cartao').val('');
                checkPaymentAbility();
            }

            $('.parcela-checkbox').on('change', updateTotal);
            $('#dinheiro, #pix, #cartao').on('input', checkPaymentAbility);

            // Botões de seleção
            $('#btn-selecionar-todas').on('click', function() {
                $('.parcela-checkbox').prop('checked', true);
                updateTotal();
            });

            $('#btn-desselecionar-todas').on('click', function() {
                $('.parcela-checkbox').prop('checked', false);
                updateTotal();
                clearPayments();
            });
            
            // Intercept form submission to show confirmation
            $('#payment-form').on('submit', function(e) {
                e.preventDefault();
                
                const dinheiro = parseCurrency($('#dinheiro').val());
                const pix = parseCurrency($('#pix').val());
                const cartao = parseCurrency($('#cartao').val());
                const total = dinheiro + pix + cartao;
                
                let metodo = 'Dinheiro';
                if (pix > 0 && cartao > 0) {
                    metodo = 'PIX e Cartão';
                } else if (pix > 0) {
                    metodo = 'PIX';
                } else if (cartao > 0) {
                    metodo = 'Cartão';
                }
                
                Swal.fire({
                    title: 'Confirmar Pagamento',
                    html: `
                        <p><strong>Total a pagar:</strong> ${formatCurrency(total)}</p>
                        <p><strong>Método de pagamento:</strong> ${metodo}</p>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the form
                        $('#payment-form')[0].submit();
                    }
                });
            });
            
            // Debug helper
            window.debugPayment = function() {
                let total = 0;
                let parcels = [];
                $('.parcela-checkbox:checked').each(function(){
                    total += parseFloat($(this).data('valor'));
                    parcels.push({
                        id: $(this).val(),
                        valor: $(this).data('valor')
                    });
                });
                console.log('Parcelas selecionadas:', parcels);
                console.log('Total:', total);
                return { parcels, total };
            };
        });
    </script>
@endsection
