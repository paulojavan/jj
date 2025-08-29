@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Editar Produto" 
        subtitle="Atualize as informações do produto: {{ $produto->produto }}" 
        icon="fas fa-edit">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-list" href="{{ route('produtos.index') }}">
                Listar Produtos
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form 
        title="Dados do Produto" 
        subtitle="Atualize as informações do produto" 
        action="{{ route('produtos.update', $produto->id) }}" 
        method="PUT"
        enctype="multipart/form-data"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input 
                label="Nome do Produto" 
                name="produto" 
                type="text" 
                placeholder="Nome do produto" 
                :value="$produto->produto" 
                icon="fas fa-shoe-prints" 
                required="true" 
            />
            
            <x-input 
                label="Marca" 
                name="marca" 
                type="select" 
                icon="fas fa-tags" 
                required="true"
                :options="$marcas->pluck('marca', 'marca')->toArray()"
                :value="$produto->marca"
            />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input 
                label="Gênero" 
                name="genero" 
                type="select" 
                icon="fas fa-venus-mars" 
                required="true"
                :options="['masculino' => 'Masculino', 'feminino' => 'Feminino']"
                :value="$produto->genero"
            />
            
            <x-input 
                label="Grupo" 
                name="grupo" 
                type="select" 
                icon="fas fa-layer-group" 
                required="true"
                :options="$grupos->pluck('grupo', 'grupo')->toArray()"
                :value="$produto->grupo"
            />
            
            <x-input 
                label="Subgrupo" 
                name="subgrupo" 
                type="select" 
                icon="fas fa-sitemap" 
                required="true"
                :options="$subgrupos->pluck('subgrupo', 'subgrupo')->toArray()"
                :value="$produto->subgrupo"
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input 
                label="Código" 
                name="codigo" 
                type="text" 
                placeholder="Código do produto" 
                :value="$produto->codigo" 
                icon="fas fa-barcode" 
                required="true" 
            />
            
            <x-input 
                label="Quantidade" 
                name="quantidade" 
                type="number" 
                placeholder="Quantidade em estoque" 
                :value="$produto->quantidade" 
                icon="fas fa-boxes" 
                required="true" 
            />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sort-numeric-up mr-2"></i>Número Inicial
                </label>
                <select name="num1" id="num1" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @for ($i = 14; $i <= $produto->num1; $i++)
                        <option value="{{ $i }}" @if($i == $produto->num1) selected @endif>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sort-numeric-down mr-2"></i>Número Final
                </label>
                <select name="num2" id="num2" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @for ($i = $produto->num2; $i <= 46; $i++)
                        <option value="{{ $i }}" @if($i == $produto->num2) selected @endif>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
        
        <x-input 
            label="Preço" 
            name="preco" 
            type="text" 
            placeholder="Preço do produto" 
            value="R$ {{ $produto->preco }}" 
            icon="fas fa-dollar-sign" 
            required="true"
            oninput="formatCurrency(this)"
        />
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-image mr-2"></i>Foto Atual do Produto
            </label>
            <div class="text-center mb-4">
                <img class="h-auto max-w-xs mx-auto rounded-lg shadow-md" src="{{ asset('storage/uploads/produtos/' . $produto->foto) }}" alt="{{ $produto->produto }}">
            </div>
            <x-input 
                label="Nova Foto do Produto" 
                name="foto" 
                type="file" 
                accept="image/*" 
                icon="fas fa-camera" 
            />
        </div>
        
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-times" href="{{ route('produtos.index') }}">
                Cancelar
            </x-button>
            <x-button variant="primary" type="submit" icon="fas fa-save">
                Atualizar Produto
            </x-button>
            <x-button variant="info" icon="fas fa-chart-pie" href="{{ route('produtos.distribuicao', $produto->id) }}">
                Distribuir Numerações
            </x-button>
        </x-slot>
    </x-form>

    </div>

@endsection
<script>
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    value = (value / 100).toFixed(2) + '';
    value = value.replace('.', ',');
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = 'R$ ' + value;
}
</script>
