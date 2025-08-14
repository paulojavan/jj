@extends('layouts.base')
@section('content')

    <div class="content p-6">
        <x-alert />
        <div class="flex flex-col md:flex-row gap-6">
            {{-- Coluna da Imagem --}}
            <div class="md:w-1/3">
                {{-- Assumindo que a imagem está disponível em $produto->imagem_url ou similar --}}
                {{-- Se a imagem for salva localmente, ajuste o src --}}
                <img src="{{  asset('storage/uploads/produtos/'.$produto->foto) }}" alt="Imagem do Produto {{ $produto->nome ?? 'Produto' }}" class="w-full h-auto object-cover rounded shadow-lg">
                {{-- Adicione aqui a logo da loja se necessário, posicionada sobre a imagem --}}
            </div>

            {{-- Coluna de Detalhes --}}
            <div class="md:w-2/3 space-y-3 text-gray-700">
                <h1 class="text-3xl font-bold text-gray-800">{{ $produto->produto  }}</h1>
                <p><span class="font-semibold">Marca:</span> {{ $produto->marca ?? 'Marca não disponível' }}</p>
                <p><span class="font-semibold">Gênero:</span> {{ ucfirst($produto->genero ?? 'Gênero não disponível') }}</p>
                <p><span class="font-semibold">Grupo:</span> {{ $produto->grupo ?? 'Grupo não disponível' }}</p>
                <p><span class="font-semibold">Sub-Grupo:</span> {{ $produto->subgrupo ?? 'Sub-Grupo não disponível' }}</p>
                <p><span class="font-semibold">Código:</span> {{ $produto->codigo ?? 'Código não disponível' }}</p>
                <p><span class="font-semibold">Quantidade:</span> {{ $produto->quantidade ?? '0' }}</p>
                <p class="text-2xl font-semibold text-green-600"><span class="font-semibold">Preço:</span> R$ {{ number_format($produto->preco ?? 0, 2, ',', '.') }}</p>
            </div>
        </div>

        {{-- Seção de Cidades Ativas --}}
        <div class="mt-8 text-center">
            @if(isset($cidades) && $cidades->count() > 0)

                    <form action="{{ route('fazerDistribuicao', ['id' => $produto->id]) }}" method="post">
                    @csrf
                    @foreach($cidades as $cidade)
                    @php
                        // Sanitizar o nome da cidade para usar no nome da tabela
                        $nomeTabelaCidade = 'estoque_' . preg_replace('/[^a-zA-Z0-9_]/', '', strtolower($cidade->cidade));
                    @endphp
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4"> Estoque - {{ $cidade->cidade }}</h2>
                    {{-- Loop para exibir números de estoque --}}
                    <div class="flex flex-wrap gap-2 justify-center mb-4">
                        @for ($i = $produto->num1 ?? 1; $i <= ($produto->num2 ?? $produto->quantidade ?? 0); $i++)
                            @php
                                // Busca o registro de estoque para o número e produto atuais
                                $estoqueRegistro = DB::table($nomeTabelaCidade)
                                                    ->where('numero', $i)
                                                    ->where('id_produto', $produto->id)
                                                    ->first(); // Busca o registro completo
                            @endphp
                            <div class="flex flex-col">
                            <label for="quantidade_{{ $cidade->cidade }}_{{ $i }}" class="block text-sm font-medium text-gray-700">Número {{ $i }}</label>

                            @if($estoqueRegistro)
                                <input type="hidden" name="estoque_id_{{ $cidade->cidade }}_{{ $i }}" id="estoque_id_{{ $cidade->cidade }}_{{ $i }}" value="{{ $estoqueRegistro->id }}">
                            @endif
                                <select name="quantidade_{{ $cidade->cidade }}_{{ $i }}" class="inline-block bg-gray-200 rounded px-2 py-1 text-sm font-semibold text-gray-700 w-20 text-center">
                                @for ($j = 0; $j <= ($produto->quantidade ?? 0); $j++)
                                    <option value="{{ $j }}" {{ (old('quantidade_'. $cidade->cidade .'_'. $i) == $j || ($estoqueRegistro && $estoqueRegistro->quantidade == $j)) ? 'selected' : '' }}>{{ $j }}</option>
                                @endfor
                                </select>
                            </div>
                        @endfor
                    </div>
                    @endforeach
                    <input type="submit" value="Distribuir" class="btn-green">
                    <a href="{{ route('produtos.edit', $produto->id) }}" class="btn-blue">Editar produto</a>
                </form>

            @else
                <p class="text-gray-500">Nenhuma cidade ativa encontrada para distribuição.</p>
            @endif
        </div>
    </div>

@endsection
