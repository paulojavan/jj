<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DespesaTabira;
use App\Models\DespesaPrincesa;
use App\Models\DespesaTabiraFixa;
use App\Models\DespesaPrincesaFixa;
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

    public function createFixa()
    {
        $user = Auth::user();
        $cidades = [];
        if ($user->nivel === 'administrador') {
            $cidades = Cidade::where('status', 'ativa')->get();
        }
        return view('despesas.create_fixa', compact('cidades'));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->nivel === 'administrador';
        
        // Datas iniciais e finais
        $dataInicial = $request->input('data_inicial');
        $dataFinal = $request->input('data_final');
        
        // Converter para objetos Carbon
        $startDate = $dataInicial ? Carbon::createFromFormat('Y-m-d', $dataInicial) : now()->startOfMonth();
        $endDate = $dataFinal ? Carbon::createFromFormat('Y-m-d', $dataFinal) : now()->endOfMonth();
        
        // Se não houver datas definidas, usar o mês atual
        if (!$dataInicial && !$dataFinal) {
            $dataInicial = now()->startOfMonth()->format('Y-m-d');
            $dataFinal = now()->endOfMonth()->format('Y-m-d');
        }
        
        // Verificar se o intervalo de datas corresponde exatamente ao mês atual
        $isMesAtual = false;
        if ($dataInicial && $dataFinal) {
            $startMesAtual = now()->startOfMonth()->format('Y-m-d');
            $endMesAtual = now()->endOfMonth()->format('Y-m-d');
            $isMesAtual = ($dataInicial === $startMesAtual && $dataFinal === $endMesAtual);
        } else {
            $isMesAtual = true;
        }
        
        // Obter cidades ativas
        $cidadesAtivas = $isAdmin ? Cidade::where('status', 'ativa')->get() : Cidade::where('id', $user->cidade)->get();
        
        // Preparar dados para despesas fixas
        $despesasFixas = [];
        $despesasNormais = [];
        $relatorios = [];
        
        // Se o intervalo de datas corresponde ao mês atual, mostrar despesas fixas
        if ($isMesAtual) {
            foreach ($cidadesAtivas as $cidade) {
                $cidadeNome = strtolower($cidade->cidade);
                $modelFixa = ($cidadeNome === 'tabira') ? DespesaTabiraFixa::class : DespesaPrincesaFixa::class;
                $modelNormal = ($cidadeNome === 'tabira') ? DespesaTabira::class : DespesaPrincesa::class;
                
                $despesasFixas[$cidade->id] = $modelFixa::orderBy('dia')->get();
                
                // Verificar se cada despesa fixa já foi inserida como despesa normal
                foreach ($despesasFixas[$cidade->id] as $despesaFixa) {
                    // Obter o primeiro e último dia do mês da data inicial
                    $primeiroDiaMes = $startDate->copy()->startOfMonth();
                    $ultimoDiaMes = $startDate->copy()->endOfMonth();
                    
                    // Verificar se já existe uma despesa normal com o mesmo número neste mês
                    $despesaNormal = $modelNormal::where('numero', $despesaFixa->numero)
                        ->whereBetween('data', [$primeiroDiaMes, $ultimoDiaMes])
                        ->first();
                    
                    // Adicionar um atributo para indicar se já foi inserida
                    $despesaFixa->ja_inserida = $despesaNormal ? true : false;
                    $despesaFixa->despesa_normal_id = $despesaNormal ? $despesaNormal->id_despesas : null;
                }
            }
        }
        
        // Coletar despesas normais no intervalo de datas
        foreach ($cidadesAtivas as $cidade) {
            $cidadeNome = strtolower($cidade->cidade);
            $modelNormal = ($cidadeNome === 'tabira') ? DespesaTabira::class : DespesaPrincesa::class;
            $modelFixa = ($cidadeNome === 'tabira') ? DespesaTabiraFixa::class : DespesaPrincesaFixa::class;
            
            // Sempre coletar despesas normais no intervalo de datas
            // Usar whereDate para garantir comparação correta de datas
            $despesasNormais[$cidade->id] = $modelNormal::whereDate('data', '>=', $startDate)
                ->whereDate('data', '<=', $endDate)
                ->orderBy('data')
                ->get();
                
            // Adicionar despesas fixas futuras apenas para datas após o final do mês atual
            if ($endDate->gt(now()->endOfMonth())) {
                // Para cada mês no intervalo futuro (começando do mês da data inicial)
                $currentDate = $startDate->copy()->startOfMonth();
                
                while ($currentDate->lte($endDate)) {
                    // Verificar se este mês está após o mês atual
                    if ($currentDate->gt(now()->endOfMonth())) {
                        // Obter todas as despesas fixas
                        $despesasFixasOriginais = $modelFixa::orderBy('dia')->get();
                        
                        foreach ($despesasFixasOriginais as $despesaFixa) {
                            // Criar a data da despesa fixa para o mês atual do loop
                            $dataDespesa = $currentDate->copy()->day($despesaFixa->dia);
                            
                            // Ajustar o dia se não existir no mês
                            if ($despesaFixa->dia > $currentDate->daysInMonth) {
                                $dataDespesa->day($currentDate->daysInMonth);
                            }
                            
                            // Verificar se a data da despesa fixa está no intervalo solicitado (inclusive)
                            // Subtrair 1 dia da data inicial para garantir inclusão correta
                            if ($dataDespesa->gte($startDate->copy()->subDay()) && $dataDespesa->lte($endDate)) {
                                // Criar um objeto simulado para a despesa fixa
                                $despesaFutura = new \stdClass();
                                $despesaFutura->id_despesas = 'fixa_' . $despesaFixa->id_despesas . '_' . $currentDate->format('Ym');
                                $despesaFutura->data = $dataDespesa;
                                $despesaFutura->tipo = $despesaFixa->tipo . ' (Fixa)';
                                $despesaFutura->empresa = $despesaFixa->empresa;
                                $despesaFutura->valor = $despesaFixa->valor;
                                // Manter o status como "pendente" para despesas fixas inseridas
                                $despesaFutura->status = 'pendente';
                                $despesaFutura->pagamento = null;
                                $despesaFutura->is_fixa_futura = true;
                                $despesaFutura->despesa_fixa_id = $despesaFixa->id_despesas;
                                $despesaFutura->mes_referencia = $currentDate->format('m/Y');
                                
                                $despesasNormais[$cidade->id]->push($despesaFutura);
                            }
                        }
                    }
                    
                    // Avançar para o próximo mês
                    $currentDate->addMonth()->startOfMonth();
                }
                
                // Ordenar novamente por data
                $despesasNormais[$cidade->id] = $despesasNormais[$cidade->id]->sortBy('data');
            }
                
            // Calcular relatórios
            $totalDespesas = $despesasNormais[$cidade->id]->sum('valor');
            $totalPago = $despesasNormais[$cidade->id]->where('status', 'Pago')->sum('valor');
            $totalAPagar = $despesasNormais[$cidade->id]->filter(function ($despesa) {
                return $despesa->status === 'pendente' || is_null($despesa->status);
            })->sum('valor');
            
            $relatorios[$cidade->id] = [
                'cidade' => $cidade->cidade,
                'total_despesas' => $totalDespesas,
                'total_pago' => $totalPago,
                'total_a_pagar' => $totalAPagar
            ];
        }
        
        // Se for administrador, calcular relatório geral
        $relatorioGeral = null;
        if ($isAdmin) {
            $totalGeralDespesas = 0;
            $totalGeralPago = 0;
            $totalGeralAPagar = 0;
            
            foreach ($relatorios as $relatorio) {
                $totalGeralDespesas += $relatorio['total_despesas'];
                $totalGeralPago += $relatorio['total_pago'];
                $totalGeralAPagar += $relatorio['total_a_pagar'];
            }
            
            $relatorioGeral = [
                'total_despesas' => $totalGeralDespesas,
                'total_pago' => $totalGeralPago,
                'total_a_pagar' => $totalGeralAPagar
            ];
        }
        
        // Passar as cidades como array associativo para a view
        $cidades = $cidadesAtivas->keyBy('id');
        
        return view('despesas.index', compact(
            'despesasFixas', 
            'despesasNormais', 
            'relatorios', 
            'relatorioGeral', 
            'dataInicial', 
            'dataFinal', 
            'isAdmin', 
            'cidades',
            'isMesAtual',
            'startDate',
            'endDate'
        ));
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

    public function storeFixa(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'dia' => 'required|integer|min:1|max:31',
            'tipo' => 'required|string|max:255',
            'empresa' => 'required|string|max:255',
            'valor' => 'required|string',
            'cidade_id' => 'sometimes|required|exists:cidades,id',
        ]);

        // Limpar o valor monetário
        $valorNumerico = preg_replace('/\D/', '', $validated['valor']);
        $valorFloat = (float) $valorNumerico / 100;

        $user = Auth::user();
        $cidade_id = $request->input('cidade_id', $user->cidade);
        $cidade = strtolower(DB::table('cidades')->where('id', $cidade_id)->value('cidade'));

        // Determinar o model correto com base na cidade
        $model = ($cidade === 'tabira') ? DespesaTabiraFixa::class : DespesaPrincesaFixa::class;

        // Criar a despesa fixa
        $model::create([
            'dia' => $validated['dia'],
            'tipo' => $validated['tipo'],
            'empresa' => $validated['empresa'],
            'numero' => uniqid(), // Gerar um número único para a despesa
            'valor' => $valorFloat,
            'data' => now(), // Inserir a data atual
        ]);

        return redirect()->route('despesas.create.fixa')->with('success', 'Despesa fixa cadastrada com sucesso!');
    }
    
    public function updateFixa(Request $request, $id)
    {
        // Validação dos dados
        $validated = $request->validate([
            'dia' => 'required|integer|min:1|max:31',
            'tipo' => 'required|string|max:255',
            'empresa' => 'required|string|max:255',
            'valor' => 'required|string',
            'cidade_id' => 'required|exists:cidades,id',
        ]);

        // Limpar o valor monetário
        $valorNumerico = preg_replace('/\D/', '', $validated['valor']);
        $valorFloat = (float) $valorNumerico / 100;

        $cidade_id = $validated['cidade_id'];
        $cidade = strtolower(DB::table('cidades')->where('id', $cidade_id)->value('cidade'));

        // Determinar o model correto com base na cidade
        $model = ($cidade === 'tabira') ? DespesaTabiraFixa::class : DespesaPrincesaFixa::class;

        // Atualizar a despesa fixa
        $despesa = $model::findOrFail($id);
        $despesa->update([
            'dia' => $validated['dia'],
            'tipo' => $validated['tipo'],
            'empresa' => $validated['empresa'],
            'valor' => $valorFloat,
        ]);

        return response()->json(['success' => true]);
    }
    
    public function destroyFixa(Request $request, $id)
    {
        $validated = $request->validate([
            'cidade_id' => 'required|exists:cidades,id',
        ]);

        $cidade_id = $validated['cidade_id'];
        $cidade = strtolower(DB::table('cidades')->where('id', $cidade_id)->value('cidade'));

        // Determinar o model correto com base na cidade
        $model = ($cidade === 'tabira') ? DespesaTabiraFixa::class : DespesaPrincesaFixa::class;

        // Excluir a despesa fixa
        $model::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
    
    public function inserirDespesaFixa(Request $request)
    {
        $validated = $request->validate([
            'id_despesa_fixa' => 'required|integer',
            'data' => 'required|date',
            'cidade_id' => 'required|integer',
        ]);
        
        $user = Auth::user();
        $cidade_id = $validated['cidade_id'];
        $cidade = strtolower(DB::table('cidades')->where('id', $cidade_id)->value('cidade'));
        
        // Obter a despesa fixa
        $modelFixa = ($cidade === 'tabira') ? DespesaTabiraFixa::class : DespesaPrincesaFixa::class;
        $despesaFixa = $modelFixa::findOrFail($validated['id_despesa_fixa']);
        
        // Determinar o model normal com base na cidade
        $modelNormal = ($cidade === 'tabira') ? DespesaTabira::class : DespesaPrincesa::class;
        
        // Ajustar a data se o dia não existir no mês
        $data = Carbon::createFromFormat('Y-m-d', $validated['data']);
        $dia = $despesaFixa->dia;
        
        // Verificar se o dia existe no mês
        if ($dia > $data->daysInMonth) {
            $dia = $data->daysInMonth; // Ajustar para o último dia do mês
        }
        
        $data->day = $dia;
        
        // Criar a despesa normal com status "Pago"
        $modelNormal::create([
            'data' => $data,
            'tipo' => $despesaFixa->tipo,
            'empresa' => $despesaFixa->empresa,
            'numero' => $despesaFixa->numero,
            'valor' => $despesaFixa->valor,
            'status' => 'Pago', // Definir status como "Pago" imediatamente
            'pagamento' => null,
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function atualizarDespesa(Request $request)
    {
        $validated = $request->validate([
            'id_despesa' => 'required|integer',
            'tipo' => 'required|string|max:255',
            'empresa' => 'required|string|max:255',
            'valor' => 'required|string',
            'status' => 'required|string|in:pendente,Pago',
            'pagamento' => 'nullable|string|max:255',
            'cidade_id' => 'required|integer',
        ]);
        
        $user = Auth::user();
        $cidade_id = $validated['cidade_id'];
        $cidade = strtolower(DB::table('cidades')->where('id', $cidade_id)->value('cidade'));
        
        // Determinar o model com base na cidade
        $model = ($cidade === 'tabira') ? DespesaTabira::class : DespesaPrincesa::class;
        
        // Limpar o valor monetário
        $valorNumerico = preg_replace('/\D/', '', $validated['valor']);
        $valorFloat = (float) $valorNumerico / 100;
        
        // Atualizar a despesa
        $despesa = $model::findOrFail($validated['id_despesa']);
        $despesa->update([
            'tipo' => $validated['tipo'],
            'empresa' => $validated['empresa'],
            'valor' => $valorFloat,
            'status' => $validated['status'],
            'pagamento' => $validated['pagamento'],
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function excluirDespesa(Request $request)
    {
        $validated = $request->validate([
            'id_despesa' => 'required|integer',
            'cidade_id' => 'required|integer',
        ]);
        
        $user = Auth::user();
        $cidade_id = $validated['cidade_id'];
        $cidade = strtolower(DB::table('cidades')->where('id', $cidade_id)->value('cidade'));
        
        // Determinar o model com base na cidade
        $model = ($cidade === 'tabira') ? DespesaTabira::class : DespesaPrincesa::class;
        
        // Excluir a despesa
        $model::findOrFail($validated['id_despesa'])->delete();
        
        return response()->json(['success' => true]);
    }
}