@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header
        title="Editar Marca"
        subtitle="Atualize as informações da marca"
        icon="fas fa-edit">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-list" href="{{ route('marcas.index') }}">
                Listar Marcas
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form
        title="Dados da Marca"
        subtitle="Atualize as informações da marca"
        action="{{ route('marcas.update', $marca) }}"
        method="POST"
    >
        @method('PUT')
        <x-input
            label="Nome da Marca"
            name="marca"
            type="text"
            placeholder="Nome da marca"
            :value="$marca->marca ?? old('marca')"
            icon="fas fa-tags"
            required="true"
        />

        <div class="flex justify-end space-x-3">
            <x-button variant="danger" href="{{ route('marcas.index') }}">
                Cancelar
            </x-button>
            <x-button type="submit" variant="success">
                Atualizar Marca
            </x-button>
        </div>
    </x-form>

</div>

@endsection
