@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="{{ request()->route('status') == 'inativo' ? 'Todos os Funcionários' : 'Funcionários Ativos' }}" 
        subtitle="Gerencie os funcionários da empresa"
        icon="fas fa-users">
        <x-slot name="actions">
            @if(request()->route('status') == 'inativo')
                <x-button variant="primary" icon="fas fa-eye" href="{{ route('funcionario.index', 'ativo') }}">
                    Mostrar Ativos
                </x-button>
            @else
                <x-button variant="secondary" icon="fas fa-list" href="{{ route('funcionario.index', 'inativo') }}">
                    Mostrar Todos
                </x-button>
            @endif
            <x-button variant="success" icon="fas fa-user-plus" href="{{ route('funcionario.cadastro') }}">
                Cadastrar
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-table :headers="[
        ['label' => 'Nome', 'class' => 'flex-1'],
        ['label' => 'Ações', 'class' => 'w-48 text-center']
    ]">
        @forelse ($funcionarios as $funcionario)
            <tr class="table-row">
                <td class="table-cell">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-user text-red-600"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-red-600">{{ $funcionario->name }}</div>
                            <div class="text-sm text-gray-500">Funcionário</div>
                        </div>
                    </div>
                </td>
                <td class="table-actions">
                    <div class="flex gap-2 justify-center">
                        <x-button variant="success" size="sm" icon="fas fa-eye" href="#">
                            Detalhes
                        </x-button>
                        <x-button variant="info" size="sm" icon="fas fa-edit" href="{{ route('funcionario.edit', ['id' => $funcionario->id]) }}">
                            Editar
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr class="table-row">
                <td colspan="2" class="table-cell text-center py-8">
                    <div class="text-gray-500">
                        <i class="fas fa-users text-4xl mb-2"></i>
                        <p>Nenhum funcionário encontrado</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-table>

    @if($funcionarios->hasPages())
        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow-md border border-yellow-200 p-4">
                {{ $funcionarios->links() }}
            </div>
        </div>
    @endif
</div>

@endsection
