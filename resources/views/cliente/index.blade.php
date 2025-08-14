@extends('layouts.base')
@section('content')

    <div class="content text-center">
        <div class="mb-4">
            <form action="{{ route('clientes.index') }}" method="get" class="form-container">
            <div class="flex flex-col md:flex-row gap-4">
                <input class="form-input flex-1" type="text" name="cliente" id="cliente" placeholder="Pesquisar Cliente" value="{{ request('cliente') }}">
                <button type="submit" class="btn-green w-full md:w-auto">Procurar cliente</button>
            </div>
            </form>
        </div>

    <x-alert />

    {{-- Container para a visualização em tabela (telas médias e maiores) --}}
    <div class="hidden md:block table-container">
        <table class="table w-full">
            <thead class="table-header-group">
                <tr class="table-header">
                    <th class="table-header w-24 text-center">Foto</th>
                    <th class="table-header flex-1 text-center">Nome</th>
                    <th class="table-header w-24 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="table-body">
                @forelse ($clientes as $cliente)

                    @if ($cliente->pasta == null)
                        @php
                            $pasta = $cliente->cpf;
                        @endphp
                    @else
                        @php
                            $pasta = $cliente->pasta;
                        @endphp
                    @endif

                    <tr class="table-row">
                        <td class="table-cell align-middle text-center">
                            <img class="h-60 max-w-xs img-preview" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $cliente->foto) }}" alt="{{ $cliente->name }}">
                        </td>
                        <td class="table-cell align-middle text-center">{{ $cliente->nome }}<br>{{ $cliente->cpf }}</td>
                        <td class="table-actions align-middle text-center">
                            <div class="space-y-2">
                                <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn-green block">Editar</a>
                                <a href="{{ route('clientes.historico.compras', $cliente->id) }}" class="btn-blue block">
                                    <i class="fas fa-history mr-1"></i>Histórico compras
                                </a>
                                <a href="{{ route('clientes.historico.pagamentos', $cliente->id) }}" class="btn-green block mt-2">
                                    <i class="fas fa-receipt mr-1"></i>Histórico pagamentos
                                </a>
                                <a href="{{ route('pagamentos.show', $cliente->id) }}" class="btn-yellow block mt-2">Pagar</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="3" class="table-cell text-center">Nenhum cliente encontrado</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Container para a visualização em cards (telas pequenas) --}}
    <div class="md:hidden space-y-4">
        @forelse ($clientes as $cliente)

        @if ($cliente->pasta == null)
                        @php
                            $pasta = $cliente->cpf;
                        @endphp
                    @else
                        @php
                            $pasta = $cliente->pasta;
                        @endphp
                    @endif


            <div class="border rounded-lg p-4 flex flex-col items-center shadow">
                {{-- Foto --}}
                <div class="mb-2 flex justify-center">
                    <img class="h-24 w-24 object-cover mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $cliente->foto) }}" alt="{{ $cliente->name }}" style="width: 50%; height: auto;">
                </div>
                {{-- Nome --}}
                <div class="mb-2 text-center font-semibold">
                    {{ $cliente->nome }}
                </div>
                {{-- Ações --}}
                <div class="mt-auto space-y-2">
                    <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn-green block w-full text-center">Editar</a>
                    <a href="{{ route('clientes.historico.compras', $cliente->id) }}" class="btn-blue block w-full text-center">
                        <i class="fas fa-history mr-1"></i>Histórico
                    </a>
                    <a href="{{ route('clientes.historico.pagamentos', $cliente->id) }}" class="btn-green block w-full text-center mt-2">
                        <i class="fas fa-receipt mr-1"></i>Pagamentos
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-4">
                Nenhum cliente encontrado
            </div>
        @endforelse
    </div>
    <div class="pagination">
        @if ($clientes instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $clientes->appends(request()->except('page'))->links() }}
        @endif
    </div>
    </div>

@endsection
