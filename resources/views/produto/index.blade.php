@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header 
        title="Produtos" 
        subtitle="Gerencie o catálogo de produtos"
        icon="fas fa-shoe-prints">
        <x-slot name="actions">
            <x-button variant="success" icon="fas fa-plus-circle" href="{{ route('produtos.create') }}">
                Cadastrar Produto
            </x-button>
        </x-slot>
    </x-page-header>

    <x-card class="mb-6">
        <form action="{{ route('produtos.index') }}" method="get">
            <div class="flex flex-col md:flex-row gap-4">
                <x-input 
                    name="produto" 
                    placeholder="Pesquisar produto" 
                    value="{{ request('produto') }}"
                    icon="fas fa-search"
                    class="flex-1" />
                <x-input 
                    type="select"
                    name="filtro" 
                    :options="[
                        'codigo' => 'Código',
                        'marca' => 'Marca', 
                        'nome' => 'Nome'
                    ]"
                    :value="request('filtro', 'codigo')"
                    class="w-full md:w-auto" />
                <x-button type="submit" variant="success" icon="fas fa-search">
                    Procurar Produto
                </x-button>
            </div>
        </form>
    </x-card>

    <x-alert />

    {{-- Container para a visualização em tabela (telas médias e maiores) --}}
    <div class="hidden md:block">
        <x-table :headers="[
            ['label' => 'Foto', 'class' => 'w-24 text-center'],
            ['label' => 'Produto', 'class' => 'flex-1 text-center'],
            ['label' => 'Ações', 'class' => 'w-32 text-center']
        ]">
            @forelse ($produtos as $produto)
                <tr class="table-row">
                    <td class="table-cell align-middle text-center">
                        <img class="h-20 w-20 object-cover rounded-lg shadow-sm border border-yellow-200 mx-auto" 
                             src="{{ asset('storage/uploads/produtos/' . $produto->foto) }}" 
                             alt="{{ $produto->produto }}">
                    </td>
                    <td class="table-cell align-middle text-center">
                        <div class="font-semibold text-red-600">{{ $produto->produto }}</div>
                        <div class="text-sm text-gray-500">Código: {{ $produto->codigo }}</div>
                    </td>
                    <td class="table-actions align-middle">
                        <div class="flex flex-col gap-2">
                            <x-button variant="info" size="sm" icon="fas fa-edit" href="{{ route('produtos.edit', $produto->id) }}">
                                Editar
                            </x-button>
                            @if($produto->pode_excluir)
                                <x-button variant="danger" size="sm" icon="fas fa-trash" 
                                         onclick="confirmDelete({{ $produto->id }})">
                                    Excluir
                                </x-button>
                                <form id="delete-form-{{ $produto->id }}" action="{{ route('produtos.destroy', $produto->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="table-row">
                    <td colspan="3" class="table-cell text-center py-8">
                        <div class="text-gray-500">
                            <i class="fas fa-shoe-prints text-4xl mb-2"></i>
                            <p>Nenhum produto encontrado</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-table>
    </div>

    {{-- Container para a visualização em cards (telas pequenas) --}}
    <div class="md:hidden space-y-4">
        @forelse ($produtos as $produto)
            <x-card class="text-center">
                {{-- Foto --}}
                <div class="mb-4 flex justify-center">
                    <img class="h-32 w-32 object-cover rounded-lg shadow-md border-4 border-yellow-200" 
                         src="{{ asset('storage/uploads/produtos/' . $produto->foto) }}" 
                         alt="{{ $produto->produto }}">
                </div>
                
                {{-- Nome --}}
                <div class="mb-4">
                    <h3 class="font-bold text-red-600 text-lg">{{ $produto->produto }}</h3>
                    <p class="text-gray-500 text-sm">Código: {{ $produto->codigo }}</p>
                </div>
                
                {{-- Ações --}}
                <div class="flex justify-center gap-2">
                    <x-button variant="info" size="sm" icon="fas fa-edit" href="{{ route('produtos.edit', $produto->id) }}">
                        Editar
                    </x-button>
                    @if($produto->pode_excluir)
                        <x-button variant="danger" size="sm" icon="fas fa-trash" 
                                 onclick="confirmDelete({{ $produto->id }}, true)">
                            Excluir
                        </x-button>
                        <form id="delete-form-card-{{ $produto->id }}" action="{{ route('produtos.destroy', $produto->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </x-card>
        @empty
            <x-card class="text-center py-8">
                <div class="text-gray-500">
                    <i class="fas fa-shoe-prints text-6xl mb-4 text-gray-300"></i>
                    <h3 class="text-xl font-semibold mb-2">Nenhum produto encontrado</h3>
                    <p>Tente ajustar os filtros de busca</p>
                </div>
            </x-card>
        @endforelse
    </div>
    @if ($produtos instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow-md border border-yellow-200 p-4">
                {{ $produtos->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    function confirmDelete(id, isCard = false) {
        JJAlert.delete('Excluir Produto?', 'Esta ação não pode ser desfeita e o produto será removido permanentemente.')
            .then((result) => {
                if (result.isConfirmed) {
                    const formId = isCard ? `delete-form-card-${id}` : `delete-form-${id}`;
                    document.getElementById(formId).submit();
                }
            });
    }
</script>
@endpush
