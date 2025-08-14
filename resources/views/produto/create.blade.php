@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Cadastro de Produtos</h1>
        </div>

    <x-alert />

    <form action="{{ route('produtos.store') }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf

        <div class="mb-4">
        <label class="form-label" for="produto" >Produto:</label>
        <input class="form-input" type="text" name="produto" id="produto" placeholder="Produto" value="{{ old('produto') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="marca" >Marca:</label>
        <select class="form-input" name="marca" id="marca">
            @foreach ($marcas as $marca)
                <option value="{{ $marca->marca }}">{{ $marca->marca }}</option>
            @endforeach
        </select>
        </div>

        <div class="mb-4">
            <label class="form-label" for="genero" >Genero:</label>
            <select class="form-input" name="genero" id="genero">
                <option value="masculino">Masculino</option>
                <option value="feminino">Feminino</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label" for="grupo" >Grupo:</label>
            <select class="form-input" name="grupo" id="grupo">
                @foreach ($grupos as $grupo)
                <option value="{{ $grupo->grupo }}">{{ $grupo->grupo }}</option>
            @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label" for="subgrupo" >Sub-grupo:</label>
            <select class="form-input" name="subgrupo" id="subgrupo">
                @foreach ($subgrupos as $subgrupo)
                    <option value="{{ $subgrupo->subgrupo }}">{{ $subgrupo->subgrupo }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label" for="codigo" >Codigo:</label>
            <input class="form-input" type="text" name="codigo" id="codigo" placeholder="Codigo" value="{{ old('codigo') }}" >
        </div>

        <div class="mb-4">
            <label class="form-label" for="quantidade" >Quantidade:</label>
            <input class="form-input" type="number" name="quantidade" id="quantidade" placeholder="Quantidade" value="{{ old('quantidade') }}" >
        </div>

        <div class="mb-4 flex flex-col md:flex-row">
            <div class="flex-1">
                <label class="form-label" for="num1" >Número Inicial:</label>
                <select class="form-input" name="num1" id="num1">
                    @for ($i = 14; $i <= 46; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex-1">
                <label class="form-label" for="num2" >Número Final:</label>
                <select class="form-input" name="num2" id="num2">
                    @for ($i = 14; $i <= 46; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label" for="preco" >Preço:</label>
            <input class="form-input" type="text" name="preco" id="preco" placeholder="Preço do produto" value="{{ old('preco') }}" oninput="formatCurrency(this)" >
            </div>

        <div class="mb-4">
            <label class="form-label" for="foto">Foto do produto:</label>
            <input class="form-file" id="foto" name="foto" type="file" multiple accept="image/*" >
        </div>



        <div class="mb-4 text-center">
        <input class="btn-green" type="submit" value="Cadastrar produto">
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
