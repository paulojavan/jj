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
        method="PUT"
        enctype="multipart/form-data"
    >
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

        <x-form-section title="Permissões do Sistema" subtitle="Configure as permissões de acesso do funcionário">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <x-input 
                    label="Cadastro de Produtos" 
                    name="cadastro_produtos" 
                    type="checkbox" 
                    value="1" 
                    :checked="$funcionario->cadastro_produtos == '1'" 
                    help="Permite cadastrar e editar produtos"
                />
                
                <x-input 
                    label="Ajuste de Estoque" 
                    name="ajuste_estoque" 
                    type="checkbox" 
                    value="1" 
                    :checked="$funcionario->ajuste_estoque == '1'" 
                    help="Permite ajustar quantidades em estoque"
                />
                
                <x-input 
                    label="Vendas no Crediário" 
                    name="vendas_crediario" 
                    type="checkbox" 
                    value="1" 
                    :checked="$funcionario->vendas_crediario == '1'" 
                    help="Permite realizar vendas a prazo"
                />
                
                <x-input 
                    label="Ajuste de Limite" 
                    name="limite" 
                    type="checkbox" 
                    value="1" 
                    :checked="$funcionario->limite == '1'" 
                    help="Permite alterar limites de crédito"
                />
                
                <x-input 
                    label="Recebimentos" 
                    name="recebimentos" 
                    type="checkbox" 
                    value="1" 
                    :checked="$funcionario->recebimentos == '1'" 
                    help="Permite registrar recebimentos"
                />
            </div>
        </x-form-section>

        <x-slot name="actions">
            <x-button type="submit" variant="primary" icon="fas fa-save">
                Atualizar Funcionário
            </x-button>
            <x-button variant="secondary" href="{{ route('funcionario.index') }}" icon="fas fa-times">
                Cancelar
            </x-button>
        </x-slot>
    </x-form>

</div>

@endsection
