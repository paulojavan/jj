@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Marcas" 
        subtitle="Gerencie as marcas de produtos" 
        icon="fas fa-tags">
        <x-slot name="actions">
            <x-button variant="success" icon="fas fa-plus" href="{{ route('marcas.create') }}">
                Cadastrar Marca
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-table :headers="['Marca', 'Ações']">
        @forelse ($marcas as $marca)
            <tr class="table-row">
                <td class="table-cell">{{ $marca->marca }}</td>
                <td class="table-actions">
                    <div class="flex flex-col md:flex-row gap-2">
                        <x-button variant="primary" size="sm" icon="fas fa-edit" 
                                 href="{{ route('marcas.edit', $marca) }}">
                            Editar
                        </x-button>
                        <x-button variant="danger" size="sm" icon="fas fa-trash" 
                                 onclick="confirmDelete({{ $marca->id_marca }})">
                            Excluir
                        </x-button>
                        <form id="delete-form-{{ $marca->id_marca }}" action="{{ route('marcas.destroy', $marca) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr class="table-row">
                <td colspan="2" class="table-cell text-center py-8">
                    <div class="text-gray-500">
                        <i class="fas fa-tags text-4xl mb-2"></i>
                        <p>Nenhuma marca encontrada</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-table>
 <div class="pagination">
        {{ $marcas->links() }}
</div>
</div>

@endsection
