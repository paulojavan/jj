@extends('layouts.base')
@section('content')


@if ($cliente->pasta == null)
    @php
        $pasta = $cliente->cpf;
    @endphp
@else
    @php
        $pasta = $cliente->pasta;
    @endphp
@endif

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Editar cadastro</h1>
        </div>

    <x-alert />

    <form action="{{ route('clientes.update', ['cliente' => $cliente->id]) }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        @method('PUT')
        <h1 class="text-center">Dados pessoais</h1><hr><br>
        <div class="mb-4">
        <label class="form-label" for="nome" >Nome completo:</label>
        <input class="form-input" type="text" name="nome" id="nome" placeholder="Nome do cliente" value="{{ $cliente->nome }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="apelido" >Apelido:</label>
        <input class="form-input" type="text" name="apelido" id="apelido" placeholder="Apelido do cliente" value="{{ $cliente->apelido }}">
        </div>

        <div class="mb-4">
        <label class="form-label" for="rg" >Rg:</label>
        <input class="form-input" type="text" name="rg" id="rg" placeholder="RG do cliente" value="{{ $cliente->rg }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="cpf" >CPF:</label>
        <input class="form-input" type="text" name="cpf" id="cpf" placeholder="CPF do cliente" value="{{ $cliente->cpf }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="mae">Nome da mãe:</label>
        <input class="form-input" type="text" name="mae" id="mae" placeholder="Nome da mãe" value="{{ $cliente->mae }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="pai">Nome do pai:</label>
        <input class="form-input" type="text" name="pai" id="pai" placeholder="Nome do pai" value="{{ $cliente->pai }}">
        </div>

        <div class="mb-4">
        <label class="form-label" for="telefone">Telefone do cliente:</label>
        <input class="form-input telefone-mask" type="text" name="telefone" id="telefone" placeholder="(XX) XXXXX-XXXX" value="{{ $cliente->telefone }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="nascimento">Data de nascimento:</label>
        <input class="form-input" type="date" name="nascimento" id="nascimento" value="{{ $cliente->nascimento }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="renda">Fonte de renda:</label>
        <input class="form-input" type="text" name="renda" id="renda" placeholder="Fonte de renda do cliente" value="{{ $cliente->renda }}" >
        </div>

        <br><h1 class="text-center">Dados da referencia</h1><hr><br>
        <div class="mb-4">
        <label class="form-label" for="nome_referencia" >Nome da referencia:</label>
        <input class="form-input" type="text" name="nome_referencia" id="nome_referencia" placeholder="Nome da pessoa de referencia" value="{{ $cliente->nome_referencia }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="telefone_referencia">Telefone da referencia:</label>
        <input class="form-input telefone-mask" type="text" name="telefone_referencia" id="telefone_referencia" placeholder="(XX) XXXXX-XXXX" value="{{ $cliente->numero_referencia }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="parentesco">Parentesco:</label>
        <input class="form-input" type="text" name="parentesco" id="parentesco" placeholder="Gráu de parentesco com o cliente" value="{{ $cliente->parentesco_referencia }}" >
        </div>

        <br><h1 class="text-center">Referencias comerciais:</h1><hr><br>
        <div class="mb-4">
        <label class="form-label" for="referencia_comercial1">Referencia comercial 1:</label>
        <input class="form-input" type="text" name="referencia_comercial1" id="referencia_comercial1" placeholder="Referencia comercial" value="{{ $cliente->referencia_comercial1 }}">
        </div>
        <div class="mb-4">
        <label class="form-label" for="telefone_referencia_comercial1">Telefone da referencia comercial 1:</label>
        <input class="form-input telefone-mask" type="text" name="telefone_referencia_comercial1" id="telefone_referencia_comercial1" placeholder="(XX) XXXXX-XXXX" value="{{ $cliente->telefone_referencia_comercial1 }}">
        </div><br>

        <div class="mb-4">
        <label class="form-label" for="referencia_comercial2">Referencia comercial 2:</label>
        <input class="form-input" type="text" name="referencia_comercial2" id="referencia_comercial2" placeholder="Referencia comercial" value="{{ $cliente->referencia_comercial2 }}">
        </div>
        <div class="mb-4">
        <label class="form-label" for="telefone_referencia_comercial2">Telefone da referencia comercial 2:</label>
        <input class="form-input telefone-mask" type="text" name="telefone_referencia_comercial2" id="telefone_referencia_comercial2" placeholder="(XX) XXXXX-XXXX" value="{{ $cliente->telefone_referencia_comercial2 }}">
        </div><br>

        <div class="mb-4">
        <label class="form-label" for="referencia_comercial3">Referencia comercial 3:</label>
        <input class="form-input" type="text" name="referencia_comercial3" id="referencia_comercial3" placeholder="Referencia comercial" value="{{ $cliente->referencia_comercial3 }}">
        </div>
        <div class="mb-4">
        <label class="form-label" for="telefone_referencia_comercial3">Telefone da referencia comercial 3:</label>
        <input class="form-input telefone-mask" type="text" name="telefone_referencia_comercial3" id="telefone_referencia_comercial3" placeholder="(XX) XXXXX-XXXX" value="{{ $cliente->telefone_referencia_comercial3 }}">
        </div>

        <br><h1 class="text-center">Fotos:</h1><hr><br>
        <div class="mb-4 text-center">
            <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $cliente->foto) }}" alt="{{ $cliente->name }}" class="img-preview"><br>
            @if(@isset($cliente->foto))
            <button type="button" id="foto" data-modal-target="mudar_fotos" data-modal-toggle="mudar_fotos" class="btn-blue">Alterar foto</button>
            <br>
            @endif
        </div>

        <div class="mb-4 text-center">
            <div class="flex flex-wrap justify-center">
                <div class="w-full md:w-1/3 mb-4">
                    <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $cliente->rg_frente) }}" alt="{{ $cliente->name }}" class="img-preview"><br>
                    @if(is_null($cliente->rg_frente))
                    <button type="button" id="rg_frente" data-modal-target="documentos" data-modal-toggle="documentos" class="btn-blue">RG Frente</button>
                    @else
                    <button type="button" id="rg_frente" data-modal-target="mudar_fotos" data-modal-toggle="mudar_fotos" class="btn-blue">RG Frente</button>
                    @endif
                </div>
                <div class="w-full md:w-1/3 mb-4">
                    <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $cliente->rg_verso) }}" alt="{{ $cliente->name }}" class="img-preview"><br>
                    @if(is_null($cliente->rg_verso))
                    <button type="button" id="rg_verso" data-modal-target="documentos" data-modal-toggle="documentos" class="btn-blue">RG Verso</button>
                    @else
                    <button type="button" id="rg_verso" data-modal-target="mudar_fotos" data-modal-toggle="mudar_fotos" class="btn-blue">RG Verso</button>
                    @endif
                </div>
                <div class="w-full md:w-1/3 mb-4">
                    <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $cliente->cpf_foto) }}" alt="{{ $cliente->name }}" class="img-preview"><br>
                    @if(is_null($cliente->cpf_foto))
                    <button type="button" id="cpf_foto" data-modal-target="documentos" data-modal-toggle="documentos" class="btn-blue">CPF</button>
                    @else
                    <button type="button" id="cpf_foto" data-modal-target="mudar_fotos" data-modal-toggle="mudar_fotos" class="btn-blue">CPF</button>
                    @endif
                </div>
            </div>
        </div>

        <br><h1 class="text-center">Autorizados a comprar:</h1><hr><br>

    @foreach($cliente->autorizados as $autorizado)
    @if (!is_null($autorizado->rg_frente) && !is_null($autorizado->rg_verso) && !is_null($autorizado->cpf_foto))
        <div class="border rounded-lg mx-auto">
            <div class="mb-4 ml-4 mt-4">
                <div class="form-label">
                    Nome completo:<br>
                    {{ $autorizado->nome }}
                </div>
            </div>
            <div class="mb-4 ml-4 mt-4 flex flex-col md:flex-row"">
                <div class="form-label flex-1">
                    RG:<br>
                    {{ $autorizado->rg }}
                </div>
                <br>
                <div class="form-label flex-1">
                    CPF:<br>
                    {{ $autorizado->cpf }}
                </div>
            </div>
            <br><h1 class="text-center">Fotos:</h1><hr><br>
            <div class="mb-4 text-center">
                <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $autorizado->foto) }}" alt="{{ $autorizado->name }}" class="img-preview"><br>
            </div>
            <div class="mb-4 text-center">
                <div class="flex flex-wrap justify-center">
                    <div class="w-full md:w-1/3 mb-4">
                        <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $autorizado->rg_frente) }}" alt="{{ $autorizado->name }}" class="img-preview"><br>
                    </div>
                    <div class="w-full md:w-1/3 mb-4">
                        <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $autorizado->rg_verso) }}" alt="{{ $autorizado->name }}" class="img-preview"><br>
                    </div>
                    <div class="w-full md:w-1/3 mb-4">
                        <img class="h-auto max-w-xs mx-auto" src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $autorizado->cpf_foto) }}" alt="{{ $autorizado->name }}" class="img-preview"><br>
                    </div>
                </div>
            </div>
            <div class="mb-4 text-center">
                <a href="{{ route('autorizados.edit', ['autorizado' => $autorizado->id]) }}" class="btn-yellow">Editar</a>
            </div>
        </div>
    @else
        <br>
        <div class="mb-4 text-center">
            <a href="{{ route('autorizados.edit', ['autorizado' => $autorizado->id]) }}" class="btn-yellow">Fotos pendentes</a>
        </div>
    @endif
