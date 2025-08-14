@extends('layouts.base')

@section('title', 'Verificação de Limite')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Verificação de Limite</h1>
        <p class="text-gray-600">Analise perfis de clientes e gerencie limites de crédito</p>
    </div>

    <!-- Busca de Clientes -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Buscar Cliente</h2>
        <div class="relative">
            <input 
                type="text" 
                id="busca-cliente" 
                placeholder="Digite nome, apelido ou CPF do cliente..."
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            
            <!-- Dropdown de resultados -->
            <div id="resultados-busca" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden">
                <!-- Resultados serão inseridos aqui via JavaScript -->
            </div>
        </div>
        
        <!-- Loading indicator -->
        <div id="loading-busca" class="hidden mt-2">
            <div class="flex items-center text-blue-600">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Buscando...
            </div>
        </div>
    </div>

    <!-- Área do Perfil do Cliente -->
    <div id="perfil-cliente" class="hidden">
        <!-- Informações Básicas -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Informações Básicas</h2>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Status:</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="toggle-status" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span id="status-label" class="ml-3 text-sm font-medium text-gray-900">Inativo</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Foto do Cliente -->
                <div class="flex-shrink-0">
                    <div class="w-32 h-40 bg-gray-200 rounded-lg overflow-hidden border-2 border-gray-300">
                        <img id="cliente-foto" src="" alt="Foto do cliente" class="w-full h-full object-cover hidden">
                        <div id="sem-foto" class="w-full h-full flex items-center justify-center text-gray-500">
                            <div class="text-center">
                                <i class="fas fa-user text-3xl mb-2"></i>
                                <p class="text-xs">Sem foto</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Informações do Cliente -->
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                        <p id="cliente-nome" class="text-gray-900 font-medium">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apelido</label>
                        <p id="cliente-apelido" class="text-gray-900">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RG</label>
                        <p id="cliente-rg" class="text-gray-900">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                        <p id="cliente-cpf" class="text-gray-900">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Renda</label>
                        <p id="cliente-renda" class="text-gray-900">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Limite Atual</label>
                        <p id="cliente-limite" class="text-gray-900 font-bold text-lg text-green-600">R$ 0,00</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referências Comerciais -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Referências Comerciais</h2>
            <div id="referencias-comerciais" class="space-y-3">
                <!-- Referências serão inseridas aqui via JavaScript -->
            </div>
        </div>

        <!-- Perfis de Compras e Pagamentos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Perfil de Compras -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Perfil de Compras</h2>
                <div id="perfil-compras-content" class="space-y-4">
                    <!-- Conteúdo será inserido via JavaScript -->
                </div>
            </div>

            <!-- Perfil de Pagamentos -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Perfil de Pagamentos</h2>
                <div id="perfil-pagamentos-content" class="space-y-4">
                    <!-- Conteúdo será inserido via JavaScript -->
                </div>
            </div>
        </div>

        <!-- Alteração de Limite -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Gerenciar Limite</h2>
            <div class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label for="novo-limite" class="block text-sm font-medium text-gray-700 mb-2">Novo Limite (R$)</label>
                    <input 
                        type="text" 
                        id="novo-limite" 
                        placeholder="R$ 0,00"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                <button 
                    id="btn-atualizar-limite" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                >
                    Atualizar Limite
                </button>
            </div>
            
            <!-- Recomendação automática -->
            <div id="recomendacao-limite" class="mt-4 p-4 bg-blue-50 rounded-lg hidden">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-blue-800 font-medium">Recomendação:</span>
                    <span id="valor-recomendado" class="ml-2 text-blue-900 font-bold"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagem quando nenhum cliente está selecionado -->
    <div id="sem-cliente" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum cliente selecionado</h3>
        <p class="text-gray-600">Use o campo de busca acima para encontrar um cliente</p>
    </div>
</div>

<!-- Toast para notificações -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm">
        <div class="flex items-center">
            <div id="toast-icon" class="flex-shrink-0 mr-3">
                <!-- Ícone será inserido via JavaScript -->
            </div>
            <div>
                <p id="toast-message" class="text-sm font-medium text-gray-900"></p>
            </div>
            <button id="toast-close" class="ml-4 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Variáveis globais
let clienteSelecionado = null;
let timeoutBusca = null;

