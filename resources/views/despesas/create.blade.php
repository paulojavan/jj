@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Cadastro de Despesas" 
        subtitle="Registre uma nova despesa no sistema"
        icon="fas fa-receipt">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-arrow-left" href="{{ route('despesas.index') }}">
                Voltar
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form action="{{ route('despesas.store') }}" method="POST">
        <!-- Seção: Informações da Despesa -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>Informações da Despesa
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    type="number"
                    label="Quantidade de Boletos" 
                    name="quantidade" 
                    placeholder="Número de boletos" 
                    :value="old('quantidade')"
                    icon="fas fa-hashtag"
                    min="1"
                    required />
                
                <x-input 
                    type="select"
                    label="Tipo de Despesa" 
                    name="tipo" 
                    :options="['Boleto' => 'Boleto', 'Despeza' => 'Despeza', 'Cheque' => 'Cheque']"
                    :value="old('tipo')"
                    icon="fas fa-tags"
                    required />
            </div>

            <div id="metodo-pagamento-container" class="mb-4" style="display: none;">
                <x-input 
                    label="Método de Pagamento" 
                    name="metodo_pagamento" 
                    placeholder="Digite o método de pagamento" 
                    :value="old('metodo_pagamento')"
                    icon="fas fa-credit-card" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    label="Nome da Empresa" 
                    name="empresa" 
                    placeholder="Nome da empresa" 
                    :value="old('empresa')"
                    icon="fas fa-building"
                    required />
                
                <x-input 
                    label="Número do Documento" 
                    name="numero_documento" 
                    placeholder="Número do documento" 
                    :value="old('numero_documento')"
                    icon="fas fa-file-alt"
                    required />
            </div>

            @if(count($cidades) > 0)
            <div class="grid grid-cols-1 gap-4">
                <x-input 
                    type="select"
                    label="Cidade" 
                    name="cidade_id" 
                    :options="collect($cidades)->pluck('cidade', 'id')->toArray()"
                    :value="old('cidade_id')"
                    icon="fas fa-map-marker-alt"
                    required />
            </div>
            @endif
        </div>

        <!-- Container para parcelas dinâmicas -->
        <div id="parcelas-container" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6"></div>
        
        <!-- Botões de ação -->
        <div class="flex justify-end space-x-4">
            <x-button variant="secondary" type="button" onclick="window.history.back()">
                Cancelar
            </x-button>
            <x-button variant="primary" type="submit" icon="fas fa-save">
                Cadastrar Despesa
            </x-button>
        </div>
    </x-form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Função para mostrar/ocultar campo de método de pagamento
document.getElementById('tipo').addEventListener('change', function() {
    const metodoPagamentoContainer = document.getElementById('metodo-pagamento-container');
    const metodoPagamentoInput = document.getElementById('metodo_pagamento');

    if (this.value === 'Despeza') {
        metodoPagamentoContainer.style.display = 'block';
        metodoPagamentoInput.required = true;
    } else {
        metodoPagamentoContainer.style.display = 'none';
        metodoPagamentoInput.required = false;
        metodoPagamentoInput.value = '';
    }
});
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

document.getElementById('quantidade').addEventListener('change', function() {
    const quantidade = this.value;
    const container = document.getElementById('parcelas-container');
    container.innerHTML = '';
    for (let i = 1; i <= quantidade; i++) {
        container.innerHTML += `
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Parcela ${i} - Data</label>
                <input type="date" name="datas[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Parcela ${i} - Valor</label>
                <input type="text" name="valores[]" class="valor-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
            </div>
        `;
    }

    // Adiciona evento de máscara para os novos campos
    document.querySelectorAll('.valor-input').forEach(input => {
        input.addEventListener('keyup', function(e) {
            aplicarMascaraMonetaria(this);
        });

        input.addEventListener('blur', function(e) {
            if (this.value) {
                aplicarMascaraMonetaria(this);
            }
        });
    });
});

// Formata os valores antes de enviar o formulário
document.querySelector('form').addEventListener('submit', function(e) {
    document.querySelectorAll('.valor-input').forEach(input => {
        // Remove a máscara e converte para número
        const valorNumerico = removerMascaraMonetaria(input.value);
        // Substitui o valor formatado pelo valor numérico
        input.value = valorNumerico;
    });
});
</script>
@if (session('success'))
<script>
Swal.fire({
  title: 'Sucesso!',
  text: '{{ session('success') }}',
  icon: 'success',
  confirmButtonText: 'OK'
});
</script>
@endif
@endsection
