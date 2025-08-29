@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Cadastro de Funcionário" 
        subtitle="Adicione um novo funcionário ao sistema" 
        icon="fas fa-user-plus">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-list" href="{{ route('funcionario.index') }}">
                Listar Funcionários
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form 
        title="Dados do Funcionário" 
        subtitle="Preencha as informações do novo funcionário" 
        action="{{ route('funcionario.store') }}" 
        method="POST" 
        enctype="multipart/form-data"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input 
                label="Nome" 
                name="name" 
                type="text" 
                placeholder="Nome do funcionário" 
                :value="old('name')" 
                icon="fas fa-user" 
                required="true" 
            />
            
            <x-input 
                label="Login" 
                name="login" 
                type="text" 
                placeholder="Login do usuário" 
                :value="old('login')" 
                icon="fas fa-sign-in-alt" 
                required="true" 
            />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input 
                label="Senha" 
                name="password" 
                type="password" 
                placeholder="Senha do usuário" 
                icon="fas fa-lock" 
                required="true" 
            />
            
            <x-input 
                label="Cidade" 
                name="cidade" 
                type="select" 
                :options="$cidades->pluck('cidade', 'id')" 
                icon="fas fa-map-marker-alt" 
                required="true" 
            />
        </div>
        
        <x-input 
            label="Foto" 
            name="image" 
            type="file" 
            accept="image/*" 
            icon="fas fa-camera" 
            required="true" 
            help="Selecione uma foto para o funcionário" 
        />

        <!-- Seção: Permissões -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-shield-alt mr-2"></i>Permissões do Sistema
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="cadastro_produtos" type="checkbox" value="1" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900">Cadastro de produtos</span>
                    </label>
                    
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="ajuste_estoque" type="checkbox" value="1" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900">Ajuste de estoque</span>
                    </label>
                </div>
                
                <div class="space-y-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="vendas_crediario" type="checkbox" value="1" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900">Vendas no crediário</span>
                    </label>
                    
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="limite" type="checkbox" value="1" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900">Ajuste de limite</span>
                    </label>
                    
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="recebimentos" type="checkbox" value="1" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900">Recebimentos</span>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3">
            <a href="{{ route('funcionario.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Cancelar
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Cadastrar Funcionário
            </button>
        </div>
    </x-form>

</div>

@endsection
