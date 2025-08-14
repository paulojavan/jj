@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Cadastro de Autorização</h1>
        </div>

    <x-alert />

    <form action="{{ route('autorizados.store') }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        <h1 class="text-center">Dados pessoais</h1><hr><br>
        <div class="mb-4">
        <label class="form-label" for="nome" >Nome completo:</label>
        <input class="form-input" type="text" name="nome" id="nome" placeholder="Nome do cliente" value="{{ old('nome') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="rg" >Rg:</label>
        <input class="form-input" type="text" name="rg" id="rg" placeholder="RG do cliente" value="{{ old('rg') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="cpf" >CPF:</label>
        <input class="form-input" type="text" name="cpf" id="cpf" placeholder="CPF do cliente" value="{{ old('cpf') }}" >
        </div>

        <input type="hidden" name="cliente_id" value="{{ request()->route('cliente_id') }}">

        <br><h1 class="text-center">Foto:</h1><hr><br>
        <div class="mb-4">
            <label class="form-label" for="foto">Foto da pessoa autorizada:</label>
            <input class="form-file" id="foto" name="foto" type="file" multiple accept="image/*" >
        </div>

        <div class="mb-4 text-center">
            <a class="btn-blue" href="{{ route('clientes.edit', request()->route('cliente_id')) }}">Voltar</a>
        <input class="btn-green" type="submit" value="Cadastrar pessoa autorizada">
        </div>
    </form>

    </div>

@endsection
