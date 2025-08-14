@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Cadastro de Marcas</h1>
        </div>

    <x-alert />

    <form action="{{ route('marcas.update', $marca->id) }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        @method('PUT')
        <div class="mb-4">
        <label class="form-label" for="marca" >Nome da marca:</label>
        <input class="form-input" type="text" name="marca" id="marca" placeholder="Nome da marca" value="{{ $marca->marca ?? old('marca') }}" required >
        </div>

        <div class="mb-4 text-center">
        <input class="btn-green" type="submit" value="Atualizar marca">
        </div>
    </form>

    </div>


@endsection
