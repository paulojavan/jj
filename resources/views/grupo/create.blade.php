@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Cadastro de Grupo" 
        subtitle="Adicione um novo grupo de produtos" 
        icon="fas fa-layer-group">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-list" href="{{ route('grupos.index') }}">
                Listar Grupos
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form 
        title="Dados do Grupo" 
        subtitle="Preencha as informações do novo grupo" 
        action="{{ route('grupos.store') }}" 
        method="POST"
    >
        <x-input 
            label="Nome do Grupo" 
            name="grupo" 
            type="text" 
            placeholder="Nome do grupo" 
            :value="old('grupo')" 
            icon="fas fa-layer-group" 
            required="true" 
        />
        
        <div class="flex justify-end space-x-3">
            <x-button variant="secondary" href="{{ route('grupos.index') }}">
                Cancelar
            </x-button>
            <x-button type="submit" variant="success">
                Cadastrar Grupo
            </x-button>
        </div>
    </x-form>

</div>

@endsection
