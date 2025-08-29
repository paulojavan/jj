@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Subgrupos" 
        subtitle="Gerencie os subgrupos de produtos" 
        icon="fas fa-sitemap">
        <x-slot name="actions">
            <x-button variant="success" icon="fas fa-plus" href="{{ route('subgrupos.create') }}">
                Cadastrar Subgrupo
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-table :headers="['Subgrupo', 'Ações']">
        @forelse ($subgrupos as $subgrupo)
            <tr class="table-row">
                <td class="table-cell">{{ $subgrupo->subgrupo }}</td>
                <td class="table-actions">
                    <div class="flex flex-col md:flex-row gap-2">
                        <x-button variant="primary" size="sm" icon="fas fa-edit" 
                                 href="{{ route('subgrupos.edit', ['subgrupo' => $subgrupo->id]) }}">
                            Editar
                        </x-button>
                        <x-button variant="danger" size="sm" icon="fas fa-trash" 
                                 onclick="confirmDelete({{ $subgrupo->id }})">
                            Excluir
                        </x-button>
                        <form id="delete-form-{{ $subgrupo->id }}" action="{{ route('subgrupos.destroy', ['subgrupo' => $subgrupo->id]) }}" method="POST" class="hidden">
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
                        <i class="fas fa-sitemap text-4xl mb-2"></i>
                        <p>Nenhum subgrupo encontrado</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-table>
 <div class="pagination">
        {{ $subgrupos->links() }}
</div>
    </div>

@endsection
