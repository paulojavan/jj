@extends('layouts.base')
@section('content')

    <div class="content">
        <div class="text-center">
            <h1 class="page-title">Atualizar Horários</h1>
        </div>

    <x-alert />
<br>
<form method="POST" action="{{ route('horarios.update') }}">
    @csrf
    @method('PUT')
    @foreach($cidades as $cidade)
        <h2 class="page-title text-center">{{ $cidade->cidade }}</h2>

                @foreach($cidade->horarios as $horario)

                        <div class="horario-field">
                            <p>{{ $horario->dia }}</p>
                        </div>
                        <div class="horario-field" style="display: flex; justify-content: space-between;">
                            <div style="width: 50%;">
                                <label for="inicio">Hora de Início:</label>
                                <input type="time" name="horarios[{{ $horario->id }}][inicio]" value="{{ $horario->inicio }}" class="form-input">
                            </div>
                            <div style="width: 50%;">
                                <label for="final">Hora de Término:</label>
                                <input type="time" name="horarios[{{ $horario->id }}][final]" value="{{ $horario->final }}" class="form-input">
                            </div>
                        </div><br>
                @endforeach
    <br><br>
    @endforeach
<div class="text-center">
<button type="submit" class="btn-green">Atualizar horários</button>
</div>
</form>

</div>


@endsection
