@extends('layouts.base')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Cadastrar Despesas</h1>
    <form method="POST" action="{{ route('despesas.store') }}">
        @csrf
        <div class="mb-4">
            <label for="quantidade" class="block text-sm font-medium text-gray-700">Quantidade de Boletos</label>
            <input type="number" name="quantidade" id="quantidade" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" min="1" required>
        </div>
        <div class="mb-4">
            <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo de Despesa</label>
            <select name="tipo" id="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                <option value="Boleto">Boleto</option>
                <option value="Despeza">Despeza</option>
                <option value="Cheque">Cheque</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="empresa" class="block text-sm font-medium text-gray-700">Nome da Empresa</label>
            <input type="text" name="empresa" id="empresa" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
        </div>
        <div class="mb-4">
            <label for="numero_documento" class="block text-sm font-medium text-gray-700">Número do Documento</label>
            <input type="text" name="numero_documento" id="numero_documento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
        </div>
        @if(count($cidades) > 0)
        <div class="mb-4">
            <label for="cidade" class="block text-sm font-medium text-gray-700">Cidade</label>
            <select name="cidade_id" id="cidade" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                @foreach($cidades as $cidade)
                    <option value="{{ $cidade->id }}">{{ $cidade->cidade }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div id="parcelas-container" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">Cadastrar</button>
    </form>
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