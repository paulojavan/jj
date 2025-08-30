@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Cadastro de Pessoa Autorizada" 
        subtitle="Adicione uma nova pessoa autorizada para o cliente" 
        icon="fas fa-user-check">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-arrow-left" href="{{ route('clientes.edit', request()->route('cliente_id')) }}">
                Voltar ao Cliente
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form 
        title="Dados da Pessoa Autorizada" 
        subtitle="Preencha as informações da pessoa autorizada" 
        action="{{ route('autorizados.store') }}" 
        method="POST"
        enctype="multipart/form-data"
    >
        <input type="hidden" name="cliente_id" value="{{ request()->route('cliente_id') }}">
        
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Dados Pessoais</h3>
            
            <x-input 
                label="Nome Completo" 
                name="nome" 
                type="text" 
                placeholder="Nome completo da pessoa autorizada" 
                :value="old('nome')" 
                icon="fas fa-user" 
            />
            
            <x-input 
                label="RG" 
                name="rg" 
                type="text" 
                placeholder="RG da pessoa autorizada" 
                :value="old('rg')" 
                icon="fas fa-id-card" 
            />
            
            <x-input 
                label="CPF" 
                name="cpf" 
                type="text" 
                placeholder="CPF da pessoa autorizada" 
                :value="old('cpf')" 
                icon="fas fa-id-card-alt" 
            />
        </div>
        
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Foto</h3>
            
            <x-input 
                label="Foto da Pessoa Autorizada" 
                name="foto" 
                type="file" 
                accept="image/*" 
                icon="fas fa-camera" 
                help="Selecione uma foto da pessoa autorizada" 
            />
        </div>
        
        <x-slot name="actions">
            <x-button type="submit" variant="primary" icon="fas fa-save">
                Cadastrar Pessoa Autorizada
            </x-button>
            <x-button variant="danger" href="{{ route('clientes.edit', request()->route('cliente_id')) }}" icon="fas fa-times">
                Cancelar
            </x-button>
        </x-slot>
    </x-form>

</div>

@endsection