// Elementos DOM
const inputBusca = document.getElementById('busca-cliente');
const resultadosBusca = document.getElementById('resultados-busca');
const loadingBusca = document.getElementById('loading-busca');
const perfilCliente = document.getElementById('perfil-cliente');
const semCliente = document.getElementById('sem-cliente');
const toggleStatus = document.getElementById('toggle-status');
const btnAtualizarLimite = document.getElementById('btn-atualizar-limite');
const inputNovoLimite = document.getElementById('novo-limite');

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar máscara monetária ao input de limite
    aplicarMascaraMonetaria(inputNovoLimite);
    // Busca de clientes
    inputBusca.addEventListener('input', function() {
        const termo = this.value.trim();
        
        // Limpar timeout anterior
        if (timeoutBusca) {
            clearTimeout(timeoutBusca);
        }
        
        // Esconder resultados se termo muito curto
        if (termo.length < 2) {
            esconderResultados();
            return;
        }
        
        // Debounce da busca
        timeoutBusca = setTimeout(() => {
            buscarClientes(termo);
        }, 300);
    });

    // Toggle de status
    toggleStatus.addEventListener('change', function() {
        if (clienteSelecionado) {
            const novoStatus = this.checked ? 'ativo' : 'inativo';
            alterarStatus(clienteSelecionado.id, novoStatus);
        }
    });

    // Atualizar limite
    btnAtualizarLimite.addEventListener('click', function() {
        if (clienteSelecionado) {
            const valorLimite = inputNovoLimite.value.replace(/[^\d,]/g, '').replace(',', '.');
            const novoLimite = parseFloat(valorLimite);
            if (isNaN(novoLimite) || novoLimite < 0) {
                mostrarToast('Por favor, insira um valor válido para o limite', 'error');
                return;
            }
            atualizarLimite(clienteSelecionado.id, novoLimite);
        }
    });

    // Fechar resultados ao clicar fora
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#busca-cliente') && !e.target.closest('#resultados-busca')) {
            esconderResultados();
        }
    });
});

