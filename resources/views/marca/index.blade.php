@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="content-title">
            <h1 class="page-title">Listando todos as marcas</h1>
            <a href=" {{ route('marcas.create') }} " class="btn-green">Cadastrar nova marca</a>
        </div>

    <x-alert />

    <div class="table-container">
        <table class="table">
            <thead>
                <tr class="table-header">
                    <th class="table-header">Marca</th>
                    <th class="table-header center">Ações</th>
                </tr>
            </thead>

            <tbody class="table-body">
                @forelse ($marcas as $marca)
                    <tr class="table-row">
                        <td class="table-cell">{{ $marca->marca }}</td>
                        <td class="table-actions">
                        <div class="flex flex-col md:flex-row">
                            <a href="{{ route('marcas.edit', $marca) }}" class="btn-blue">Editar</a>

                            <form id="delete-form-{{ $marca->id_marca }}" action="{{ route('marcas.destroy', $marca) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" name="excluir" id="excluir" class="btn-red" onclick="cofirmDelete({{ $marca->id_marca }})">Excluir</button>
                            </form>
                        </div>
                        </td>
                    </tr>
                @empty
                    <div class="alert-error">
                        Não há marcas Encontrados
                    </div>
                @endforelse
            </tbody>
        </table>

    </div>
 <div class="pagination">
        {{ $marcas->links() }}
</div>
</div>

@endsection
