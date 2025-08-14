@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Cadastro de Subgrupo</h1>
        </div>

    <x-alert />

    <form action="{{ route('subgrupos.store') }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        <div class="mb-4">
        <label class="form-label" for="subgrupo" >Nome da subgrupo:</label>
        <input class="form-input" type="text" name="subgrupo" id="subgrupo" placeholder="Nome da Subgrupo" value="{{ old('subgrupo') }}" required >
        </div>

        <div class="mb-4 text-center">
        <input class="btn-green" type="submit" value="Cadastrar novo subgrupo">
        </div>
    </form>

    </div>


@endsection
