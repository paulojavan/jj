@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Grupos" 
        subtitle="Gerencie os grupos de produtos" 
        icon="fas fa-layer-group">
        <x-slot name="actions">
            <x-button variant="success" icon="fas fa-plus" href="{{ route('grupos.create') }}">
                Cadastrar Grupo
            </x-button>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-table :headers="['Grupo', 'Ações']">
        @forelse ($grupos as $grupo)
            <tr class="table-row">
                <td class="table-cell">{{ $grupo->grupo }}</td>
                <td class="table-actions">
                    <div class="flex flex-col md:flex-row gap-2">
                        <x-button variant="primary" size="sm" icon="fas fa-edit" 
                                 href="{{ route('grupos.edit', ['grupo' => $grupo->id]) }}">
                            Editar
                        </x-button>
                        <x-button variant="danger" size="sm" icon="fas fa-trash" 
                                 onclick="confirmDelete({{ $grupo->id }})">
                            Excluir
                        </x-button>
                        <form id="delete-form-{{ $grupo->id }}" action="{{ route('grupos.destroy', ['grupo' => $grupo->id]) }}" method="POST" class="hidden">
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
                        <i class="fas fa-layer-group text-4xl mb-2"></i>
                        <p>Nenhum grupo encontrado</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-table>
 <div class="pagination">
        {{ $grupos->links() }}
</div>
    </div>

@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    JJAlert.delete({
        title: 'Tem certeza?',
        text: 'Esta ação não pode ser desfeita!',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endpush
