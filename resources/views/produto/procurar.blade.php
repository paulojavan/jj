@extends('layouts.base')
@section('content')

    <div class="content text-center">
        <div class="mb-4">
            <form action="{{ route('produtos.procurar') }}" method="get" class="form-container">
            <div class="flex flex-col md:flex-row gap-4">
                <input class="form-input flex-1" type="text" name="produto" id="produto" placeholder="Pesquisar produto" value="{{ request('produto') }}">
                <select name="filtro" id="filtro" class="form-input w-full md:w-auto">
                    <option value="codigo" {{ request('filtro') == 'codigo' ? 'selected' : '' }}>Código</option>
                    <option value="marca" {{ request('filtro') == 'marca' ? 'selected' : '' }}>Marca</option>
                    <option value="nome" {{ request('filtro') == 'nome' ? 'selected' : '' }}>Nome</option>
                </select>
                <button type="submit" class="btn-green w-full md:w-auto">Procurar produto</button>
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
                @forelse ($produtos as $produto)
                    <tr class="table-row">
                        <td class="table-cell align-middle text-center">
                            @php
                                // Busca o nome da cidade usando o ID da cidade do usuário
                                $cidadeDoUsuario = DB::table('cidades')->where('id', Auth::user()->cidade)->first();
                                $nomeCidadeFormatado = '';
                                if ($cidadeDoUsuario) {
                                    $nomeCidadeFormatado = strtolower(str_replace(' ', '_', $cidadeDoUsuario->cidade));
                                }
                                $nomeTabelaEstoque = 'estoque_' . $nomeCidadeFormatado;
                                $estoqueTotal = false; // Inicializa $estoqueTotal

                                // Verifica se existe estoque para qualquer numeração, apenas se o nome da tabela foi formado e a tabela existe
                                if ($nomeCidadeFormatado && !empty($nomeCidadeFormatado) && \Illuminate\Support\Facades\Schema::hasTable($nomeTabelaEstoque)) {
                                    $estoqueTotal = DB::table($nomeTabelaEstoque)
                                        ->where('id_produto', $produto->id)
                                        ->where('quantidade', '>', 0)
                                        ->exists();
                                }

                                $classeImagem = $estoqueTotal ? 'h-60 max-w-xs img-preview' : 'h-60 max-w-xs img-preview grayscale';
                            @endphp
                            {{-- Certifique-se que $produto->foto contém apenas o nome do arquivo --}}
                            <img class="{{ $classeImagem }}" src="{{ asset('storage/uploads/produtos/' . $produto->foto) }}" alt="{{ $produto->produto }}">
                            @if(!$estoqueTotal)
                                <div class="text-red-500 text-sm mt-2">Sem estoque disponível</div>
                            @endif
                        </td>
                        {{-- Exibindo nome e código do produto --}}
                        <td class="table-cell align-middle text-center">{{ $produto->produto }}<br>Código: {{ $produto->codigo }}</td>
                        <td class="table-actions align-middle text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('produtos.exibir', $produto->id) }}" class="btn-blue">Visualizar informações</a>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="3" class="table-cell text-center">Nenhum produto encontrado</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Container para a visualização em cards (telas pequenas) --}}
    <div class="md:hidden space-y-4">
        @forelse ($produtos as $produto)
            <div class="border rounded-lg p-4 flex flex-col items-center shadow">
                {{-- Foto --}}
                <div class="mb-2 flex justify-center">
                    @php
                        // Busca o nome da cidade usando o ID da cidade do usuário
                        $cidadeDoUsuarioCard = DB::table('cidades')->where('id', Auth::user()->cidade)->first();
                        $nomeCidadeFormatadoCard = '';
                        if ($cidadeDoUsuarioCard) {
                            $nomeCidadeFormatadoCard = strtolower(str_replace(' ', '_', $cidadeDoUsuarioCard->cidade));
                        }
                        $nomeTabelaEstoqueCard = 'estoque_' . $nomeCidadeFormatadoCard;
                        $estoqueTotal = false; // Inicializa $estoqueTotal, que será usado por $classeImagem

                        // Verifica se existe estoque para qualquer numeração, apenas se o nome da tabela foi formado e a tabela existe
                        if ($nomeCidadeFormatadoCard && !empty($nomeCidadeFormatadoCard) && \Illuminate\Support\Facades\Schema::hasTable($nomeTabelaEstoqueCard)) {
                            $estoqueTotal = DB::table($nomeTabelaEstoqueCard)
                                ->where('id_produto', $produto->id)
                                ->where('quantidade', '>', 0)
                                ->exists();
                        }

                        $classeImagem = $estoqueTotal ? 'h-24 w-24 object-cover mx-auto' : 'h-24 w-24 object-cover mx-auto grayscale';
                    @endphp
                    {{-- Certifique-se que $produto->foto contém apenas o nome do arquivo --}}
                    <img class="{{ $classeImagem }}" src="{{ asset('storage/uploads/produtos/' . $produto->foto) }}" alt="{{ $produto->produto }}" style="width: 50%; height: auto;">
                    @if(!$estoqueTotal)
                        <div class="text-red-500 text-sm mt-2">Sem estoque disponível</div>
                    @endif
                </div>
                {{-- Nome --}}
                <div class="mb-2 text-center font-semibold">
                    {{ $produto->produto }}
                </div>
                 {{-- Código --}}
                 <div class="mb-2 text-center text-sm text-gray-600">
                    Código: {{ $produto->codigo }}
                </div>
                {{-- Ações --}}
                <div class="mt-auto flex justify-center gap-2">
                    <a href="{{ route('produtos.exibir', $produto->id) }}" class="btn-blue">Visualizar produto</a>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-4">
                Nenhum produto encontrado
            </div>
        @endforelse
    </div>
    <div class="pagination">
        @if ($produtos instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{-- Usar query() para manter os parâmetros de busca na paginação --}}
        {{ $produtos->appends(request()->query())->links() }}
        @endif
    </div>
    </div>

@endsection
