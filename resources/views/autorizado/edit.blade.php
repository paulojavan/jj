@extends('layouts.base')
@section('content')
@if ($autorizado->pasta == null)
    @php
        $cliente = DB::table('clientes')->where('id', $autorizado->idCliente)->first();
        $pasta = $cliente->cpf;
    @endphp
@else
    @php
        $pasta = $autorizado->pasta;
    @endphp
@endif

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Cadastro de Autorização</h1>
        </div>

    <x-alert />

    <form action="{{ route('autorizados.update', ['autorizado' => $autorizado->id]) }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        @method('PUT')
        <h1 class="text-center">Dados pessoais</h1><hr><br>
        <div class="mb-4">
        <label class="form-label" for="nome" >Nome completo:</label>
        <input class="form-input" type="text" name="nome" id="nome" placeholder="Nome do autorizado" value="{{ old('nome', $autorizado->nome) }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="rg" >Rg:</label>
        <input class="form-input" type="text" name="rg" id="rg" placeholder="RG do autorizado" value="{{ old('rg', $autorizado->rg) }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="cpf" >CPF:</label>
        <input class="form-input" type="text" name="cpf" id="cpf" placeholder="CPF do autorizado" value="{{ old('cpf', $autorizado->cpf) }}" >
        </div>

        <input type="hidden" name="autorizado_id" value="{{ request()->route('autorizado_id') }}">

        <br><h1 class="text-center">Fotos:</h1><hr><br>
        <div class="mb-4 text-center">
            <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $autorizado->foto) }}" alt="{{ $autorizado->name }}" class="img-preview"><br>
            @if(@isset($autorizado->foto))
            <button type="button" id="foto" data-modal-target="mudar_fotos" data-modal-toggle="mudar_fotos" class="btn-blue">Alterar foto</button>
            <br>
            @endif
        </div>

        <div class="mb-4 text-center">
            <div class="flex flex-wrap justify-center">
                <div class="w-full md:w-1/3 mb-4">
                    <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $autorizado->rg_frente) }}" alt="{{ $autorizado->name }}" class="img-preview"><br>
                    @if(is_null($autorizado->rg_frente))
                    <button type="button" id="rg_frente" data-modal-target="documentos" data-modal-toggle="documentos" class="btn-blue">RG Frente</button>
                    @else
                    <button type="button" id="rg_frente" data-modal-target="mudar_fotos" data-modal-toggle="mudar_fotos" class="btn-blue">RG Frente</button>
                    @endif
                </div>
                <div class="w-full md:w-1/3 mb-4">
                    <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $autorizado->rg_verso) }}" alt="{{ $autorizado->name }}" class="img-preview"><br>
                    @if(is_null($autorizado->rg_verso))
                    <button type="button" id="rg_verso" data-modal-target="documentos" data-modal-toggle="documentos" class="btn-blue">RG Verso</button>
                    @else
                    <button type="button" id="rg_verso" data-modal-target="mudar_fotos" data-modal-toggle="mudar_fotos" class="btn-blue">RG Verso</button>
                    @endif
                </div>
                <div class="w-full md:w-1/3 mb-4">
                    <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $autorizado->cpf_foto) }}" alt="{{ $autorizado->name }}" class="img-preview"><br>
                    @if(is_null($autorizado->cpf_foto))
                    <button type="button" id="cpf_foto" data-modal-target="documentos" data-modal-toggle="documentos" class="btn-blue">CPF</button>
                    @else
                    <button type="button" id="cpf_foto" data-modal-target="mudar_fotos" data-modal-toggle="mudar_fotos" class="btn-blue">CPF</button>
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-4 text-center">
            <a class="btn-blue" href="{{ route('clientes.edit', $autorizado->idCliente) }}">Voltar</a>
        <input class="btn-green" type="submit" value="Editar pessoa autorizada">
        </div>
    </form>

    </div>


<!-- Carregar imagem-->
<div id="documentos" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-gray-800 rounded-lg shadow-sm dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-xl font-semibold text-white dark:text-white">
                    Enviar foto
                </h3>
                <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="documentos">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-5">
                <form class="space-y-4" action="{{ route('upload_documentos_autorizado', ['id' => $autorizado->id]) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" value="{{ $autorizado->id }}">
                    <input type="hidden" name="documento" value="">
                    <div>
                        <input class="form-file" id="foto" name="foto" type="file" multiple accept="image/*" required >
                    </div>
                    <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Enviar foto</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Alterar imagem-->
<div id="mudar_fotos" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-gray-800 rounded-lg shadow-sm dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-xl font-semibold text-white dark:text-white">
                    Alterar foto
                </h3>
                <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="mudar_fotos">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-5">
                <form class="space-y-4" action="{{ route('alterar_foto_autorizado', ['id' => $autorizado->id]) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" value="{{ $autorizado->id }}">
                    <input type="hidden" name="documento2" value="">
                    <div>
                        <input class="form-file" id="image" name="image" type="file" multiple accept="image/*" required >
                    </div>
                    <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Enviar foto</button>
                </form>
            </div>
        </div>
    </div>
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
document.querySelectorAll('.btn-blue').forEach(function(button) {
    button.addEventListener('click', function() {
        document.querySelector('input[name="documento"]').value = button.id;
        document.querySelector('input[name="documento2"]').value = button.id;
    });

});
</script>


@endsection
