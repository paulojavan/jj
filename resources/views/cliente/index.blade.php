@extends('layouts.base')
@section('content')

<div class="content">
    <x-page-header
        title="Clientes"
        subtitle="Gerencie os clientes da loja"
        icon="fas fa-user-friends">
        <x-slot name="actions">
            <x-button variant="success" icon="fas fa-user-plus" href="{{ route('clientes.create') }}">
                Cadastrar Cliente
            </x-button>
        </x-slot>
    </x-page-header>

    <x-card class="mb-6">
        <form action="{{ route('clientes.index') }}" method="get">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="flex-grow lg:flex-[3]">
                    <x-input
                        name="cliente"
                        placeholder="Pesquisar Cliente"
                        value="{{ request('cliente') }}"
                        icon="fas fa-search"
                        class="w-full text-xl px-8 py-6" />
                </div>
                <div class="lg:flex-[1]">
                    <x-button type="submit" variant="success" icon="fas fa-search" class="w-full px-10 py-6 text-xl">
                        Procurar Cliente
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>

    <x-alert />

    {{-- Container para a visualização em tabela (telas médias e maiores) --}}
    <div class="hidden md:block">
        <x-table :headers="[
            ['label' => 'Foto', 'class' => 'w-32 text-center'],
            ['label' => 'Nome', 'class' => 'w-64 text-center'],
            ['label' => 'Ações', 'class' => 'w-48 text-center']
        ]">
            @forelse ($clientes as $cliente)
                @php
                    $pasta = $cliente->pasta ?? $cliente->cpf;
                @endphp

                <tr class="table-row">
                    <td class="table-cell align-middle text-center">
                        <img class="h-28 w-28 object-cover rounded-lg shadow-sm border border-yellow-200 mx-auto"
                             src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $cliente->foto) }}"
                             alt="{{ $cliente->nome }}">
                    </td>
                    <td class="table-cell align-middle text-center">
                        <div class="font-semibold text-red-600">{{ $cliente->nome }}</div>
                        <div class="text-sm text-gray-500">{{ $cliente->cpf }}</div>
                    </td>
                    <td class="table-actions align-middle">
                        <div class="flex flex-col gap-2">
                            <x-button variant="info" size="sm" icon="fas fa-edit" href="{{ route('clientes.edit', $cliente->id) }}">
                                Editar
                            </x-button>
                            <x-button variant="primary" size="sm" icon="fas fa-history" href="{{ route('clientes.historico.compras', $cliente->id) }}">
                                Histórico
                            </x-button>
                            <x-button variant="primary" size="sm" icon="fas fa-receipt" href="{{ route('clientes.historico.pagamentos', $cliente->id) }}">
                                Pagamentos
                            </x-button>
                            @if($cliente->tem_tickets_negativados ?? false)
                                <x-button variant="secondary" size="sm" icon="fas fa-exclamation-triangle" href="{{ route('pagamentos.show', $cliente->id) }}">
                                    Negativado
                                </x-button>
                            @else
                                <x-button variant="success" size="sm" icon="fas fa-dollar-sign" href="{{ route('pagamentos.show', $cliente->id) }}">
                                    Pagar
                                </x-button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="table-row">
                    <td colspan="3" class="table-cell text-center py-8">
                        <div class="text-gray-500">
                            <i class="fas fa-users text-4xl mb-2"></i>
                            <p>Nenhum cliente encontrado</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-table>
    </div>

    {{-- Container para a visualização em cards (telas pequenas) --}}
    <div class="md:hidden space-y-4">
        @forelse ($clientes as $cliente)
            @php
                $pasta = $cliente->pasta ?? $cliente->cpf;
            @endphp

            <x-card class="text-center">
                {{-- Foto --}}
                <div class="mb-3 flex justify-center">
                    <img class="h-32 w-32 object-cover rounded-full shadow-md border-4 border-yellow-200"
                         src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $cliente->foto) }}"
                         alt="{{ $cliente->nome }}">
                </div>

                {{-- Nome --}}
                <div class="mb-3">
                    <h3 class="font-bold text-red-600 text-lg">{{ $cliente->nome }}</h3>
                    <p class="text-gray-500 text-sm">{{ $cliente->cpf }}</p>
                </div>

                {{-- Ações --}}
                <div class="space-y-2">
                    <x-button variant="info" size="sm" icon="fas fa-edit" href="{{ route('clientes.edit', $cliente->id) }}" class="w-full">
                        Editar
                    </x-button>
                    <x-button variant="primary" size="sm" icon="fas fa-history" href="{{ route('clientes.historico.compras', $cliente->id) }}" class="w-full">
                        Histórico
                    </x-button>
                    <x-button variant="primary" size="sm" icon="fas fa-receipt" href="{{ route('clientes.historico.pagamentos', $cliente->id) }}" class="w-full">
                        Pagamentos
                    </x-button>
                    @if($cliente->tem_tickets_negativados ?? false)
                        <x-button variant="secondary" size="sm" icon="fas fa-exclamation-triangle" href="{{ route('pagamentos.show', $cliente->id) }}" class="w-full">
                            Negativado
                        </x-button>
                    @else
                        <x-button variant="success" size="sm" icon="fas fa-dollar-sign" href="{{ route('pagamentos.show', $cliente->id) }}" class="w-full">
                            Pagar
                        </x-button>
                    @endif
                </div>
            </x-card>
        @empty
            <x-card class="text-center py-8">
                <div class="text-gray-500">
                    <i class="fas fa-users text-6xl mb-4 text-gray-300"></i>
                    <h3 class="text-xl font-semibold mb-2">Nenhum cliente encontrado</h3>
                    <p>Tente ajustar os filtros de busca</p>
                </div>
            </x-card>
        @endforelse
    </div>
    @if ($clientes instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-lg shadow-md border border-yellow-200 p-4">
                {{ $clientes->appends(request()->except('page'))->links() }}
            </div>
        </div>
    @endif
</div>

@endsection
