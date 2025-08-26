@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Cadastro de Cliente" 
        subtitle="Preencha os dados do novo cliente"
        icon="fas fa-user-plus">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-arrow-left" href="{{ route('clientes.index') }}">
                Voltar
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form action="{{ route('clientes.store') }}" method="POST" enctype="multipart/form-data">
        <!-- Seção: Dados Pessoais -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-user mr-2"></i>Dados Pessoais
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    label="Nome Completo" 
                    name="nome" 
                    placeholder="Nome do cliente" 
                    :value="old('nome')" 
                    icon="fas fa-user"
                    required />
                
                <x-input 
                    label="Apelido" 
                    name="apelido" 
                    placeholder="Apelido do cliente" 
                    :value="old('apelido')" 
                    icon="fas fa-smile" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    label="RG" 
                    name="rg" 
                    placeholder="RG do cliente" 
                    :value="old('rg')" 
                    icon="fas fa-id-card"
                    required />
                
                <x-input 
                    label="CPF" 
                    name="cpf" 
                    placeholder="000.000.000-00" 
                    :value="old('cpf')" 
                    icon="fas fa-id-card-alt"
                    mask="cpf"
                    required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    label="Nome da Mãe" 
                    name="mae" 
                    placeholder="Nome da mãe" 
                    :value="old('mae')" 
                    icon="fas fa-female"
                    required />
                
                <x-input 
                    label="Nome do Pai" 
                    name="pai" 
                    placeholder="Nome do pai" 
                    :value="old('pai')" 
                    icon="fas fa-male" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    label="Telefone" 
                    name="telefone" 
                    placeholder="(XX) XXXXX-XXXX" 
                    :value="old('telefone')" 
                    icon="fas fa-phone"
                    mask="telefone"
                    required />
                
                <x-input 
                    label="Data de Nascimento" 
                    name="nascimento" 
                    type="date"
                    :value="old('nascimento')" 
                    icon="fas fa-calendar"
                    required />
            </div>

            <x-input 
                label="Fonte de Renda" 
                name="renda" 
                placeholder="Fonte de renda do cliente" 
                :value="old('renda')" 
                icon="fas fa-briefcase"
                required />
        </div>

        <!-- Seção: Dados da Referência -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-users mr-2"></i>Dados da Referência
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    label="Nome da Referência" 
                    name="nome_referencia" 
                    placeholder="Nome da pessoa de referência" 
                    :value="old('nome_referencia')" 
                    icon="fas fa-user-friends"
                    required />
                
                <x-input 
                    label="Telefone da Referência" 
                    name="telefone_referencia" 
                    placeholder="(XX) XXXXX-XXXX" 
                    :value="old('telefone_referencia')" 
                    icon="fas fa-phone"
                    mask="telefone"
                    required />
            </div>

            <x-input 
                label="Parentesco" 
                name="parentesco" 
                placeholder="Grau de parentesco com o cliente" 
                :value="old('parentesco')" 
                icon="fas fa-heart"
                required />
        </div>

        <!-- Seção: Referências Comerciais -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-store mr-2"></i>Referências Comerciais
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            
            <!-- Referência Comercial 1 -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-700 mb-3">Referência Comercial 1</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        label="Nome da Empresa" 
                        name="referencia_comercial1" 
                        placeholder="Nome da empresa" 
                        :value="old('referencia_comercial1')" 
                        icon="fas fa-building" />
                    
                    <x-input 
                        label="Telefone" 
                        name="telefone_referencia_comercial1" 
                        placeholder="(XX) XXXXX-XXXX" 
                        :value="old('telefone_referencia_comercial1')" 
                        icon="fas fa-phone"
                        mask="telefone" />
                </div>
            </div>

            <!-- Referência Comercial 2 -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-700 mb-3">Referência Comercial 2</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        label="Nome da Empresa" 
                        name="referencia_comercial2" 
                        placeholder="Nome da empresa" 
                        :value="old('referencia_comercial2')" 
                        icon="fas fa-building" />
                    
                    <x-input 
                        label="Telefone" 
                        name="telefone_referencia_comercial2" 
                        placeholder="(XX) XXXXX-XXXX" 
                        :value="old('telefone_referencia_comercial2')" 
                        icon="fas fa-phone"
                        mask="telefone" />
                </div>
            </div>

            <!-- Referência Comercial 3 -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-700 mb-3">Referência Comercial 3</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        label="Nome da Empresa" 
                        name="referencia_comercial3" 
                        placeholder="Nome da empresa" 
                        :value="old('referencia_comercial3')" 
                        icon="fas fa-building" />
                    
                    <x-input 
                        label="Telefone" 
                        name="telefone_referencia_comercial3" 
                        placeholder="(XX) XXXXX-XXXX" 
                        :value="old('telefone_referencia_comercial3')" 
                        icon="fas fa-phone"
                        mask="telefone" />
                </div>
            </div>
        </div>

        <!-- Seção: Foto -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-camera mr-2"></i>Foto do Cliente
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            
            <x-input 
                label="Foto do Cliente" 
                name="foto" 
                type="file"
                accept="image/*"
                icon="fas fa-image"
                help="Selecione uma foto do cliente (formatos: JPG, PNG, GIF)" />
        </div>

        <!-- Seção: Endereço -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-map-marker-alt mr-2"></i>Informações de Endereço
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <x-input 
                        label="Rua" 
                        name="rua" 
                        placeholder="Nome da rua" 
                        :value="old('rua')" 
                        icon="fas fa-road"
                        required />
                </div>
                
                <x-input 
                    label="Número" 
                    name="numero" 
                    type="number"
                    placeholder="Número da casa" 
                    :value="old('numero')" 
                    icon="fas fa-hashtag"
                    required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    label="Bairro" 
                    name="bairro" 
                    placeholder="Bairro" 
                    :value="old('bairro')" 
                    icon="fas fa-map"
                    required />
                
                <x-input 
                    label="Cidade" 
                    name="cidade" 
                    placeholder="Cidade" 
                    :value="old('cidade')" 
                    icon="fas fa-city"
                    required />
            </div>

            <x-input 
                label="Ponto de Referência" 
                name="referencia" 
                placeholder="Ponto de referência da casa" 
                :value="old('referencia')" 
                icon="fas fa-map-pin" />
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-center gap-4">
            <x-button variant="secondary" icon="fas fa-times" href="{{ route('clientes.index') }}">
                Cancelar
            </x-button>
            <x-button type="submit" variant="success" icon="fas fa-save">
                Cadastrar Cliente
            </x-button>
        </div>
    </x-form>

@endsection
