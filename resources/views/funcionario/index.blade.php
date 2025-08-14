@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="content-title">
            @if(request()->route('status') == 'inativo')
            <h1 class="page-title">Listando todos os funcionários</h1>
            @else
            <h1 class="page-title">Listando funcionários ativos</h1>
            @endif


            @if(request()->route('status') == 'inativo')
            <a href=" {{ route('funcionario.index', 'ativo') }} " class="btn-yellow">Mostrar Ativos</a>
            @else
            <a href=" {{ route('funcionario.index', 'inativo') }} " class="btn-yellow">Mostrar todos</a>
            @endif


            <a href=" {{ route('funcionario.cadastro') }} " class="btn-green">Cadastrar</a>
        </div>

    <x-alert />

    <div class="table-container">
        <table class="table">
            <thead>
                <tr class="table-header">
                    <th class="table-header">Nome</th>
                    <th class="table-header center">Ações</th>
                </tr>
            </thead>

            <tbody class="table-body">
                @forelse ($funcionarios as $funcionario)
                    <tr class="table-row">
                        <td class="table-cell">{{ $funcionario->name }}</td>
                        <td class="table-actions">
                            <a href="#" class="btn-green">Detalhes</a>
                            <a href="{{ route('funcionario.edit', ['id' => $funcionario->id]) }}" class="btn-blue">Editar</a>
                        </td>
                    </tr>
                @empty
                    <div class="alert-error">
                        Não há vendedores Encontrados
                    </div>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $funcionarios->links() }}
    </div>

    </div>

@endsection
