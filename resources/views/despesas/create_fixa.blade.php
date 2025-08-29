@extends('layouts.base')

@section('content')
<x-page-header 
    title="Cadastrar Despesa Fixa" 
    subtitle="Adicione uma nova despesa fixa ao sistema" 
    icon="fas fa-plus-circle" 
/>

<x-alert />

<x-form 
    title="Dados da Despesa Fixa" 
    subtitle="Preencha as informações da despesa fixa" 
    action="{{ route('despesas.store.fixa') }}" 
    method="POST"
>
    <x-input 
        label="Dia Aproximado" 
        name="dia" 
        type="select" 
        required="true" 
        :options="collect(range(1, 31))->mapWithKeys(fn($i) => [$i => $i])"
    />
    
    <x-input 
        label="Tipo de Despesa" 
        name="tipo" 
        type="text" 
        placeholder="Ex: Aluguel, Energia, Água..." 
        required="true" 
    />
    
    <x-input 
        label="Nome da Empresa" 
        name="empresa" 
        type="text" 
        placeholder="Nome da empresa fornecedora" 
        required="true" 
    />
    
    <x-input 
        label="Valor" 
        name="valor" 
        type="text" 
        placeholder="R$ 0,00" 
        required="true" 
        class="valor-input" 
    />
    
    @if(count($cidades) > 0)
    <x-input 
        label="Cidade" 
        name="cidade_id" 
        type="select" 
        required="true" 
        :options="$cidades->pluck('cidade', 'id')"
    />
    @endif
    
    <div class="flex justify-end space-x-3">
        <a href="{{ route('despesas.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Cancelar
        </a>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Cadastrar Despesa Fixa
        </button>
    </div>
</x-form>

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

// Adiciona evento de máscara para o campo de valor
document.addEventListener('DOMContentLoaded', function() {
    const valorInput = document.querySelector('.valor-input');
    
    valorInput.addEventListener('keyup', function(e) {
        aplicarMascaraMonetaria(this);
    });
    
    valorInput.addEventListener('blur', function(e) {
        if (this.value) {
            aplicarMascaraMonetaria(this);
        }
    });
});

// Formata os valores antes de enviar o formulário
document.querySelector('form').addEventListener('submit', function(e) {
    const valorInput = document.querySelector('.valor-input');
    // Remove a máscara e converte para número
    const valorNumerico = removerMascaraMonetaria(valorInput.value);
    // Substitui o valor formatado pelo valor numérico
    valorInput.value = valorNumerico;
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