// Função para buscar clientes
async function buscarClientes(termo) {
    try {
        mostrarLoading(true);
        
        const response = await fetch(`/verificacao-limite/buscar-clientes?termo=${encodeURIComponent(termo)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarResultados(data.clientes);
        } else {
            mostrarToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Erro na busca:', error);
        mostrarToast('Erro ao buscar clientes', 'error');
    } finally {
        mostrarLoading(false);
    }
}

// Função para mostrar resultados da busca
function mostrarResultados(clientes) {
    if (clientes.length === 0) {
        resultadosBusca.innerHTML = '<div class="p-4 text-gray-500 text-center">Nenhum cliente encontrado</div>';
    } else {
        const html = clientes.map(cliente => `
            <div class="p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                 onclick="selecionarCliente(${cliente.id})">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-medium text-gray-900">${cliente.nome}</p>
                        <p class="text-sm text-gray-600">${cliente.apelido || 'Sem apelido'}</p>
                        <p class="text-sm text-gray-500">${cliente.cpf_formatado}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            cliente.status === 'ativo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }">
                            ${cliente.status}
                        </span>
                        <p class="text-sm text-gray-600 mt-1">Limite: R$ ${parseFloat(cliente.limite || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                    </div>
                </div>
            </div>
        `).join('');
        
        resultadosBusca.innerHTML = html;
    }
    
    resultadosBusca.classList.remove('hidden');
}

// Função para esconder resultados
function esconderResultados() {
    resultadosBusca.classList.add('hidden');
}

// Função para mostrar/esconder loading
function mostrarLoading(mostrar) {
    if (mostrar) {
        loadingBusca.classList.remove('hidden');
    } else {
        loadingBusca.classList.add('hidden');
    }
}

// Função para selecionar cliente
async function selecionarCliente(clienteId) {
    try {
        esconderResultados();
        inputBusca.value = 'Carregando...';
        
        const response = await fetch(`/verificacao-limite/perfil-cliente/${clienteId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.success) {
            clienteSelecionado = data.cliente;
            exibirPerfilCliente(data.cliente);
            inputBusca.value = `${data.cliente.nome} - ${data.cliente.cpf}`;
        } else {
            mostrarToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao carregar perfil:', error);
        mostrarToast('Erro ao carregar perfil do cliente', 'error');
    }
}

// Função para exibir perfil do cliente
function exibirPerfilCliente(cliente) {
    // Mostrar seção do perfil
    semCliente.classList.add('hidden');
    perfilCliente.classList.remove('hidden');
    
    // Preencher informações básicas
    document.getElementById('cliente-nome').textContent = cliente.nome;
    document.getElementById('cliente-apelido').textContent = cliente.apelido || 'Não informado';
    document.getElementById('cliente-rg').textContent = cliente.rg || 'Não informado';
    document.getElementById('cliente-cpf').textContent = cliente.cpf;
    document.getElementById('cliente-renda').textContent = cliente.renda;
    document.getElementById('cliente-limite').textContent = `R$ ${cliente.limite_atual}`;
    
    // Configurar toggle de status
    toggleStatus.checked = cliente.status === 'ativo';
    document.getElementById('status-label').textContent = cliente.status === 'ativo' ? 'Ativo' : 'Inativo';
    
    // Preencher input de limite com máscara
    inputNovoLimite.value = formatarMoeda(cliente.limite_atual_numerico);
    
    // Exibir foto do cliente
    exibirFotoCliente(cliente);
    
    // Exibir referências comerciais
    exibirReferenciasComerciais(cliente.referencias_comerciais);
    
    // Exibir perfis
    exibirPerfilCompras(cliente.perfil_compras);
    exibirPerfilPagamentos(cliente.perfil_pagamentos);
}

// Função para exibir referências comerciais
function exibirReferenciasComerciais(referencias) {
    const container = document.getElementById('referencias-comerciais');
    
    const html = referencias.map((ref, index) => `
        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
            <div class="flex-shrink-0">
                <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                    ${index + 1}
                </span>
            </div>
            <div class="flex-1">
                <p class="font-medium text-gray-900">${ref.nome}</p>
                <p class="text-sm text-gray-600">${ref.telefone}</p>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

// Função para exibir perfil de compras
function exibirPerfilCompras(perfil) {
    const container = document.getElementById('perfil-compras-content');
    
    const html = `
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">${perfil.total_compras}</p>
                <p class="text-sm text-gray-600">Total de Compras</p>
            </div>
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">R$ ${parseFloat(perfil.valor_total_gasto || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                <p class="text-sm text-gray-600">Valor Total Gasto</p>
            </div>
            <div class="text-center p-3 bg-purple-50 rounded-lg">
                <p class="text-2xl font-bold text-purple-600">R$ ${parseFloat(perfil.ticket_medio || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                <p class="text-sm text-gray-600">Ticket Médio</p>
            </div>
            <div class="text-center p-3 bg-orange-50 rounded-lg">
                <p class="text-2xl font-bold text-orange-600">${parseFloat(perfil.frequencia_compras?.compras_por_mes || 0).toFixed(1)}</p>
                <p class="text-sm text-gray-600">Compras/Mês</p>
            </div>
        </div>
        
        ${perfil.frequencia_compras?.ultima_compra ? `
        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600">Última compra: <span class="font-medium">${new Date(perfil.frequencia_compras.ultima_compra).toLocaleDateString('pt-BR')}</span></p>
        </div>
        ` : ''}
    `;
    
    container.innerHTML = html;
}

// Função para exibir perfil de pagamentos
function exibirPerfilPagamentos(perfil) {
    const container = document.getElementById('perfil-pagamentos-content');
    
    const scoreColor = perfil.risco_calculado?.score >= 80 ? 'green' : 
                      perfil.risco_calculado?.score >= 60 ? 'yellow' : 'red';
    
    const html = `
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">${parseFloat(perfil.pontualidade?.percentual_pontual || 0).toFixed(1)}%</p>
                <p class="text-sm text-gray-600">Pontualidade</p>
            </div>
            <div class="text-center p-3 bg-red-50 rounded-lg">
                <p class="text-2xl font-bold text-red-600">${perfil.inadimplencia?.parcelas_em_atraso || 0}</p>
                <p class="text-sm text-gray-600">Parcelas em Atraso</p>
            </div>
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">${parseFloat(perfil.pontualidade?.atraso_medio_dias || 0).toFixed(1)}</p>
                <p class="text-sm text-gray-600">Atraso Médio (dias)</p>
            </div>
            <div class="text-center p-3 bg-${scoreColor}-50 rounded-lg">
                <p class="text-2xl font-bold text-${scoreColor}-600">${perfil.risco_calculado?.score || 0}/100</p>
                <p class="text-sm text-gray-600">Score de Risco</p>
            </div>
        </div>
        
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-medium text-gray-900">Classificação de Risco</p>
                    <p class="text-sm text-gray-600 capitalize">${perfil.risco_calculado?.classificacao || 'Não calculado'}</p>
                </div>
                <div class="text-right">
                    <p class="font-medium text-gray-900">Valor em Atraso</p>
                    <p class="text-sm text-red-600">R$ ${parseFloat(perfil.inadimplencia?.valor_em_atraso || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Mostrar recomendação de limite
    if (perfil.risco_calculado?.recomendacao_limite > 0) {
        const recomendacao = document.getElementById('recomendacao-limite');
        const valorRecomendado = document.getElementById('valor-recomendado');
        valorRecomendado.textContent = `R$ ${parseFloat(perfil.risco_calculado.recomendacao_limite).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
        recomendacao.classList.remove('hidden');
    }
}

// Função para alterar status
async function alterarStatus(clienteId, novoStatus) {
    try {
        const response = await fetch(`/verificacao-limite/alterar-status/${clienteId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: novoStatus })
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarToast(data.message, 'success');
            document.getElementById('status-label').textContent = novoStatus === 'ativo' ? 'Ativo' : 'Inativo';
            clienteSelecionado.status = novoStatus;
        } else {
            mostrarToast(data.message, 'error');
            // Reverter toggle
            toggleStatus.checked = clienteSelecionado.status === 'ativo';
        }
    } catch (error) {
        console.error('Erro ao alterar status:', error);
        mostrarToast('Erro ao alterar status', 'error');
        // Reverter toggle
        toggleStatus.checked = clienteSelecionado.status === 'ativo';
    }
}

// Função para atualizar limite
async function atualizarLimite(clienteId, novoLimite) {
    try {
        const response = await fetch(`/verificacao-limite/atualizar-limite/${clienteId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ limite: novoLimite })
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarToast(data.message, 'success');
            document.getElementById('cliente-limite').textContent = `R$ ${data.limite_formatado}`;
            clienteSelecionado.limite_atual = data.limite_formatado;
            clienteSelecionado.limite_atual_numerico = data.limite_numerico;
        } else {
            mostrarToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao atualizar limite:', error);
        mostrarToast('Erro ao atualizar limite', 'error');
    }
}

// Função para mostrar toast
function mostrarToast(mensagem, tipo = 'info') {
    const toast = document.getElementById('toast');
    const toastIcon = document.getElementById('toast-icon');
    const toastMessage = document.getElementById('toast-message');
    
    // Definir ícone baseado no tipo
    let iconHtml = '';
    if (tipo === 'success') {
        iconHtml = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
    } else if (tipo === 'error') {
        iconHtml = '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
    } else {
        iconHtml = '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    }
    
    toastIcon.innerHTML = iconHtml;
    toastMessage.textContent = mensagem;
    
    // Mostrar toast
    toast.classList.remove('hidden');
    
    // Auto-hide após 5 segundos
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 5000);
    
    // Botão de fechar
    document.getElementById('toast-close').onclick = () => {
        toast.classList.add('hidden');
    };
}

// Função para aplicar máscara monetária
function aplicarMascaraMonetaria(input) {
    input.addEventListener('input', function(e) {
        let value = e.target.value;
        
        // Remove tudo que não é dígito
        value = value.replace(/\D/g, '');
        
        // Converte para centavos
        value = (parseInt(value) / 100).toFixed(2);
        
        // Aplica formatação brasileira
        value = value.replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        
        // Adiciona o prefixo R$
        e.target.value = 'R$ ' + value;
    });
    
    // Remove formatação ao focar para facilitar edição
    input.addEventListener('focus', function(e) {
        let value = e.target.value;
        value = value.replace(/[^\d,]/g, '');
        if (value === '0,00') value = '';
        e.target.value = value;
    });
    
    // Reaplica formatação ao sair do campo
    input.addEventListener('blur', function(e) {
        let value = e.target.value;
        if (value === '') {
            e.target.value = 'R$ 0,00';
            return;
        }
        
        // Garante que tem pelo menos 2 casas decimais
        if (!value.includes(',')) {
            value += ',00';
        } else {
            const parts = value.split(',');
            if (parts[1].length === 1) {
                value += '0';
            }
        }
        
        // Aplica formatação
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        e.target.value = 'R$ ' + value;
    });
}

// Função para formatar valor como moeda
function formatarMoeda(valor) {
    if (!valor || valor === 0) return 'R$ 0,00';
    
    return 'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Função para exibir foto do cliente
function exibirFotoCliente(cliente) {
    const fotoImg = document.getElementById('cliente-foto');
    const semFoto = document.getElementById('sem-foto');
    
    if (cliente.foto && cliente.pasta) {
        const urlFoto = `/storage/uploads/clientes/${cliente.pasta}/${cliente.foto}`;
        
        // Testar se a imagem existe
        const img = new Image();
        img.onload = function() {
            fotoImg.src = urlFoto;
            fotoImg.classList.remove('hidden');
            semFoto.classList.add('hidden');
        };
        img.onerror = function() {
            fotoImg.classList.add('hidden');
            semFoto.classList.remove('hidden');
        };
        img.src = urlFoto;
    } else {
        fotoImg.classList.add('hidden');
        semFoto.classList.remove('hidden');
    }
}
</script>
@endsection