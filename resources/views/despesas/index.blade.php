@extends('layouts.base')

@section('content')
<style>
@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto;
    }
    
    .table-responsive table {
        min-width: 600px;
    }
    
    .table-responsive th,
    .table-responsive td {
        white-space: nowrap;
    }
}
</style>

<div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Verificação de Despesas</h1>
    
    <!-- Filtros de data -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <form method="GET" action="{{ route('despesas.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="data_inicial" class="block text-sm font-medium text-gray-700">Data Inicial</label>
                <input type="date" name="data_inicial" id="data_inicial" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ $dataInicial }}">
            </div>
            <div>
                <label for="data_final" class="block text-sm font-medium text-gray-700">Data Final</label>
                <input type="date" name="data_final" id="data_final" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ $dataFinal }}">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                    Filtrar
                </button>
            </div>
        </form>
    </div>
    
    <!-- Para cada cidade, mostrar despesas fixas, despesas normais e relatório -->
    @foreach($cidades as $cidade)
        @php
            $cidadeId = $cidade->id;
        @endphp
        
        <!-- Despesas Fixas (apenas quando o intervalo de datas corresponde ao mês atual) -->
        @if($isMesAtual && isset($despesasFixas[$cidadeId]) && count($despesasFixas[$cidadeId]) > 0)
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Despesas Fixas - {{ $cidade->cidade }}</h2>
            <div class="table-responsive">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dia</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($despesasFixas[$cidadeId] as $despesa)
                        <tr class="{{ $despesa->ja_inserida ? 'bg-green-100' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $despesa->dia }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $despesa->tipo }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $despesa->empresa }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">R$ {{ number_format($despesa->valor, 2, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($despesa->ja_inserida)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Já Inserida
                                    </span>
                                @else
                                    <button type="button" class="inserir-btn bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded" data-id="{{ $despesa->id_despesas }}" data-cidade="{{ $cidadeId }}">
                                        Inserir
                                    </button>
                                @endif
                                
                                <button type="button" class="editar-fixa-btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded mt-1" data-id="{{ $despesa->id_despesas }}" data-cidade="{{ $cidadeId }}" data-dia="{{ $despesa->dia }}" data-tipo="{{ $despesa->tipo }}" data-empresa="{{ $despesa->empresa }}" data-valor="{{ number_format($despesa->valor, 2, ',', '.') }}">
                                    Editar
                                </button>
                                
                                @if($isAdmin)
                                <button type="button" class="excluir-fixa-btn bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded mt-1" data-id="{{ $despesa->id_despesas }}" data-cidade="{{ $cidadeId }}">
                                    Excluir
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        
        <!-- Despesas Normais -->
        @if(isset($despesasNormais[$cidadeId]) && count($despesasNormais[$cidadeId]) > 0)
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Despesas - {{ $cidade->cidade }}</h2>
            <div class="table-responsive">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($despesasNormais[$cidadeId] as $despesa)
                        @php
                            // Verificar se é uma despesa fixa futura
                            $isDespesaFixaFutura = isset($despesa->is_fixa_futura) && $despesa->is_fixa_futura;
                            
                            // Determinar a classe de background
                            $classeBg = '';
                            if ($isDespesaFixaFutura) {
                                $classeBg = 'bg-blue-50';
                            } elseif (isset($despesa->status) && $despesa->status === 'Pago') {
                                $classeBg = 'bg-green-100';
                            } elseif (isset($despesa->data) && $despesa->data->isToday()) {
                                $classeBg = 'bg-yellow-100';
                            } elseif (isset($despesa->data) && $despesa->data->isPast() && (isset($despesa->status) && ($despesa->status === 'pendente' || is_null($despesa->status)))) {
                                $classeBg = 'bg-red-100';
                            }
                        @endphp
                        <tr class="{{ $classeBg }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ isset($despesa->data) ? $despesa->data->format('d/m/Y') : '' }}</div>
                                @if(isset($despesa->pagamento) && $despesa->pagamento)
                                <div class="text-xs text-gray-500">Método: {{ $despesa->pagamento }}</div>
                                @elseif($isDespesaFixaFutura)
                                <div class="text-xs text-gray-500">Mês: {{ $despesa->mes_referencia }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $despesa->tipo }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $despesa->empresa }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">R$ {{ number_format($despesa->valor, 2, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($isDespesaFixaFutura)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Despesa Fixa
                                    </span>
                                @elseif(isset($despesa->status) && $despesa->status === 'Pago')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Pago
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Pendente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($isDespesaFixaFutura)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Fixa Futura
                                    </span>
                                @else
                                    <button type="button" class="editar-btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded" data-id="{{ $despesa->id_despesas }}" data-cidade="{{ $cidadeId }}" data-tipo="{{ $despesa->tipo }}" data-empresa="{{ $despesa->empresa }}" data-valor="{{ number_format($despesa->valor, 2, ',', '.') }}" data-status="{{ $despesa->status ?? '' }}" data-pagamento="{{ $despesa->pagamento ?? '' }}">
                                        Editar
                                    </button>
                                    @if($isAdmin)
                                    <button type="button" class="excluir-btn bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded mt-1" data-id="{{ $despesa->id_despesas }}" data-cidade="{{ $cidadeId }}">
                                        Excluir
                                    </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        
        <!-- Relatório da Cidade -->
        @if(isset($relatorios[$cidadeId]))
        <div class="mb-8 p-4 bg-blue-50 rounded-lg">
            <h2 class="text-lg font-semibold mb-2">Relatório - {{ $cidade->cidade }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-3 bg-white rounded shadow">
                    <p class="text-sm text-gray-600">Total de Despesas</p>
                    <p class="text-xl font-bold text-blue-600">R$ {{ number_format($relatorios[$cidadeId]['total_despesas'], 2, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-white rounded shadow">
                    <p class="text-sm text-gray-600">Valor Pago</p>
                    <p class="text-xl font-bold text-green-600">R$ {{ number_format($relatorios[$cidadeId]['total_pago'], 2, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-white rounded shadow">
                    <p class="text-sm text-gray-600">Valor a Pagar</p>
                    <p class="text-xl font-bold text-red-600">R$ {{ number_format($relatorios[$cidadeId]['total_a_pagar'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        @endif
    @endforeach
    
    <!-- Relatório Geral (apenas para administrador) -->
    @if($isAdmin && $relatorioGeral)
    <div class="mb-6 p-4 bg-purple-50 rounded-lg">
        <h2 class="text-lg font-semibold mb-2">Relatório Geral</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-3 bg-white rounded shadow">
                <p class="text-sm text-gray-600">Total de Despesas</p>
                <p class="text-xl font-bold text-blue-600">R$ {{ number_format($relatorioGeral['total_despesas'], 2, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-white rounded shadow">
                <p class="text-sm text-gray-600">Valor Pago</p>
                <p class="text-xl font-bold text-green-600">R$ {{ number_format($relatorioGeral['total_pago'], 2, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-white rounded shadow">
                <p class="text-sm text-gray-600">Valor a Pagar</p>
                <p class="text-xl font-bold text-red-600">R$ {{ number_format($relatorioGeral['total_a_pagar'], 2, ',', '.') }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal para edição de despesa -->
<div id="editarModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Despesa</h3>
            <form id="editarForm">
                @csrf
                <input type="hidden" id="editar_id_despesa" name="id_despesa">
                <input type="hidden" id="editar_cidade_id" name="cidade_id">
                
                <div class="mb-4">
                    <label for="editar_tipo" class="block text-sm font-medium text-gray-700">Tipo de Despesa</label>
                    <input type="text" id="editar_tipo" name="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                
                <div class="mb-4">
                    <label for="editar_empresa" class="block text-sm font-medium text-gray-700">Empresa</label>
                    <input type="text" id="editar_empresa" name="empresa" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                
                <div class="mb-4">
                    <label for="editar_valor" class="block text-sm font-medium text-gray-700">Valor</label>
                    <input type="text" id="editar_valor" name="valor" class="valor-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                
                <div class="mb-4">
                    <label for="editar_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="editar_status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="pendente">Pendente</option>
                        <option value="Pago">Pago</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="editar_pagamento" class="block text-sm font-medium text-gray-700">Método de Pagamento</label>
                    <input type="text" id="editar_pagamento" name="pagamento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                
                <div class="flex justify-end">
                    <button type="button" id="cancelarEdicao" class="mr-2 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Atualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para edição de despesa fixa -->
<div id="editarFixaModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Despesa Fixa</h3>
            <form id="editarFixaForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="editar_fixa_id" name="id">
                <input type="hidden" id="editar_fixa_cidade_id" name="cidade_id">
                
                <div class="mb-4">
                    <label for="editar_fixa_dia" class="block text-sm font-medium text-gray-700">Dia</label>
                    <select id="editar_fixa_dia" name="dia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        @for($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="editar_fixa_tipo" class="block text-sm font-medium text-gray-700">Tipo de Despesa</label>
                    <input type="text" id="editar_fixa_tipo" name="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                
                <div class="mb-4">
                    <label for="editar_fixa_empresa" class="block text-sm font-medium text-gray-700">Empresa</label>
                    <input type="text" id="editar_fixa_empresa" name="empresa" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                
                <div class="mb-4">
                    <label for="editar_fixa_valor" class="block text-sm font-medium text-gray-700">Valor</label>
                    <input type="text" id="editar_fixa_valor" name="valor" class="valor-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" id="cancelarEdicaoFixa" class="mr-2 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Atualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Função para aplicar máscara monetária
function aplicarMascaraMonetaria(input) {
    // Remove qualquer formatação existente
    let valor = input.value.replace(/\D/g, '');
    
    // Converte para número e formata como moeda
    valor = (valor / 100).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2
    });
    
    input.value = valor;
}

// Função para remover a máscara antes de enviar o formulário
function removerMascaraMonetaria(valor) {
    // Se o valor já estiver no formato de moeda brasileira
    if (valor.includes('R$')) {
        // Remove o "R$" e espaços
        valor = valor.replace('R$', '').trim();
        // Substitui o separador de milhar (ponto) por vazio
        valor = valor.replace(/\./g, '');
        // Substitui a vírgula por ponto para formato decimal
        valor = valor.replace(',', '.');
    }
    
    // Converte para número float
    return parseFloat(valor) || 0;
}

// Função para abrir o modal de edição de despesa fixa
function initEditarFixaButtons() {
    document.querySelectorAll('.editar-fixa-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const cidadeId = this.getAttribute('data-cidade');
            const dia = this.getAttribute('data-dia');
            const tipo = this.getAttribute('data-tipo');
            const empresa = this.getAttribute('data-empresa');
            const valor = this.getAttribute('data-valor');
            
            document.getElementById('editar_fixa_id').value = id;
            document.getElementById('editar_fixa_cidade_id').value = cidadeId;
            document.getElementById('editar_fixa_dia').value = dia;
            document.getElementById('editar_fixa_tipo').value = tipo;
            document.getElementById('editar_fixa_empresa').value = empresa;
            document.getElementById('editar_fixa_valor').value = valor;
            
            // Aplicar máscara monetária ao valor
            aplicarMascaraMonetaria(document.getElementById('editar_fixa_valor'));
            
            document.getElementById('editarFixaModal').classList.remove('hidden');
        });
    });
}

// Função para abrir o modal de edição
function initEditarButtons() {
    document.querySelectorAll('.editar-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const cidadeId = this.getAttribute('data-cidade');
            const tipo = this.getAttribute('data-tipo');
            const empresa = this.getAttribute('data-empresa');
            const valor = this.getAttribute('data-valor');
            const status = this.getAttribute('data-status');
            const pagamento = this.getAttribute('data-pagamento');
            
            document.getElementById('editar_id_despesa').value = id;
            document.getElementById('editar_cidade_id').value = cidadeId;
            document.getElementById('editar_tipo').value = tipo;
            document.getElementById('editar_empresa').value = empresa;
            document.getElementById('editar_valor').value = valor;
            document.getElementById('editar_status').value = status;
            document.getElementById('editar_pagamento').value = pagamento;
            
            // Aplicar máscara monetária ao valor
            aplicarMascaraMonetaria(document.getElementById('editar_valor'));
            
            document.getElementById('editarModal').classList.remove('hidden');
        });
    });
}

// Função para fechar o modal de edição
function initCancelarEdicao() {
    document.getElementById('cancelarEdicao').addEventListener('click', function() {
        document.getElementById('editarModal').classList.add('hidden');
    });
}

// Função para atualizar despesa
function initEditarForm() {
    document.getElementById('editarForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Remover máscara do valor antes de enviar
        const valorInput = document.getElementById('editar_valor');
        formData.set('valor', removerMascaraMonetaria(valorInput.value));
        
        fetch("{{ route('despesas.atualizar') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Sucesso!',
                    text: 'Despesa atualizada com sucesso!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            }
        });
    });
}

// Função para excluir despesa
function initExcluirButtons() {
    document.querySelectorAll('.excluir-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const cidadeId = this.getAttribute('data-cidade');
            
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id_despesa', id);
                    formData.append('cidade_id', cidadeId);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    
                    fetch("{{ route('despesas.excluir') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Excluído!',
                                text: 'A despesa foi excluída com sucesso.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    });
                }
            });
        });
    });
}

// Função para inserir despesa fixa
function initInserirButtons() {
    document.querySelectorAll('.inserir-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const cidadeId = this.getAttribute('data-cidade');
            const data = new Date().toISOString().split('T')[0]; // Data atual
            
            const formData = new FormData();
            formData.append('id_despesa_fixa', id);
            formData.append('data', data);
            formData.append('cidade_id', cidadeId);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            fetch("{{ route('despesas.inserir.fixa') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: 'Despesa fixa inserida com sucesso!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        });
    });
}

// Função para fechar o modal de edição de despesa fixa
function initCancelarEdicaoFixa() {
    document.getElementById('cancelarEdicaoFixa').addEventListener('click', function() {
        document.getElementById('editarFixaModal').classList.add('hidden');
    });
}

// Função para atualizar despesa fixa
function initEditarFixaForm() {
    document.getElementById('editarFixaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('editar_fixa_id').value;
        const formData = new FormData(this);
        
        // Remover máscara do valor antes de enviar
        const valorInput = document.getElementById('editar_fixa_valor');
        formData.set('valor', removerMascaraMonetaria(valorInput.value));
        
        fetch(`/despesas/fixa/${id}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'PUT'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Sucesso!',
                    text: 'Despesa fixa atualizada com sucesso!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            }
        });
    });
}

// Função para excluir despesa fixa
function initExcluirFixaButtons() {
    document.querySelectorAll('.excluir-fixa-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const cidadeId = this.getAttribute('data-cidade');
            
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('cidade_id', cidadeId);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    formData.append('_method', 'DELETE');
                    
                    fetch(`/despesas/fixa/${id}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Excluído!',
                                text: 'A despesa fixa foi excluída com sucesso.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    });
                }
            });
        });
    });
}

// Inicializar todos os event listeners após o carregamento do DOM
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para os inputs de valor no formulário de edição
    const valorInputs = document.querySelectorAll('.valor-input');
    valorInputs.forEach(input => {
        input.addEventListener('keyup', function(e) {
            aplicarMascaraMonetaria(this);
        });
        
        input.addEventListener('blur', function(e) {
            if (this.value) {
                aplicarMascaraMonetaria(this);
            }
        });
    });
    
    // Inicializar todos os botões e formulários
    initEditarButtons();
    initCancelarEdicao();
    initEditarForm();
    initExcluirButtons();
    initInserirButtons();
    initEditarFixaButtons();
    initCancelarEdicaoFixa();
    initEditarFixaForm();
    initExcluirFixaButtons();
});
</script>
@endsection