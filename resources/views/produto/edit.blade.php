@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Editar - {{ $produto->produto }}</h1>
        </div>

    <x-alert />

    <form action="{{ route('produtos.update', $produto->id) }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        @method('PUT')

        <div class="mb-4">
        <label class="form-label" for="produto" >Produto:</label>
        <input class="form-input" type="text" name="produto" id="produto" placeholder="Produto" value="{{ $produto->produto }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="marca" >Marca:</label>
        <select class="form-input" name="marca" id="marca">
            @foreach ($marcas as $marca)
                <option value="{{ $marca->marca }}" @if($marca->marca == $produto->marca) selected @endif>{{ $marca->marca }}</option>
            @endforeach
        </select>
        </div>

        <div class="mb-4">
            <label class="form-label" for="genero" >Genero:</label>
            <select class="form-input" name="genero" id="genero">
                <option value="masculino" @if($produto->genero == "masculino") selected @endif>Masculino</option>
                <option value="feminino" @if($produto->genero == "feminino") selected @endif>Feminino</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label" for="grupo" >Grupo:</label>
            <select class="form-input" name="grupo" id="grupo">
                @foreach ($grupos as $grupo)
                <option value="{{ $grupo->grupo }}" @if($grupo->grupo == $produto->grupo) selected @endif>{{ $grupo->grupo }}</option>
            @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label" for="subgrupo" >Sub-grupo:</label>
            <select class="form-input" name="subgrupo" id="subgrupo">
                @foreach ($subgrupos as $subgrupo)
                    <option value="{{ $subgrupo->subgrupo }}" @if($subgrupo->subgrupo == $produto->subgrupo) selected @endif>{{ $subgrupo->subgrupo }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label" for="codigo" >Codigo:</label>
            <input class="form-input" type="text" name="codigo" id="codigo" placeholder="Codigo" value="{{ $produto->codigo }}" >
        </div>

        <div class="mb-4">
            <label class="form-label" for="quantidade" >Quantidade:</label>
            <input class="form-input" type="number" name="quantidade" id="quantidade" placeholder="Quantidade" value="{{ $produto->quantidade }}" >
        </div>

        <div class="mb-4 flex flex-col md:flex-row">
            <div class="flex-1">
                <label class="form-label" for="num1" >Número Inicial:</label>
                <select class="form-input" name="num1" id="num1">
                    @for ($i = 14; $i <= $produto->num1; $i++)
                        <option value="{{ $i }}"  @if($i == $produto->num1) selected @endif>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex-1">
                <label class="form-label" for="num2" >Número Final:</label>
                <select class="form-input" name="num2" id="num2">
                    @for ($i = $produto->num2; $i <= 46; $i++)
                        <option value="{{ $i }}" @if($i == $produto->num2) selected @endif>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label" for="preco" >Preço:</label>
            <input class="form-input" type="text" name="preco" id="preco" placeholder="Preço do produto" value="R$ {{ $produto->preco }}" oninput="formatCurrency(this)" >
            </div>

        <div class="mb-4">
            <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/produtos/' . $produto->foto) }}" alt="{{ $produto->produto }}" class="img-preview"><br>
            <label class="form-label" for="foto">Foto do produto:</label>
            <input class="form-file" id="foto" name="foto" type="file" accept="image/*" >
        </div>

        <div class="mb-4 text-center">
        <input class="btn-green" type="submit" value="Alterar produto">
        <a href="{{ route('produtos.distribuicao', $produto->id) }}" class="btn-blue">Distribuir Numerações</a>
        </div>
    </form>

    </div>

@endsection
<script>
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    value = (value / 100).toFixed(2) + '';
    value = value.replace('.', ',');
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = 'R$ ' + value;
}
</script>
