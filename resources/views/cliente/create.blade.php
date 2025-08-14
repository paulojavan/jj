@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Cadastro de Clientes</h1>
        </div>

    <x-alert />

    <form action="{{ route('clientes.store') }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        <h1 class="text-center">Dados pessoais</h1><hr><br>
        <div class="mb-4">
        <label class="form-label" for="nome" >Nome completo:</label>
        <input class="form-input" type="text" name="nome" id="nome" placeholder="Nome do cliente" value="{{ old('nome') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="apelido" >Apelido:</label>
        <input class="form-input" type="text" name="apelido" id="apelido" placeholder="Apelido do cliente" value="{{ old('apelido') }}">
        </div>

        <div class="mb-4">
        <label class="form-label" for="rg" >Rg:</label>
        <input class="form-input" type="text" name="rg" id="rg" placeholder="RG do cliente" value="{{ old('rg') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="cpf" >CPF:</label>
        <input class="form-input" type="text" name="cpf" id="cpf" placeholder="CPF do cliente" value="{{ old('cpf') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="mae">Nome da mãe:</label>
        <input class="form-input" type="text" name="mae" id="mae" placeholder="Nome da mãe" value="{{ old('mae') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="pai">Nome do pai:</label>
        <input class="form-input" type="text" name="pai" id="pai" placeholder="Nome do pai" value="{{ old('pai') }}">
        </div>

        <div class="mb-4">
        <label class="form-label" for="telefone">Telefone do cliente:</label>
        <input class="form-input telefone-mask" type="text" name="telefone" id="telefone" placeholder="(XX) XXXXX-XXXX" value="{{ old('telefone') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="nascimento">Data de nascimento:</label>
        <input class="form-input" type="date" name="nascimento" id="nascimento" value="{{ old('nascimento') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="renda">Fonte de renda:</label>
        <input class="form-input" type="text" name="renda" id="renda" placeholder="Fonte de renda do cliente" value="{{ old('renda') }}" >
        </div>

        <br><h1 class="text-center">Dados da referencia</h1><hr><br>
        <div class="mb-4">
        <label class="form-label" for="nome_referencia" >Nome da referencia:</label>
        <input class="form-input" type="text" name="nome_referencia" id="nome_referencia" placeholder="Nome da pessoa de referencia" value="{{ old('nome') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="telefone_referencia">Telefone da referencia:</label>
        <input class="form-input telefone-mask" type="text" name="telefone_referencia" id="telefone_referencia" placeholder="(XX) XXXXX-XXXX" value="{{ old('telefone_referencia') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="parentesco">Parentesco:</label>
        <input class="form-input" type="text" name="parentesco" id="parentesco" placeholder="Gráu de parentesco com o cliente" value="{{ old('parentesco') }}" >
        </div>

        <br><h1 class="text-center">Referencias comerciais:</h1><hr><br>
        <div class="mb-4">
        <label class="form-label" for="referencia_comercial1">Referencia comercial 1:</label>
        <input class="form-input" type="text" name="referencia_comercial1" id="referencia_comercial1" placeholder="Referencia comercial" value="{{ old('referencia_comercial1') }}">
        </div>
        <div class="mb-4">
        <label class="form-label" for="telefone_referencia_comercial1">Telefone da referencia comercial 1:</label>
        <input class="form-input telefone-mask" type="text" name="telefone_referencia_comercial1" id="telefone_referencia_comercial1" placeholder="(XX) XXXXX-XXXX" value="{{ old('telefone_referencia_comercial1') }}">
        </div><br>

        <div class="mb-4">
        <label class="form-label" for="referencia_comercial2">Referencia comercial 2:</label>
        <input class="form-input" type="text" name="referencia_comercial2" id="referencia_comercial2" placeholder="Referencia comercial" value="{{ old('referencia_comercial2') }}">
        </div>
        <div class="mb-4">
        <label class="form-label" for="telefone_referencia_comercial2">Telefone da referencia comercial 2:</label>
        <input class="form-input telefone-mask" type="text" name="telefone_referencia_comercial2" id="telefone_referencia_comercial2" placeholder="(XX) XXXXX-XXXX" value="{{ old('telefone_referencia_comercial2') }}">
        </div><br>

        <div class="mb-4">
        <label class="form-label" for="referencia_comercial3">Referencia comercial 3:</label>
        <input class="form-input" type="text" name="referencia_comercial3" id="referencia_comercial3" placeholder="Referencia comercial" value="{{ old('referencia_comercial3') }}">
        </div>
        <div class="mb-4">
        <label class="form-label" for="telefone_referencia_comercial3">Telefone da referencia comercial 3:</label>
        <input class="form-input telefone-mask" type="text" name="telefone_referencia_comercial3" id="telefone_referencia_comercial3" placeholder="(XX) XXXXX-XXXX" value="{{ old('telefone_referencia_comercial3') }}">
        </div>

        <br><h1 class="text-center">Foto:</h1><hr><br>
        <div class="mb-4">
            <label class="form-label" for="foto">Foto do cliente:</label>
            <input class="form-file" id="foto" name="foto" type="file" multiple accept="image/*" >
        </div>

        <br><h1 class="text-center">Informaçoes de endereço:</h1><hr><br>
        <div class="mb-4">
        <label class="form-label" for="rua">Rua:</label>
        <input class="form-input" type="text" name="rua" id="rua" placeholder="Nome da rua" value="{{ old('rua') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="numero">Número:</label>
        <input class="form-input" type="number" name="numero" id="numero" placeholder="Numero da casa" value="{{ old('numero') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="bairro">Bairro:</label>
        <input class="form-input" type="text" name="bairro" id="bairro" placeholder="Bairro" value="{{ old('bairro') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="referencia">Ponto de referencia:</label>
        <input class="form-input" type="text" name="referencia" id="referencia" placeholder="Ponro de referencia da casa" value="{{ old('referencia') }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="cidade">Cidade:</label>
        <input class="form-input" type="text" name="cidade" id="cidade" placeholder="Cidade" value="{{ old('cidade') }}" >
        </div>

        <div class="mb-4 text-center">
        <input class="btn-green" type="submit" value="Cadastrar cliente">
        </div>
    </form>

    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Seleciona todos os campos com a classe telefone-mask
        const telefoneInputs = document.querySelectorAll('.telefone-mask');

        // Adiciona o evento de input para cada campo de telefone
        telefoneInputs.forEach(function(input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value;

                // Remove todos os caracteres não numéricos
                value = value.replace(/\D/g, '');

                // Limita a 11 dígitos (telefone brasileiro com DDD)
                if (value.length > 11) {
                    value = value.slice(0, 11);
                }

                // Aplica a máscara de telefone (XX) XXXXX-XXXX
                if (value.length > 0) {
                    // Adiciona o parêntese no início
                    value = '(' + value;

                    // Fecha o parêntese após o DDD (2 dígitos)
                    if (value.length > 3) {
                        value = value.substring(0, 3) + ') ' + value.substring(3);
                    }

                    // Adiciona o hífen após o quinto dígito do número
                    if (value.length > 10) {
                        value = value.substring(0, 10) + '-' + value.substring(10);
                    }
                }

                e.target.value = value;
            });

            // Formata o valor inicial se existir
            if (input.value) {
                const event = new Event('input');
                input.dispatchEvent(event);
            }
        });
    });
</script>

@endsection
