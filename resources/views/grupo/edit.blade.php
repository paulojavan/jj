@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Editar Grupo" 
        subtitle="Atualize as informações do grupo" 
        icon="fas fa-edit">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-list" href="{{ route('grupos.index') }}">
                Listar Grupos
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form 
        title="Dados do Grupo" 
        subtitle="Atualize as informações do grupo" 
        action="{{ route('grupos.update', $grupo->id) }}" 
        method="PUT"
    >
        <x-input 
            label="Nome do Grupo" 
            name="grupo" 
            type="text" 
            placeholder="Nome do grupo" 
            :value="$grupo->grupo ?? old('grupo')" 
            icon="fas fa-layer-group" 
            required="true" 
        />
        
        <div class="flex justify-end space-x-3">
            <x-button variant="secondary" href="{{ route('grupos.index') }}">
                Cancelar
            </x-button>
            <x-button type="submit" variant="success">
                Atualizar Grupo
            </x-button>
        </div>
    </x-form>

</div>

@endsection