@endforeach

        @if($cliente->autorizados->count() < 3)
        <br>
            <div class="mb-4 text-center ">
                <a href="{{ route('autorizados.create', ['cliente_id' => $cliente->id]) }}" class="btn-blue">Cadastrar pessoa autorizada</a>
            </div>
        @endif



        <br><h1 class="text-center">Informaçoes de endereço:</h1><hr><br>
        <div class="mb-4">
        <label class="form-label" for="rua">Rua:</label>
        <input class="form-input" type="text" name="rua" id="rua" placeholder="Nome da rua" value="{{ $cliente->rua }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="numero">Número:</label>
        <input class="form-input" type="number" name="numero" id="numero" placeholder="Numero da casa" value="{{ $cliente->numero }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="bairro">Bairro:</label>
        <input class="form-input" type="text" name="bairro" id="bairro" placeholder="Bairro" value="{{ $cliente->bairro }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="referencia">Ponto de referencia:</label>
        <input class="form-input" type="text" name="referencia" id="referencia" placeholder="Ponro de referencia da casa" value="{{ $cliente->referencia }}" >
        </div>

        <div class="mb-4">
        <label class="form-label" for="cidade">Cidade:</label>
        <input class="form-input" type="text" name="cidade" id="cidade" placeholder="Cidade" value="{{ $cliente->cidade }}" >
        </div>

        <div class="mb-4">
            <label class="form-label" for="obs">Observação:</label>
            <input class="form-input" type="text" name="obs" id="obs" placeholder="observação" value="{{ $cliente->obs }}" >
            </div>

        <div class="mb-4 text-center">
        <button class="btn-green" type="submit">Atualizar informações</button>
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
                <form class="space-y-4" action="{{ route('upload_documentos', ['id' => $cliente->id]) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" value="{{ $cliente->id }}">
                    <input type="hidden" name="cpf" value="{{ $cliente->cpf }}">
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
                <form class="space-y-4" action="{{ route('alterar_foto_cliente', ['id' => $cliente->id]) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id" value="{{ $cliente->id }}">
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
