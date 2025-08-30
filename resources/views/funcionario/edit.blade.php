@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Editar Funcionário" 
        subtitle="Atualize as informações do funcionário: {{ $funcionario->name }}" 
        icon="fas fa-user-edit">
        <x-slot name="actions">
            <x-button variant="secondary" icon="fas fa-list" href="{{ route('funcionario.index') }}">
                Listar Funcionários
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-form 
        title="Dados do Funcionário" 
        subtitle="Atualize as informações do funcionário" 
        action="{{ route('funcionario.update', ['id' => $funcionario->id]) }}" 
        method="POST"
        enctype="multipart/form-data"
    >
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input 
                label="Nome" 
                name="name" 
                type="text" 
                placeholder="Nome do funcionário" 
                :value="$funcionario->name" 
                icon="fas fa-user" 
                required="true" 
            />
            
            <x-input 
                label="Login" 
                name="login" 
                type="text" 
                placeholder="Login do usuário" 
                :value="$funcionario->login" 
                icon="fas fa-sign-in-alt" 
                required="true" 
            />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input 
                label="Nova Senha" 
                name="password" 
                type="password" 
                placeholder="Deixe em branco para manter a atual" 
                icon="fas fa-lock" 
                help="Deixe em branco se não quiser alterar a senha"
            />
            
            <x-input 
                label="Cidade" 
                name="cidade" 
                type="select" 
                :options="$cidades->pluck('cidade', 'id')"
                :value="$funcionario->cidade" 
                icon="fas fa-map-marker-alt" 
                required="true" 
            />
            
            <x-input 
                label="Status" 
                name="status" 
                type="select" 
                :options="['ativo' => 'Ativo', 'inativo' => 'Inativo']"
                :value="$funcionario->status" 
                icon="fas fa-toggle-on" 
                required="true" 
            />
        </div>

        <div class="mb-6">
            <x-input 
                label="Foto do Funcionário" 
                name="image" 
                type="file" 
                accept="image/*" 
                icon="fas fa-camera" 
                help="Selecione uma nova foto ou deixe em branco para manter a atual"
            />
            
            @if($funcionario->image)
                <div class="mt-3">
                    <img class="h-32 w-32 object-cover rounded-lg border" 
                         src="{{ asset('storage/uploads/funcionarios/' . $funcionario->image) }}" 
                         alt="{{ $funcionario->name }}">
                    <p class="text-sm text-gray-600 mt-1">Foto atual</p>
                </div>
            @endif
        </div>

        <!-- Seção: Permissões -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fas fa-shield-alt mr-2"></i>Permissões do Sistema
            </h3>
            <div class="h-0.5 bg-gradient-to-r from-yellow-400 to-red-500 rounded-full mb-6"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="cadastro_produtos" type="checkbox" value="1" {{ $funcionario->cadastro_produtos == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900">Cadastro de produtos</span>
                    </label>
                    
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="ajuste_estoque" type="checkbox" value="1" {{ $funcionario->ajuste_estoque == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900">Ajuste de estoque</span>
                    </label>
                    
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="recebimentos" type="checkbox" value="1" {{ $funcionario->recebimentos == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900">Recebimentos</span>
                    </label>
                </div>
                
                <div class="space-y-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="vendas_crediario" type="checkbox" value="1" {{ $funcionario->vendas_crediario == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900">Vendas no crediário</span>
                    </label>
                    
                    <label class="inline-flex items-center cursor-pointer">
                        <input name="limite" type="checkbox" value="1" {{ $funcionario->limite == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900">Ajuste de limite</span>
                    </label>
                </div>
            </div>
        </div>

        <x-slot name="actions">
            <x-button type="submit" variant="primary" icon="fas fa-save">
                Atualizar Funcionário
            </x-button>
            <x-button variant="danger" href="{{ route('funcionario.index') }}" icon="fas fa-times">
                Cancelar
            </x-button>
        </x-slot>
    </x-form>

</div>

@endsection
