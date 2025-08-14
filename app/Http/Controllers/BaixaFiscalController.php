<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TabelaDinamica;

class BaixaFiscalController extends Controller
{
    public function index()
    {
        // Obter cidades ativas (removendo duplicatas)
        $cidades = Cidade::where('status', 'ativa')
                         ->get()
                         ->unique('cidade');
        
        // Para cada cidade, buscar as vendas com baixa_fiscal = false
        $vendasPorCidade = [];
        
        foreach ($cidades as $cidade) {
            $nomeTabelaVendas = TabelaDinamica::vendas($cidade->cidade);
            
            // Verificar se a tabela existe antes de consultar
            if (DB::getSchemaBuilder()->hasTable($nomeTabelaVendas)) {
                // Buscar vendas com baixa_fiscal = false
                $vendas = DB::table($nomeTabelaVendas)
                    ->where('baixa_fiscal', false)
                    ->join('produtos', 'produtos.id', '=', $nomeTabelaVendas . '.id_produto')
                    ->select(
                        $nomeTabelaVendas . '.id_vendas as id',
                        'produtos.produto as nome_produto',
                        'produtos.codigo as codigo_produto'
                    )
                    ->get();
                
                // Adicionar apenas se houver vendas
                if ($vendas->count() > 0) {
                    $vendasPorCidade[$cidade->cidade] = $vendas;
                }
            }
        }
        
        return view('baixa_fiscal.index', compact('vendasPorCidade'));
    }
    
    public function darBaixa(Request $request, $cidade, $idVenda)
    {
        try {
            $nomeTabelaVendas = TabelaDinamica::vendas($cidade);
            
            // Verificar se a tabela existe
            if (!DB::getSchemaBuilder()->hasTable($nomeTabelaVendas)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabela de vendas não encontrada para a cidade: ' . $cidade
                ]);
            }
            
            // Obter informações do produto antes de atualizar
            $venda = DB::table($nomeTabelaVendas)
                ->where('id_vendas', $idVenda)
                ->join('produtos', 'produtos.id', '=', $nomeTabelaVendas . '.id_produto')
                ->select(
                    'produtos.codigo as codigo_produto',
                    'produtos.produto as nome_produto'
                )
                ->first();
            
            if (!$venda) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venda não encontrada.'
                ]);
            }
            
            // Atualizar o campo baixa_fiscal para true
            DB::table($nomeTabelaVendas)
                ->where('id_vendas', $idVenda)
                ->update(['baixa_fiscal' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Baixa fiscal realizada com sucesso para o produto ' . $venda->codigo_produto . ' - ' . $venda->nome_produto . '!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar baixa fiscal: ' . $e->getMessage()
            ]);
        }
    }
}