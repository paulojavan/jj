@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="content-title">
            <h1 class="page-title">Editar Funcionário</h1>
            <a href=" {{ route('funcionario.index') }} " class="btn-yellow">Listar</a>
        </div>

    <x-alert />

    <form action="{{ route('funcionario.update', ['id' => $funcionario->id]) }}" method="post" enctype="multipart/form-data" class="form-container">
        @csrf
        @method('PUT')

        <div class="mb-4">
        <label class="form-label" for="name" >Nome:</label>
        <input class="form-input" type="text" name="name" id="name" placeholder="Nome do vendedor" value="{{ old('name', $funcionario->name) }}" required>
        </div>

        <div class="mb-4">
        <label class="form-label" for="login">Login:</label>
        <input class="form-input" type="text" name="login" id="login" placeholder="login do usuário" value="{{ old('login', $funcionario->login) }}" required>
        </div>

        <div class="mb-4">
            <div class="mb-4">
                <label class="form-label" for="password">Senha:</label>
                <input class="form-input" type="password" name="password" id="password" placeholder="senha">
            </div>

            <div class="mb-4">
                <label class="form-label" for="cidade">Cidade:</label>
                <select class="form-input" name="cidade" id="cidade">

                    @foreach ($cidades as $cidade)
                        <option value="{{ $cidade->cidade }}" @if ($funcionario->cidade == $cidade->id) selected @endif>
                            {{ $cidade->cidade }}
                        </option>
                    @endforeach

                </select>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="cidade">Status:</label>
                    <select class="form-input" name="status" id="status">
                        <option value="ativo" @if ($funcionario->status == "ativo") selected @endif>Ativo</option>
                        <option value="inativo" @if ($funcionario->status == "inativo") selected @endif>Inativo</option>
                    </select>
                </div>

                <div class="mb-4">
                    <img class="h-auto max-w-xs" src="{{ asset('storage/uploads/funcionarios/' . $funcionario->image) }}" alt="{{ $funcionario->name }}" class="img-preview">
                </div>

                <div class="mb-4">
                    <label class="form-label" for="image">Foto:</label>
                    <input class="form-file" id="image" name="image" type="file" accept="image/*">
                </div>

                <div class="mb-4">
                <label class="inline-flex items-center mb-5 cursor-pointer">
                    <input name="cadastro_produtos" type="checkbox" value="1" class="sr-only peer"
                    @if ($funcionario->cadastro_produtos == '1') checked @endif>
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-lime-600 dark:peer-checked:bg-lime-600"></div>
                    <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Cadastro de produtos</span>
                  </label>
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center mb-5 cursor-pointer">
                        <input name="ajuste_estoque" type="checkbox" value="1" class="sr-only peer"
                        @if ($funcionario->ajuste_estoque == '1') checked @endif>
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-lime-600 dark:peer-checked:bg-lime-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Ajuste de estoque</span>
                      </label>
                    </div>

                    <div class="mb-4">
                        <label class="inline-flex items-center mb-5 cursor-pointer">
                            <input name="vendas_crediario" type="checkbox" value="1" class="sr-only peer"
                            @if ($funcionario->vendas_crediario == '1') checked @endif>
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-lime-600 dark:peer-checked:bg-lime-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Vendas no crediário</span>
                          </label>
                        </div>

                        <div class="mb-4">
                            <label class="inline-flex items-center mb-5 cursor-pointer">
                                <input name="limite" type="checkbox" value="1" class="sr-only peer"
                                @if ($funcionario->limite == '1') checked @endif>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-lime-600 dark:peer-checked:bg-lime-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Ajuste de limite</span>
                              </label>
                            </div>

                            <div class="mb-4">
                                <label class="inline-flex items-center mb-5 cursor-pointer">
                                    <input name="recebimentos" type="checkbox" value="1" class="sr-only peer"
                                    @if ($funcionario->recebimentos == '1') checked @endif>
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-lime-600 dark:peer-checked:bg-lime-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Recebimentos</span>
                                  </label>
                                </div>

        <input class="btn-blue" type="submit" value="Editar">
    </form>

    </div>

@endsection
