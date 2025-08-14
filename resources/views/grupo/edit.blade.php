@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Cadastro de grupos</h1>
        </div>

    <x-alert />

    <form action="{{ route('grupos.update', $grupo->id) }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        @method('PUT')
        <div class="mb-4">
        <label class="form-label" for="grupo" >Nome da grupo:</label>
        <input class="form-input" type="text" name="grupo" id="grupo" placeholder="Nome do grupo" value="{{ $grupo->grupo ?? old('grupo') }}" required >
        </div>

        <div class="mb-4 text-center">
        <input class="btn-green" type="submit" value="Atualizar grupo">
        </div>
    </form>

    </div>


@endsection
