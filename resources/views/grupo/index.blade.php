@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="content-title">
            <h1 class="page-title">Listando todos os grupos</h1>
            <a href=" {{ route('grupos.create') }} " class="btn-green">Cadastrar novo grupo</a>
        </div>

    <x-alert />

    <div class="table-container">
        <table class="table">
            <thead>
                <tr class="table-header">
                    <th class="table-header">grupos</th>
                    <th class="table-header center">Ações</th>
                </tr>
            </thead>

            <tbody class="table-body">
                @forelse ($grupos as $grupo)
                    <tr class="table-row">
                        <td class="table-cell">{{ $grupo->grupo }}</td>
                        <td class="table-actions">
                            <div class="flex flex-col md:flex-row">
                                <a href="{{ route('grupos.edit', ['grupo' => $grupo->id]) }}" class="btn-blue">Editar</a>
                                <form id="delete-form-{{ $grupo->id }}" action="{{ route('grupos.destroy', ['grupo' => $grupo->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" name="excluir" id="excluir" class="btn-red" onclick="cofirmDelete({{ $grupo->id }})">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <div class="alert-error">
                        Não há grupos Encontrados
                    </div>
                @endforelse
            </tbody>
        </table>

    </div>
 <div class="pagination">
        {{ $grupos->links() }}
</div>
    </div>

@endsection
