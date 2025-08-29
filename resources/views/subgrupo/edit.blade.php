@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Editar Subgrupo" 
        subtitle="Atualize as informações do subgrupo" 
        icon="fas fa-edit">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-list" href="{{ route('subgrupos.index') }}">
                Listar Subgrupos
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form 
        title="Dados do Subgrupo" 
        subtitle="Atualize as informações do subgrupo" 
        action="{{ route('subgrupos.update', $subgrupo->id) }}" 
        method="PUT"
    >
        <x-input 
            label="Nome do Subgrupo" 
            name="subgrupo" 
            type="text" 
            placeholder="Nome do subgrupo" 
            :value="$subgrupo->subgrupo ?? old('subgrupo')" 
            icon="fas fa-sitemap" 
            required="true" 
        />
        
        <div class="flex justify-end space-x-3">
            <x-button variant="secondary" href="{{ route('subgrupos.index') }}">
                Cancelar
            </x-button>
            <x-button type="submit" variant="success">
                Atualizar Subgrupo
            </x-button>
        </div>
    </x-form>

</div>

@endsection
