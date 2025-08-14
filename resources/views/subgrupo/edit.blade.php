@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Cadastro de subgrupos</h1>
        </div>

    <x-alert />

    <form action="{{ route('subgrupos.update', $subgrupo->id) }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        @method('PUT')
        <div class="mb-4">
        <label class="form-label" for="subgrupo" >Nome da subgrupo:</label>
        <input class="form-input" type="text" name="subgrupo" id="subgrupo" placeholder="Nome da subgrupo" value="{{ $subgrupo->subgrupo ?? old('subgrupo') }}" required >
        </div>

        <div class="mb-4 text-center">
        <input class="btn-green" type="submit" value="Atualizar subgrupo">
        </div>
    </form>

    </div>


@endsection
