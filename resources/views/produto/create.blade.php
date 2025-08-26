@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Cadastro de Produto" 
        subtitle="Adicione um novo produto ao catálogo"
        icon="fas fa-plus-circle">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-arrow-left" href="{{ route('produtos.index') }}">
                Voltar
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data">

        <!-- Seção: Informações Básicas -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>Informações Básicas
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    label="Nome do Produto" 
                    name="produto" 
                    placeholder="Nome do produto" 
                    :value="old('produto')" 
                    icon="fas fa-shoe-prints"
                    required />
                
                <x-input 
                    label="Código" 
                    name="codigo" 
                    placeholder="Código do produto" 
                    :value="old('codigo')" 
                    icon="fas fa-barcode"
                    required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-input 
                    type="select"
                    label="Marca" 
                    name="marca" 
                    :options="collect($marcas)->pluck('marca', 'marca')->toArray()"
                    icon="fas fa-tags"
                    required />
                
                <x-input 
                    type="select"
                    label="Gênero" 
                    name="genero" 
                    :options="[
                        'masculino' => 'Masculino',
                        'feminino' => 'Feminino'
                    ]"
                    icon="fas fa-venus-mars"
                    required />
                
                <x-input 
                    label="Quantidade" 
                    name="quantidade" 
                    type="number"
                    placeholder="Quantidade em estoque" 
                    :value="old('quantidade')" 
                    icon="fas fa-boxes"
                    required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    type="select"
                    label="Grupo" 
                    name="grupo" 
                    :options="collect($grupos)->pluck('grupo', 'grupo')->toArray()"
                    icon="fas fa-layer-group"
                    required />
                
                <x-input 
                    type="select"
                    label="Sub-grupo" 
                    name="subgrupo" 
                    :options="collect($subgrupos)->pluck('subgrupo', 'subgrupo')->toArray()"
                    icon="fas fa-sitemap"
                    required />
            </div>
        </div>

        <!-- Seção: Numeração -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-ruler mr-2"></i>Numeração Disponível
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    type="select"
                    label="Número Inicial" 
                    name="num1" 
                    :options="array_combine(range(14, 46), range(14, 46))"
                    icon="fas fa-play"
                    required />
                
                <x-input 
                    type="select"
                    label="Número Final" 
                    name="num2" 
                    :options="array_combine(range(14, 46), range(14, 46))"
                    icon="fas fa-stop"
                    required />
            </div>
        </div>

        <!-- Seção: Preço e Foto -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-dollar-sign mr-2"></i>Preço e Imagem
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    label="Preço" 
                    name="preco" 
                    placeholder="R$ 0,00" 
                    :value="old('preco')" 
                    icon="fas fa-money-bill-wave"
                    mask="money"
                    required />
                
                <x-input 
                    label="Foto do Produto" 
                    name="foto" 
                    type="file"
                    accept="image/*"
                    icon="fas fa-camera"
                    help="Selecione uma foto do produto (formatos: JPG, PNG, GIF)" />
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-center gap-4">
            <x-button variant="secondary" icon="fas fa-times" href="{{ route('produtos.index') }}">
                Cancelar
            </x-button>
            <x-button type="submit" variant="success" icon="fas fa-save">
                Cadastrar Produto
            </x-button>
        </div>
    </x-form>

@endsection
