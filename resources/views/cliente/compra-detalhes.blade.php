@extends('layouts.base')
@section('content')

<div class="content">
    <!-- Header com informações da compra -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Detalhes da Compra</h1>
                <nav class="text-sm text-gray-600">
                    <a href="{{ route('clientes.index') }}" class="hover:text-blue-600">Clientes</a>
                    <span class="mx-2">></span>
                    <a href="{{ route('clientes.historico.compras', $cliente->id) }}" class="hover:text-blue-600">Histórico</a>
                    <span class="mx-2">></span>
                    <span class="text-gray-800">{{ $ticketData->ticket }}</span>
                </nav>
            </div>
            <div class="text-right">
                <a href="{{ route('clientes.historico.compras', $cliente->id) }}" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar ao Histórico
                </a>
            </div>
        </div>

        <!-- Informações da Compra -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Cliente</h3>
                <p class="text-gray-800">{{ $cliente->nome }}</p>
                <p class="text-sm text-gray-600">{{ $cliente->cpf }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Ticket</h3>
                <p class="text-lg font-bold text-gray-800">{{ $ticketData->ticket }}</p>
                <p class="text-sm text-gray-600">{{ $ticketData->data_formatada }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Valor Total</h3>
                <p class="text-xl font-bold text-green-600">{{ $ticketData->valor_formatado }}</p>
                <p class="text-sm text-gray-600">Entrada: {{ $ticketData->entrada_formatada }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Parcelas</h3>
                <p class="text-lg font-bold text-blue-600">{{ $ticketData->parcelas }}x</p>
                <p class="text-sm text-gray-600">Financiado: R$ {{ number_format($ticketData->valor_financiado, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <x-alert />

    <!-- Produtos da Compra -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Produtos da Compra</h2>
        
        @if ($produtos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Produto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantidade
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Valor Unitário
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($produtos as $produto)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($produto->imagem)
                                                <img class="h-10 w-10 rounded-full object-cover" 
                                                     src="{{ asset('storage/uploads/produtos/' . $produto->imagem) }}" 
                                                     alt="{{ $produto->nome }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-box text-gray-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $produto->nome }}
                                                @if(!$produto->produto_encontrado)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                                                        Produto não encontrado
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Código: {{ $produto->codigo }} | Numeração: {{ $produto->numeracao }}
                                            </div>
                                            @if($produto->marca !== 'N/A')
                                                <div class="text-xs text-gray-400">
                                                    {{ $produto->marca }} - {{ $produto->grupo }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $produto->quantidade }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    R$ {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                    @if($produto->desconto > 0)
                                        <div class="text-xs text-green-600">
                                            Desconto: R$ {{ number_format($produto->desconto, 2, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    R$ {{ number_format($produto->valor_total, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-box-open text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Produtos não disponíveis</h3>
                <p class="text-gray-500">Os detalhes dos produtos desta compra não estão disponíveis no momento.</p>
            </div>
        @endif
    </div>

    <!-- Parcelas da Compra -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Parcelas</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($ticketData->parcelasRelacao as $parcela)
                <div class="border rounded-lg p-4 {{ $parcela->status_color }}">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-800">Parcela {{ $parcela->numero }}</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                            {{ $parcela->status_texto }}
                        </span>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Vencimento:</span>
                            <span class="font-medium">{{ $parcela->vencimento_formatado }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Valor:</span>
                            <span class="font-medium">{{ $parcela->valor_formatado }}</span>
                        </div>
                        @if ($parcela->data_pagamento)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pagamento:</span>
                                <span class="font-medium">{{ $parcela->pagamento_formatado }}</span>
                            </div>
                        @endif
                        @if ($parcela->isPaga() && $parcela->dias_atraso_ou_antecipacao !== null)
                            <div class="flex justify-between">
                                <span class="text-gray-600">
                                    @if ($parcela->dias_atraso_ou_antecipacao < 0)
                                        Atraso:
                                    @elseif ($parcela->dias_atraso_ou_antecipacao > 0)
                                        Antecipação:
                                    @else
                                        Situação:
                                    @endif
                                </span>
                                <span class="font-medium">
                                    @if ($parcela->dias_atraso_ou_antecipacao < 0)
                                        {{ abs($parcela->dias_atraso_ou_antecipacao) }} dias
                                    @elseif ($parcela->dias_atraso_ou_antecipacao > 0)
                                        {{ $parcela->dias_atraso_ou_antecipacao }} dias
                                    @else
                                        No prazo
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Ações</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Duplicata -->
            <div class="text-center">
                <a href="{{ route('clientes.duplicata', [$cliente->id, $ticketData->ticket]) }}" 
                   class="btn-blue w-full inline-flex items-center justify-center">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Gerar Duplicata
                </a>
                <p class="text-xs text-gray-500 mt-2">Documento oficial da compra</p>
            </div>

            <!-- Carnê de Pagamento -->
            <div class="text-center">
                <a href="{{ route('clientes.carne', [$cliente->id, $ticketData->ticket]) }}" 
                   class="btn-green w-full inline-flex items-center justify-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Carnê de Pagamento
                </a>
                <p class="text-xs text-gray-500 mt-2">Carnê com todas as parcelas</p>
            </div>

            <!-- Mensagem de Aviso -->
            <div class="text-center">
                <button onclick="openMessageModal()" 
                        class="btn-yellow w-full inline-flex items-center justify-center">
                    <i class="fas fa-envelope mr-2"></i>
                    Mensagem de Aviso
                </button>
                <p class="text-xs text-gray-500 mt-2">Enviar lembrete ao cliente</p>
            </div>

            <!-- Devolução -->
            <div class="text-center">
                @if($ticketData->canBeReturned())
                    <button onclick="confirmarDevolucao('{{ $ticketData->ticket }}', '{{ $ticketData->valor_formatado }}', {{ $ticketData->parcelas }})" 
                            class="btn-red w-full inline-flex items-center justify-center">
                        <i class="fas fa-undo mr-2"></i>
                        Processar Devolução
                    </button>
                    <p class="text-xs text-gray-500 mt-2">Devolver compra completa</p>
                @else
                    <button class="btn-gray w-full inline-flex items-center justify-center cursor-not-allowed" 
                            disabled 
                            title="Não é possível devolver esta compra pois há parcelas pagas ou já foi devolvida">
                        <i class="fas fa-undo mr-2"></i>
                        Processar Devolução
                    </button>
                    <p class="text-xs text-gray-500 mt-2">Devolução não disponível</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal para Mensagem -->
<div id="messageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Enviar Mensagem de Aviso</h3>
            
            <form action="{{ route('clientes.mensagem', [$cliente->id, $ticketData->ticket]) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Mensagem</label>
                    <select name="tipo" id="tipo" class="form-select w-full" required onchange="updateMessagePlaceholder()"> 
                        <option value="whatsapp">WhatsApp (abre em nova aba)</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="mensagem" class="block text-sm font-medium text-gray-700 mb-2">Mensagem</label>
                    <textarea name="mensagem" id="mensagem" rows="4" 
                              class="form-textarea w-full" 
                              placeholder="Digite sua mensagem aqui ou deixe em branco para usar a mensagem padrão..." 
                              maxlength="500">Joécio calçados informa: Olá {{ $cliente->nome }}, compra realizada no valor de {{ $ticketData->valor_formatado }}. Acompanhe suas parcelas através do link: {{ url('/parcelas/') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Máximo 500 caracteres. Para WhatsApp, uma mensagem padrão será usada se este campo estiver vazio.</p>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeMessageModal()" 
                            class="btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-green">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openMessageModal() {
    document.getElementById('messageModal').classList.remove('hidden');
}

function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.getElementById('mensagem').value = '';
    document.getElementById('tipo').value = '';
}

// Fechar modal ao clicar fora
document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMessageModal();
    }
});

// Verificar se há URL do WhatsApp para abrir
@if(session('whatsapp_url'))
    window.open('{{ session('whatsapp_url') }}', '_blank');
@endif

// Atualizar placeholder baseado no tipo selecionado
function updateMessagePlaceholder() {
    const tipo = document.getElementById('tipo').value;
    const mensagem = document.getElementById('mensagem');
    
    if (tipo === 'whatsapp') {
        mensagem.placeholder = 'Mensagem padrão será usada automaticamente para WhatsApp. Você pode personalizar aqui se desejar.';
    } else {
        mensagem.placeholder = 'Digite sua mensagem aqui...';
    }
}

// Função para confirmar devolução
function confirmarDevolucao(ticket, valor, parcelas) {
    // Mostrar loading enquanto busca informações
    Swal.fire({
        title: 'Carregando informações...',
        text: 'Por favor, aguarde',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Buscar informações do operador e vendedor
    fetch(`/clientes/{{ $cliente->id }}/venda-info/${ticket}`)
        .then(response => response.json())
        .then(data => {
            const operadorInfo = data.success ? data.operador_caixa : 'Não identificado';
            const vendedorInfo = data.success ? data.vendedor_atendente : 'Não identificado';

            Swal.fire({
                title: 'Confirmar Devolução',
                html: `
                    <div class="text-left">
                        <p><strong>Ticket:</strong> ${ticket}</p>
                        <p><strong>Valor Total:</strong> ${valor}</p>
                        <p><strong>Parcelas:</strong> ${parcelas}</p>
                        <br>
                        <p><strong>Operador de Caixa:</strong> ${operadorInfo}</p>
                        <p><strong>Vendedor Atendente:</strong> ${vendedorInfo}</p>
                        <br>
                        <p class="text-red-600">⚠️ Esta ação não pode ser desfeita!</p>
                        <p>Tem certeza que deseja processar a devolução desta compra?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sim, devolver',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    processarDevolucao(ticket);
                }
            });
        })
        .catch(error => {
            console.error('Erro ao buscar informações da venda:', error);
            
            // Exibir modal mesmo com erro
            Swal.fire({
                title: 'Confirmar Devolução',
                html: `
                    <div class="text-left">
                        <p><strong>Ticket:</strong> ${ticket}</p>
                        <p><strong>Valor Total:</strong> ${valor}</p>
                        <p><strong>Parcelas:</strong> ${parcelas}</p>
                        <br>
                        <p><strong>Operador de Caixa:</strong> Erro ao carregar</p>
                        <p><strong>Vendedor Atendente:</strong> Erro ao carregar</p>
                        <br>
                        <p class="text-red-600">⚠️ Esta ação não pode ser desfeita!</p>
                        <p>Tem certeza que deseja processar a devolução desta compra?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sim, devolver',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    processarDevolucao(ticket);
                }
            });
        });
}

// Função para processar devolução
function processarDevolucao(ticket) {
    // Mostrar loading
    Swal.fire({
        title: 'Processando devolução...',
        text: 'Por favor, aguarde',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fazer requisição AJAX
    const url = `/clientes/{{ $cliente->id }}/devolucao/${ticket}`;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    console.log('URL:', url);
    console.log('CSRF Token:', csrfToken);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            Swal.fire({
                title: 'Sucesso!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#059669'
            }).then(() => {
                // Redirecionar para o histórico de compras
                window.location.href = '{{ route("clientes.historico.compras", $cliente->id) }}';
            });
        } else {
            Swal.fire({
                title: 'Erro!',
                text: data.message,
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
        }
    })
    .catch(error => {
        console.error('Erro completo:', error);
        console.error('Tipo do erro:', typeof error);
        console.error('Stack trace:', error.stack);
        
        Swal.fire({
            title: 'Erro!',
            text: 'Erro interno do servidor. Tente novamente. Detalhes: ' + error.message,
            icon: 'error',
            confirmButtonColor: '#dc2626'
        });
    });
}
</script>

@endsection