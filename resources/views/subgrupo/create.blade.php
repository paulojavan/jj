@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Cadastro de Subgrupo" 
        subtitle="Adicione um novo subgrupo de produtos" 
        icon="fas fa-sitemap">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-list" href="{{ route('subgrupos.index') }}">
                Listar Subgrupos
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form 
        title="Dados do Subgrupo" 
        subtitle="Preencha as informações do novo subgrupo" 
        action="{{ route('subgrupos.store') }}" 
        method="POST"
    >
        <x-input 
            label="Nome do Subgrupo" 
            name="subgrupo" 
            type="text" 
            placeholder="Nome do subgrupo" 
            :value="old('subgrupo')" 
            icon="fas fa-sitemap" 
            required="true" 
        />
        
        <div class="flex justify-end space-x-3">
            <x-button variant="secondary" href="{{ route('subgrupos.index') }}">
                Cancelar
            </x-button>
            <x-button type="submit" variant="success">
                Cadastrar Subgrupo
            </x-button>
        </div>
    </x-form>

</div>

@endsection
