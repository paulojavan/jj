<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DespesaTabira;
use App\Models\DespesaPrincesa;
use App\Models\Cidade;

class DespesaController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $cidades = [];
        if ($user->nivel === 'administrador') {
            $cidades = Cidade::where('status', 'ativa')->get();
        }
        return view('despesas.create', compact('cidades'));
    }

    public function store(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'quantidade' => 'required|integer|min:1',
            'tipo' => 'required|string|in:Boleto,Despeza,Cheque',
            'empresa' => 'required|string|max:255',
            'numero_documento' => 'required|string|max:255',
            'datas' => 'required|array',
            'valores' => 'required|array',
            'cidade_id' => 'sometimes|required|exists:cidades,id',
        ]);

        // Limpar os valores monetários usando a lógica sugerida
        $valoresLimpos = [];
        foreach ($validated['valores'] as $valor) {
            // Remover tudo que não for dígito
            $valorNumerico = preg_replace('/\D/', '', $valor);
            
            // Converter para float dividindo por 100 (considerando centavos)
            $valorFloat = (float) $valorNumerico / 100;
            
            // Garantir que o valor é válido
            $valoresLimpos[] = is_numeric($valorFloat) ? $valorFloat : 0.0;
        }

        $user = Auth::user();
        $cidade_id = $request->input('cidade_id', $user->cidade);
        $cidade = strtolower(DB::table('cidades')->where('id', $cidade_id)->value('cidade'));

        $model = ($cidade === 'tabira') ? DespesaTabira::class : DespesaPrincesa::class;

        $quantidade = $validated['quantidade'];
        $numeroDocumento = $validated['numero_documento'];

        for ($i = 1; $i <= $quantidade; $i++) {
            $numero = $numeroDocumento . "($i de $quantidade)";
            $model::create([
                'data' => $validated['datas'][$i-1],
                'tipo' => $validated['tipo'],
                'empresa' => $validated['empresa'],
                'numero' => $numero,
                'valor' => $valoresLimpos[$i-1],
                'status' => 'pendente',
                'pagamento' => null,
            ]);
        }

        return redirect()->route('despesas.create')->with('success', 'Despesas cadastradas com sucesso!');
    }
}