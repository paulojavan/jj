<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Marca;
use App\Models\Grupo;
use App\Models\Subgrupo;
use App\Models\Cidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $produtos = collect(); // Cria uma coleção vazia por padrão
        if ($request->has('produto')) { // Só executa a busca se o formulário foi enviado
            $query = Produto::query();

            if ($request->filled('produto') && $request->filled('filtro')) {
                $termo = $request->input('produto');
                $filtro = $request->input('filtro');

                switch ($filtro) {
                    case 'codigo':
                        $query->where('codigo', 'like', '%' . $termo . '%');
                        break;
                    case 'marca':
                        $query->where('marca', 'like', '%' . $termo . '%');
                        break;
                    case 'nome':
                        // Assumindo que a coluna para o nome do produto é 'produto'
                        $query->where('produto', 'like', '%' . $termo . '%');
                        break;
                    // Adicione mais casos se houver outros filtros
                }
            }

            $produtos = $query->orderBy('produto')->paginate(10); // Adiciona ordenação e paginação

            // Busca todas as cidades ativas
            $cidadesAtivas = Cidade::where('status', 'ativa')->get();

            // Verifica para cada produto se existem vendas relacionadas em alguma cidade ativa
            foreach ($produtos as $produto) {
                $produto->pode_excluir = true; // Assume que pode excluir por padrão
                foreach ($cidadesAtivas as $cidade) {
                    $nomeTabelaVendas = 'vendas_' . strtolower(str_replace(' ', '_', $cidade->cidade)); // Constrói o nome da tabela dinamicamente
                    if (DB::table($nomeTabelaVendas)->where('id_produto', $produto->id)->exists()) {
                        $produto->pode_excluir = false; // Encontrou venda, não pode excluir
                        break; // Sai do loop interno de cidades, já que encontrou uma venda
                    }
                }
            }
        }

        return view('produto.index', ['produtos' => $produtos]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $marcas = Marca::orderBy('marca')->get();
        $grupos = Grupo::orderBy('grupo')->get();
        $subgrupos = Subgrupo::orderBy('subgrupo')->get();
        return view('produto.create', compact('marcas', 'grupos', 'subgrupos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $existingProduct = Produto::where('codigo', $request->codigo)->exists();
        if ($existingProduct) {
            return redirect()->back()->withErrors(['codigo' => 'O código do produto já está cadastrado.'])->withInput();
        }
        $validatedData = $request->validate([
            'produto' => 'required|string|max:255',
            'marca' => 'required|exists:marcas,marca',
            'genero' => 'required|string',
            'grupo' => 'required|exists:grupos,grupo',
            'subgrupo' => 'required|exists:subgrupos,subgrupo',
            'codigo' => 'required|string|max:255',
            'quantidade' => 'required|integer',
            'num1' => 'required|integer',
            'num2' => 'required|integer',
            'preco' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $validatedData['preco'] = (float) preg_replace('/\D/', '', $validatedData['preco']) / 100;
        $file_name = $request->codigo.time().'.'.$request->file('foto')->extension();
        $validatedData['foto'] = $file_name;

        Produto::create($validatedData);

        $manager = new ImageManager(new Driver());
        if ($request->hasFile('foto')) {
            $image = $manager->read($request->file('foto'));
            $image->resize(800, 550);
            $path = 'uploads/produtos/'.$file_name;
            Storage::disk('public')->put($path, $image->toJpeg(85));
        }

        // Busca o produto recém-criado pelo código para obter o ID
        $produto = Produto::where('codigo', $request->codigo)->firstOrFail();

        return redirect()->route('produtos.distribuicao', ['id' => $produto->id])->with('success', 'Produto cadastrado com sucesso!');
    }


    public function procurar(Request $request)
    {
        $produtos = collect(); // Cria uma coleção vazia por padrão
        if ($request->has('produto')) { // Só executa a busca se o formulário foi enviado
            $query = Produto::query();

            if ($request->filled('produto') && $request->filled('filtro')) {
                $termo = $request->input('produto');
                $filtro = $request->input('filtro');

                switch ($filtro) {
                    case 'codigo':
                        $query->where('codigo', 'like', '%' . $termo . '%');
                        break;
                    case 'marca':
                        $query->where('marca', 'like', '%' . $termo . '%');
                        break;
                    case 'nome':
                        // Assumindo que a coluna para o nome do produto é 'produto'
                        $query->where('produto', 'like', '%' . $termo . '%');
                        break;
                    // Adicione mais casos se houver outros filtros
                }
            }

            $produtos = $query->orderBy('produto')->paginate(10); // Adiciona ordenação e paginação

        }

        return view('produto.procurar', ['produtos' => $produtos]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Produto $produto)
    {
        //return view('produto.show', compact('produto', 'diasSemana'));
    }

    public function exibir(Produto $produto)
    {
        $quantidadeEmEstoque = 0;
        $salesHistory = collect();
        $availableSizes = collect();
        
        if (Auth::check()) {
            $user = Auth::user();
            // Assumindo que o usuário tem uma relação 'cidade' ou um campo 'cidade_id'
            // e que a tabela de cidades tem um campo 'cidade' com o nome da cidade.
            // Se a estrutura for diferente, ajuste conforme necessário.
            if ($user->cidade) { // Verifica se o usuário tem uma cidade associada
                $nomeCidade = strtolower(str_replace(' ', '_', $user->cidade)); // Ajuste para o nome do campo correto
                $nomeTabelaEstoque = 'estoque_' . $nomeCidade;

                // Verifica se a tabela de estoque da cidade existe
                if (DB::getSchemaBuilder()->hasTable($nomeTabelaEstoque)) {
                    $quantidadeEmEstoque = DB::table($nomeTabelaEstoque)
                        ->where('id_produto', $produto->id)
                        ->where('quantidade', '>', 0)
                        ->sum('quantidade');
                }
                
                // Get sales history and available sizes
                $salesHistory = $this->getSalesHistory($produto->id);
                $availableSizes = $this->getAvailableSizes($produto->id);
            }
        }
        return view('produto.exibir', compact('produto', 'quantidadeEmEstoque', 'salesHistory', 'availableSizes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produto $produto)
    {
        $marcas = Marca::orderBy('marca')->get();
        $grupos = Grupo::orderBy('grupo')->get();
        $subgrupos = Subgrupo::orderBy('subgrupo')->get();
        return view('produto.edit', compact('produto', 'marcas', 'grupos', 'subgrupos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produto $produto)
    {
        $validatedData = $request->validate([
            'produto' => 'required|string|max:255',
            'marca' => 'required|exists:marcas,marca',
            'genero' => 'required|string',
            'grupo' => 'required|exists:grupos,grupo',
            'subgrupo' => 'required|exists:subgrupos,subgrupo',
            'codigo' => 'required|string|max:255|unique:produtos,codigo,' . $produto->id, // Ignora o próprio produto na validação unique
            'quantidade' => 'required|integer',
            'num1' => 'required|integer',
            'num2' => 'required|integer',
            'preco' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $validatedData['preco'] = (float) preg_replace('/\D/', '', $validatedData['preco']) / 100;

        if ($request->hasFile('foto')) {
            // Deleta a foto antiga se existir
            if ($produto->foto && Storage::disk('public')->exists('uploads/produtos/' . $produto->foto)) {
                Storage::disk('public')->delete('uploads/produtos/' . $produto->foto);
            }

            // Processa a nova foto
            $file_name = $request->codigo . time() . '.' . $request->file('foto')->extension();
            $validatedData['foto'] = $file_name;

            $manager = new ImageManager(new Driver());
            $image = $manager->read($request->file('foto'));
            $image->resize(800, 550);
            $path = 'uploads/produtos/' . $file_name;
            Storage::disk('public')->put($path, $image->toJpeg(85));
        } else {
            // Mantém a foto existente se nenhuma nova for enviada
            unset($validatedData['foto']);
        }

        $produto->update($validatedData);

        return redirect()->back()->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        // Busca todas as cidades ativas
        $cidadesAtivas = Cidade::where('status', 'ativa')->get();

        // Verifica se existem vendas relacionadas em alguma cidade ativa
        foreach ($cidadesAtivas as $cidade) {
            $nomeTabelaVendas = 'vendas_' . strtolower(str_replace(' ', '_', $cidade->cidade));
            if (DB::table($nomeTabelaVendas)->where('id_produto', $produto->id)->exists()) {
                return redirect()->route('produtos.index')->with('error', 'Este produto não pode ser excluído pois possui vendas registradas.');
            }
        }

        // Deleta a foto associada se existir
        if ($produto->foto && Storage::disk('public')->exists('uploads/produtos/' . $produto->foto)) {
            Storage::disk('public')->delete('uploads/produtos/' . $produto->foto);
        }

        // Exclui o produto
        $produto->delete();

        return redirect()->back()->with('success', 'Produto excluído com sucesso!');
    }

    public function distribuicao($id)
    {
        $produto = Produto::findOrFail($id); // Busca o produto pelo ID ou falha se não encontrar
        // Você pode adicionar mais lógica aqui se necessário, como carregar relacionamentos
        // $produto->load('relacionamento1', 'relacionamento2');

        $cidades = Cidade::where('status', 'ativa')->orderBy('cidade', 'asc')->get();
        return view('produto.distribuicao', compact('produto', 'cidades')); // Retorna a view passando o produto e as cidades ativas
    }

    public function processarDistribuicao(Request $request, $id) {
        $produto = Produto::findOrFail($id);
        $quantidade = $produto->quantidade;
        $somaQuantidade = 0;
        $cidadesAtivas = Cidade::where('status', 'ativa')->get();
        // Utilize $cidadesAtivas conforme necessário na lógica de distribuição
        foreach ($cidadesAtivas as $cidade) {
            for ($i = $produto->num1; $i <= $produto->num2; $i++) {
                $somaQuantidade += $request->input('quantidade_' . $cidade->cidade .'_'. $i);
                // $request->input('quantidade_' . $cidade->cidade .'_'. $i);
            }
        }
        if ($somaQuantidade != $quantidade) {
            return redirect()->back()->withInput()->with('error', 'A soma das quantidades não corresponde à quantidade total do produto.');
        }

        foreach ($cidadesAtivas as $cidade) {

            for ($i = $produto->num1; $i <= $produto->num2; $i++) {
                $estoqueId = $request->input('estoque_id_' . $cidade->cidade . '_' . $i);
                $numero = $i;
                $quantidade = $request->input('quantidade_'. $cidade->cidade.'_'. $i);
                $idProduto = $id;

                // Lógica para inserir na tabela estoque_$cidade->cidade
                $nomeTabela = 'estoque_' . $cidade->cidade;
                $model = 'Estoque'.ucfirst($cidade->cidade);

                if (!$estoqueId) {
                    \DB::table($nomeTabela)->insert([
                        'id_produto' => $idProduto,
                        'quantidade' => $quantidade,
                        'numero' => $numero,
                    ]);
                }else{
                    \DB::table($nomeTabela)->where('id', $estoqueId)->update([
                        'id_produto' => $idProduto,
                        'numero' => $numero,
                        'quantidade' => $quantidade,
                    ]);
                }

            }
        }
        return redirect()->back()->with('success', 'Distrubuição feita com sucesso!');;

    }

    /**
     * Get sales history for a product in the seller's city
     */
    public function getSalesHistory($productId)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->cidade) {
                return collect();
            }

            // Get city name from user's city ID
            $cidade = DB::table('cidades')->where('id', $user->cidade)->first();
            if (!$cidade) {
                return collect();
            }

            $nomeCidadeFormatado = strtolower(str_replace(' ', '_', $cidade->cidade));
            $salesTable = 'vendas_' . $nomeCidadeFormatado;

            // Check if sales table exists
            if (!DB::getSchemaBuilder()->hasTable($salesTable)) {
                \Log::warning("Sales table {$salesTable} does not exist for city {$cidade->cidade}");
                return collect();
            }

            // Get last 30 sales with seller information
            $salesHistory = DB::table($salesTable)
                ->leftJoin('users', $salesTable . '.id_vendedor', '=', 'users.id')
                ->select([
                    $salesTable . '.id_vendas as id',
                    $salesTable . '.id_vendedor',
                    $salesTable . '.data_venda',
                    $salesTable . '.valor_dinheiro',
                    $salesTable . '.valor_pix',
                    $salesTable . '.valor_cartao',
                    $salesTable . '.numeracao',
                    $salesTable . '.data_estorno',
                    'users.name as vendedor_nome'
                ])
                ->where($salesTable . '.id_produto', $productId)
                ->where(function($query) use ($salesTable) {
                    $query->whereNull($salesTable . '.ticket')
                          ->orWhere($salesTable . '.ticket', '');
                })
                ->orderBy($salesTable . '.data_venda', 'desc')
                ->limit(30)
                ->get();

            // Add computed fields
            $salesHistory = $salesHistory->map(function ($sale) {
                $sale->is_returned = !is_null($sale->data_estorno) && $sale->data_estorno !== '';
                $sale->vendedor_nome = $sale->vendedor_nome ?? 'Vendedor não encontrado';
                return $sale;
            });

            return $salesHistory;

        } catch (\Exception $e) {
            \Log::error("Error getting sales history for product {$productId}: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get available sizes for a product in the seller's city (AJAX endpoint)
     */
    public function getAvailableSizesAjax(Produto $produto)
    {
        $availableSizes = $this->getAvailableSizes($produto->id);
        return response()->json($availableSizes);
    }



    /**
     * Get available sizes for a product in the seller's city
     */
    public function getAvailableSizes($productId)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->cidade) {
                return collect();
            }

            // Get city name from user's city ID
            $cidade = DB::table('cidades')->where('id', $user->cidade)->first();
            if (!$cidade) {
                return collect();
            }

            $nomeCidadeFormatado = strtolower(str_replace(' ', '_', $cidade->cidade));
            $stockTable = 'estoque_' . $nomeCidadeFormatado;

            // Check if stock table exists
            if (!DB::getSchemaBuilder()->hasTable($stockTable)) {
                \Log::warning("Stock table {$stockTable} does not exist for city {$cidade->cidade}");
                return collect();
            }

            // Get available sizes with stock > 0
            $availableSizes = DB::table($stockTable)
                ->select(['numero', 'quantidade'])
                ->where('id_produto', $productId)
                ->where('quantidade', '>', 0)
                ->orderBy('numero')
                ->get();

            return $availableSizes;

        } catch (\Exception $e) {
            \Log::error("Error getting available sizes for product {$productId}: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Process product return
     */
    public function processReturn(Request $request)
    {
        \Log::info('processReturn called with data: ' . json_encode($request->all()));
        
        // Validate request
        if (!$request->has('sale_id') || !is_numeric($request->sale_id)) {
            return response()->json(['success' => false, 'message' => 'ID da venda é obrigatório e deve ser um número.']);
        }

        try {
            $saleId = $request->input('sale_id');
            $user = Auth::user();

            if (!$user || !$user->cidade) {
                return response()->json(['success' => false, 'message' => 'Usuário não possui cidade configurada.']);
            }

            // Get city name from user's city ID
            $cidade = DB::table('cidades')->where('id', $user->cidade)->first();
            if (!$cidade) {
                return response()->json(['success' => false, 'message' => 'Cidade do usuário não encontrada.']);
            }

            $nomeCidadeFormatado = strtolower(str_replace(' ', '_', $cidade->cidade));
            $salesTable = 'vendas_' . $nomeCidadeFormatado;
            $stockTable = 'estoque_' . $nomeCidadeFormatado;

            // Check if tables exist
            if (!DB::getSchemaBuilder()->hasTable($salesTable) || !DB::getSchemaBuilder()->hasTable($stockTable)) {
                return response()->json(['success' => false, 'message' => 'Tabelas necessárias não encontradas para sua cidade.']);
            }

            // Get sale record
            $sale = DB::table($salesTable)->where('id_vendas', $saleId)->first();
            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Venda não encontrada.']);
            }

            // Check if already returned
            if ($sale->data_estorno && $sale->data_estorno !== '') {
                return response()->json(['success' => false, 'message' => 'Este item já foi devolvido.']);
            }

            DB::beginTransaction();

            // Update sale record with return date
            DB::table($salesTable)
                ->where('id_vendas', $saleId)
                ->update(['data_estorno' => now()->toDateString()]);

            // Add 1 unit to inventory for the returned size
            $stockRecord = DB::table($stockTable)
                ->where('id_produto', $sale->id_produto)
                ->where('numero', $sale->numeracao)
                ->first();

            if ($stockRecord) {
                DB::table($stockTable)
                    ->where('id_produto', $sale->id_produto)
                    ->where('numero', $sale->numeracao)
                    ->update(['quantidade' => $stockRecord->quantidade + 1]);
            } else {
                // Create new stock record if it doesn't exist
                DB::table($stockTable)->insert([
                    'id_produto' => $sale->id_produto,
                    'numero' => $sale->numeracao,
                    'quantidade' => 1
                ]);
            }

            // Add 1 unit to the main product quantity
            DB::table('produtos')
                ->where('id', $sale->id_produto)
                ->increment('quantidade', 1);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Produto devolvido com sucesso!']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error processing return: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao processar devolução.']);
        }
    }

    /**
     * Busca informações da venda (operador de caixa e vendedor atendente)
     */
    public function buscarInformacoesVendaProduto(Request $request, $saleId)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->cidade) {
                return response()->json([
                    'success' => false,
                    'operador_caixa' => 'Usuário sem cidade',
                    'vendedor_atendente' => 'Usuário sem cidade'
                ]);
            }

            // Get city name from user's city ID
            $cidade = DB::table('cidades')->where('id', $user->cidade)->first();
            if (!$cidade) {
                return response()->json([
                    'success' => false,
                    'operador_caixa' => 'Cidade não encontrada',
                    'vendedor_atendente' => 'Cidade não encontrada'
                ]);
            }

            $nomeCidadeFormatado = strtolower(str_replace(' ', '_', $cidade->cidade));
            $salesTable = 'vendas_' . $nomeCidadeFormatado;

            // Check if table exists
            if (!DB::getSchemaBuilder()->hasTable($salesTable)) {
                return response()->json([
                    'success' => false,
                    'operador_caixa' => 'Tabela não encontrada',
                    'vendedor_atendente' => 'Tabela não encontrada'
                ]);
            }

            // Get sale record
            $sale = DB::table($salesTable)->where('id_vendas', $saleId)->first();
            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'operador_caixa' => 'Venda não encontrada',
                    'vendedor_atendente' => 'Venda não encontrada'
                ]);
            }

            // Buscar nomes dos usuários
            $operadorCaixa = 'Não identificado';
            $vendedorAtendente = 'Não identificado';

            if (isset($sale->id_vendedor) && $sale->id_vendedor) {
                $operador = DB::table('users')->where('id', $sale->id_vendedor)->first();
                $operadorCaixa = $operador ? $operador->name : 'ID: ' . $sale->id_vendedor;
            }

            if (isset($sale->id_vendedor_atendente) && $sale->id_vendedor_atendente) {
                $vendedor = DB::table('users')->where('id', $sale->id_vendedor_atendente)->first();
                $vendedorAtendente = $vendedor ? $vendedor->name : 'ID: ' . $sale->id_vendedor_atendente;
            }

            return response()->json([
                'success' => true,
                'operador_caixa' => $operadorCaixa,
                'vendedor_atendente' => $vendedorAtendente
            ]);

        } catch (Exception $e) {
            \Log::error('Erro ao buscar informações da venda: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'operador_caixa' => 'Erro ao buscar',
                'vendedor_atendente' => 'Erro ao buscar'
            ]);
        }
    }

    /**
     * Process size exchange
     */
    public function processExchange(Request $request)
    {
        \Log::info('processExchange called with data: ' . json_encode($request->all()));
        
        // Validate request
        if (!$request->has('sale_id') || !is_numeric($request->sale_id)) {
            return response()->json(['success' => false, 'message' => 'ID da venda é obrigatório e deve ser um número.']);
        }
        
        if (!$request->has('new_size') || empty($request->new_size)) {
            return response()->json(['success' => false, 'message' => 'Nova numeração é obrigatória.']);
        }

        try {
            $saleId = $request->input('sale_id');
            $newSize = $request->input('new_size');
            $user = Auth::user();

            if (!$user || !$user->cidade) {
                return response()->json(['success' => false, 'message' => 'Usuário não possui cidade configurada.']);
            }

            // Get city name from user's city ID
            $cidade = DB::table('cidades')->where('id', $user->cidade)->first();
            if (!$cidade) {
                return response()->json(['success' => false, 'message' => 'Cidade do usuário não encontrada.']);
            }

            $nomeCidadeFormatado = strtolower(str_replace(' ', '_', $cidade->cidade));
            $salesTable = 'vendas_' . $nomeCidadeFormatado;
            $stockTable = 'estoque_' . $nomeCidadeFormatado;

            // Check if tables exist
            if (!DB::getSchemaBuilder()->hasTable($salesTable) || !DB::getSchemaBuilder()->hasTable($stockTable)) {
                return response()->json(['success' => false, 'message' => 'Tabelas necessárias não encontradas para sua cidade.']);
            }

            // Get sale record
            $sale = DB::table($salesTable)->where('id_vendas', $saleId)->first();
            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Venda não encontrada.']);
            }

            // Check if already returned
            if ($sale->data_estorno && $sale->data_estorno !== '') {
                return response()->json(['success' => false, 'message' => 'Este item já foi devolvido.']);
            }

            // Check if new size has stock
            $newSizeStock = DB::table($stockTable)
                ->where('id_produto', $sale->id_produto)
                ->where('numero', $newSize)
                ->first();

            if (!$newSizeStock || $newSizeStock->quantidade <= 0) {
                return response()->json(['success' => false, 'message' => 'Numeração selecionada sem estoque disponível.']);
            }

            DB::beginTransaction();

            // Add 1 unit to original size inventory
            $originalSizeStock = DB::table($stockTable)
                ->where('id_produto', $sale->id_produto)
                ->where('numero', $sale->numeracao)
                ->first();

            if ($originalSizeStock) {
                DB::table($stockTable)
                    ->where('id_produto', $sale->id_produto)
                    ->where('numero', $sale->numeracao)
                    ->update(['quantidade' => $originalSizeStock->quantidade + 1]);
            } else {
                // Create new stock record if it doesn't exist
                DB::table($stockTable)->insert([
                    'id_produto' => $sale->id_produto,
                    'numero' => $sale->numeracao,
                    'quantidade' => 1
                ]);
            }

            // Subtract 1 unit from new size inventory
            DB::table($stockTable)
                ->where('id_produto', $sale->id_produto)
                ->where('numero', $newSize)
                ->update(['quantidade' => $newSizeStock->quantidade - 1]);

            // Update sale record with new size
            DB::table($salesTable)
                ->where('id_vendas', $saleId)
                ->update(['numeracao' => $newSize]);

            // Note: We don't update the main product quantity in exchanges
            // because we're just swapping one size for another (no net change)

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Troca de numeração realizada com sucesso!']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error processing exchange: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao processar troca de numeração.']);
        }
    }

}


