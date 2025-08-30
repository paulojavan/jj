@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Cadastro de Marca" 
        subtitle="Adicione uma nova marca de produtos" 
        icon="fas fa-tags">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-list" href="{{ route('marcas.index') }}">
                Listar Marcas
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form 
        title="Dados da Marca" 
        subtitle="Preencha as informações da nova marca" 
        action="{{ route('marcas.store') }}" 
        method="POST"
    >
        <x-input 
            label="Nome da Marca" 
            name="marca" 
            type="text" 
            placeholder="Nome da marca" 
            :value="old('marca')" 
            icon="fas fa-tags" 
            required="true" 
        />
        
        <div class="flex justify-end space-x-3">
            <x-button variant="danger" href="{{ route('marcas.index') }}">
                Cancelar
            </x-button>
            <x-button type="submit" variant="success">
                Cadastrar Marca
            </x-button>
        </div>
    </x-form>

</div>

@endsection
