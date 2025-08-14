<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horario;
use App\Models\Cidade;

class HorarioController extends Controller
{
    public function edit()
    {
        $cidades = Cidade::where('status', 'ativa')->with('horarios')->get();
        return view('horarios.edit', ['cidades' => $cidades]);
    }

    public function update(Request $request)
    {
        $horariosData = $request->input('horarios', []);

        foreach ($horariosData as $id => $data) {
            $horario = Horario::find($id);
            if ($horario) {
                $horario->inicio = $data['inicio'];
                $horario->final = $data['final'];
                $horario->save();
            }
        }

        return redirect()->route('horarios.edit')->with('success', 'Horários atualizados com sucesso!');
    }
}
