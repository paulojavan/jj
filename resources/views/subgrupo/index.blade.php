@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="content-title">
            <h1 class="page-title">Listando todos os sub-grupos</h1>
            <a href=" {{ route('subgrupos.create') }} " class="btn-green">Cadastrar novo sub-grupo</a>
        </div>

    <x-alert />

    <div class="table-container">
        <table class="table">
            <thead>
                <tr class="table-header">
                    <th class="table-header">Subgrupos</th>
                    <th class="table-header center">Ações</th>
                </tr>
            </thead>

            <tbody class="table-body">
                @forelse ($subgrupos as $subgrupo)
                    <tr class="table-row">
                        <td class="table-cell">{{ $subgrupo->subgrupo }}</td>
                        <td class="table-actions">
                        <div class="flex flex-col md:flex-row">
                            <a href="{{ route('subgrupos.edit', ['subgrupo' => $subgrupo->id]) }}" class="btn-blue">Editar</a>
                            <form id="delete-form-{{ $subgrupo->id }}" action="{{ route('subgrupos.destroy', ['subgrupo' => $subgrupo->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" name="excluir" id="excluir" class="btn-red" onclick="cofirmDelete({{ $subgrupo->id }})">Excluir</button>

                            </form>
                        </div>
                        </td>
                    </tr>
                @empty
                    <div class="alert-error">
                        Não há sub-grupos Encontrados
                    </div>
                @endforelse
            </tbody>
        </table>

    </div>
 <div class="pagination">
        {{ $subgrupos->links() }}
</div>
    </div>

@endsection
