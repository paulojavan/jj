@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Cadastro de Grupo</h1>
        </div>

    <x-alert />

    <form action="{{ route('grupos.store') }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        <div class="mb-4">
        <label class="form-label" for="grupo" >Nome do Grupo:</label>
        <input class="form-input" type="text" name="grupo" id="grupo" placeholder="Nome do grupo" value="{{ old('grupo') }}" required >
        </div>

        <div class="mb-4 text-center">
        <input class="btn-green" type="submit" value="Cadastrar novo grupo">
        </div>
    </form>

    </div>


@endsection
