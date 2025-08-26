<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\Parcela;
use App\Models\Pagamento;
use App\Services\PaymentProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clientes = collect(); // Cria uma coleção vazia por padrão

        if ($request->has('cliente')) { // Só executa a busca se o formulário foi enviado
            $query = cliente::query();
            if ($request->cliente) {
                $searchTerm = '%' . $request->cliente . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('nome', 'like', $searchTerm)
                      ->orWhere('apelido', 'like', $searchTerm)
                      ->orWhere('cpf', 'like', $searchTerm);
                });
            }
            $clientes = $query->orderBy('nome', 'asc')->paginate(10);
            
            // Verificar se cada cliente tem tickets negativados
            foreach ($clientes as $cliente) {
                $cliente->tem_tickets_negativados = $cliente->tickets()->where('spc', true)->exists();
            }
        }

        return view('cliente.index', ['clientes' => $clientes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('cliente.create');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        try{
            $request->validate([
                'nome' => 'required',
                'rg' => 'required',
                'cpf' => 'required',
                'telefone' => 'required',
                'nome_referencia' => 'required',
                'telefone_referencia' => 'required',
                'parentesco' => 'required',
                'foto' => 'required|image',
                'rua' => 'required',
                'numero' => 'required|numeric',
                'bairro' => 'required',
                'referencia' => 'required',
                'cidade' => 'required',
                'renda' => 'required'
            ]);

            $pasta = Str::uuid();
            $file_name = 'foto'.time().'.'.$request->file('foto')->extension();


        if (cliente::where('cpf', $request->cpf)->exists()) {
            return back()->withInput()->with('error', 'CPF já cadastrado!');
        }
        cliente::create([
            'nome' => $request->nome,
            'apelido' => $request->apelido,
            'rg' => $request->rg,
            'cpf' => $request->cpf,
            'mae' => $request->mae,
            'pai' => $request->pai,
            'telefone' => preg_replace('/[^0-9]/', '', $request->telefone),
            'nascimento' => $request->nascimento,
            'nome_referencia' => $request->nome_referencia,
            'numero_referencia' => preg_replace('/[^0-9]/', '', $request->telefone_referencia),
            'parentesco_referencia' => $request->parentesco,
            'referencia_comercial1' => $request->referencia_comercial1,
            'telefone_referencia_comercial1' => preg_replace('/[^0-9]/', '', $request->telefone_referencia_comercial1),
            'referencia_comercial2' => $request->referencia_comercial2,
            'telefone_referencia_comercial2' => preg_replace('/[^0-9]/', '', $request->telefone_referencia_comercial2),
            'referencia_comercial3' => $request->referencia_comercial3,
            'telefone_referencia_comercial3' => preg_replace('/[^0-9]/', '', $request->telefone_referencia_comercial3),
            'foto' => $file_name,
            'rua' => $request->rua,
            'numero' => $request->numero,
            'bairro' => $request->bairro,
            'referencia' => $request->referencia,
            'cidade' => $request->cidade,
            'limite' => 0.00,
            'status' => 'inativo',
            'renda' => $request->renda,
            'pasta' => $pasta,
        ]);

        $folderPath = 'uploads/clientes/'.$pasta;
        $exists = Storage::disk('public')->exists($folderPath);

        if (!$exists) {
            Storage::disk('public')->makeDirectory($folderPath);
            $exists = Storage::disk('public')->exists($folderPath);
        }

        $manager = new ImageManager(new Driver());
        if ($request->hasFile('foto')) {
            $image = $manager->read($request->file('foto'));
            $image->resize(562, 1000);
            $path = 'uploads/clientes/'.$pasta.'/'.$file_name;
            Storage::disk('public')->put($path, $image->toJpeg(90));
        }

        return redirect()->route('clientes.create')->with('success', 'Cliente cadastrado com sucesso! Adicione as fotos de seus documentos!');

        }catch( Exception $e){
            $errorMessage = 'Erro ao cadastrar cliente: ' . $e->getMessage();
            return back()->withInput()->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(cliente $cliente)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(cliente $cliente)
    {
        // Carrega os autorizados relacionados ao cliente
        $cliente->load('autorizados');
        
        // Calcular limite disponível
        $limiteTotal = $cliente->limite ?? 0;
        
        // Somar parcelas com status "aguardando pagamento"
        $valorParcelasAguardando = $cliente->parcelas()
            ->where('status', 'aguardando pagamento')
            ->sum('valor_parcela');
            
        $limiteDisponivel = $limiteTotal - $valorParcelasAguardando;
        
        // Adicionar as informações calculadas ao cliente
        $cliente->limite_total_calculado = $limiteTotal;
        $cliente->limite_disponivel_calculado = $limiteDisponivel;
        
        return view('cliente.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, cliente $cliente)
    {
        try{
            $request->validate([
                'nome' => 'required',
                'rg' => 'required',
                'cpf' => 'required',
                'telefone' => 'required',
                'nome_referencia' => 'required',
                'telefone_referencia' => 'required',
                'parentesco' => 'required',
                'referencia_comercial1' => 'required',
                'telefone_referencia_comercial1' => 'required',
                'referencia_comercial2' => 'required',
                'telefone_referencia_comercial2' => 'required',
                'referencia_comercial3' => 'required',
                'telefone_referencia_comercial3' => 'required',
                'rua' => 'required',
                'numero' => 'required|numeric',
                'bairro' => 'required',
                'referencia' => 'required',
                'cidade' => 'required',
                'renda' => 'required'
            ]);

            if (is_null($cliente->pasta)) {
                $cliente->pasta = $cliente->cpf;
            }

            // Gera token aleatório de 4 números se for NULL
            if (is_null($cliente->token)) {
                $cliente->token = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            }

            $pasta = $cliente->pasta;
            $atualizacao = Carbon::now();

            $cliente->update([
                'nome' => $request->nome,
                'apelido' => $request->apelido,
                'rg' => $request->rg,
                'cpf' => $request->cpf,
                'mae' => $request->mae,
                'pai' => $request->pai,
                'telefone' => preg_replace('/[^0-9]/', '', $request->telefone),
                'nascimento' => $request->nascimento,
                'nome_referencia' => $request->nome_referencia,
                'numero_referencia' => preg_replace('/[^0-9]/', '', $request->telefone_referencia),
                'parentesco_referencia' => $request->parentesco,
                'referencia_comercial1' => $request->referencia_comercial1,
                'telefone_referencia_comercial1' => preg_replace('/[^0-9]/', '', $request->telefone_referencia_comercial1),
                'referencia_comercial2' => $request->referencia_comercial2,
                'telefone_referencia_comercial2' => preg_replace('/[^0-9]/', '', $request->telefone_referencia_comercial2),
                'referencia_comercial3' => $request->referencia_comercial3,
                'telefone_referencia_comercial3' => preg_replace('/[^0-9]/', '', $request->telefone_referencia_comercial3),
                'rua' => $request->rua,
                'numero' => $request->numero,
                'bairro' => $request->bairro,
                'referencia' => $request->referencia,
                'cidade' => $request->cidade,
                'renda' => $request->renda,
                'pasta' => $pasta,
                'atualizacao' => $atualizacao,
                'obs' => $request->obs,
                'token' => $cliente->token,
            ]);
        }catch( Exception $e){
            return back()->withInput()->with('error', 'Erro ao atualizar Cliente: ' . $e->getMessage());
        }
            return redirect()->back()->with('success', 'Cliente atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(cliente $cliente)
    {
        //
    }


    public function uploadDocumentos(Request $request, cliente $id)
    {

        try{
        $id = $request->input('id');
        $cliente = cliente::find($id);
        if (is_null($cliente->pasta)) {
            $cliente->pasta = $cliente->cpf;
        }
        $pasta = $cliente->pasta;
        $documento = $request->input('documento');

        $manager = new ImageManager(new Driver());
            if ($request->hasFile('foto')) {
                $image = $manager->read($request->file('foto'));

                // Verifica a orientação da imagem
                $width = $image->width();
                $height = $image->height();

                if ($width > $height) {
                    // Imagem na horizontal
                    $image->resize(1000, 562);
                } else {
                    // Imagem na vertical
                    $image->resize(562, 1000);
                }

                $file_name = $documento.time().'.'.$request->file('foto')->extension();
                $path = 'uploads/clientes/'.$pasta.'/'.$file_name;
                Storage::disk('public')->put($path, $image->toJpeg(90));
        }

            $cliente = cliente::find($id);
            if ($cliente) {
                $cliente->update([
                    $documento => $file_name,
                ]);
            }
        }catch(Exception $e){
            return back()->withInput()->with('error', 'Erro ao Enviar foto!');
        }

        return back();
    }

    public function alterar_foto_cliente(Request $request, cliente $id)
    {

        try{
        $id = $request->input('id');
        $campo = $request->input('documento2');
        $cliente = cliente::find($id);
        if (is_null($cliente->pasta)) {
            $cliente->pasta = $cliente->cpf;
        }
        $pasta = $cliente->pasta;

         // Check if a new image was uploaded
         if($request->hasFile('image')) {

            // Get the old image path to delete it later
            switch ($campo) {
                case "foto":
                    $oldImagePath = 'uploads/clientes/'.$pasta.'/'.$cliente->foto;
                    break;
                case 'rg_frente':
                    $oldImagePath = 'uploads/clientes/'.$pasta.'/'.$cliente->rg_frente;
                    break;
                case 'rg_verso':
                    $oldImagePath = 'uploads/clientes/'.$pasta.'/'.$cliente->rg_verso;
                    break;
                case 'cpf_foto':
                    $oldImagePath = 'uploads/clientes/'.$pasta.'/'.$cliente->cpf_foto;
                    break;
            }

            // Process and upload the new image
            $manager = new ImageManager(new Driver());
            $image = $manager->read($request->file('image'));

            // Verifica a orientação da imagem
            $width = $image->width();
            $height = $image->height();

            if ($width > $height) {
                // Imagem na horizontal
                $image->resize(1000, 562);
            } else {
                // Imagem na vertical
                $image->resize(562, 1000);
            }

            $file_name = $campo.time().'.'.$request->image->extension();
            $path = 'uploads/clientes/'.$pasta.'/'.$file_name;
            Storage::put($path, $image->toJpeg(90));

            // Delete the old image if it exists using Storage facade
            if($oldImagePath && Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }
        } else {
            // Keep the existing image path
            $path = $id->image;
        }

        $cliente->update([
            $campo =>  $file_name,
        ]);

            }catch(Exception $e){
                return back()->withInput()->with('error', $e->getMessage());
            }

        return back();
    }

    /**
     * Exibe o histórico de compras do cliente
     */
    public function historicoCompras(Request $request, $id)
    {
        try {
            $cliente = cliente::findOrFail($id);

            // Buscar tickets com paginação (20 por página)
            $tickets = Ticket::where('id_cliente', $id)
                ->with(['parcelasRelacao' => function($query) {
                    $query->orderBy('data_vencimento', 'asc');
                }])
                ->orderBy('data', 'desc')
                ->paginate(20);

            // Calcular perfil de pagamento
            $paymentProfileService = new PaymentProfileService();
            $paymentProfile = $paymentProfileService->calculateProfile($id);

            return view('cliente.historico-compras', compact('cliente', 'tickets', 'paymentProfile'));

        } catch (Exception $e) {
            return redirect()->route('clientes.index')
                ->with('error', 'Erro ao carregar histórico de compras: ' . $e->getMessage());
        }
    }

    /**
     * Exibe o histórico de pagamentos do cliente
     */
    public function historicoPagamentos(Request $request, $id)
    {
        try {
            $cliente = cliente::findOrFail($id);

            // Buscar pagamentos do cliente
            $pagamentos = Pagamento::where('id_cliente', $id)
                ->with(['parcelas' => function($query) {
                    $query->orderBy('data_vencimento', 'asc');
                }])
                ->orderBy('data', 'desc')
                ->get();

            return view('cliente.historico-pagamentos', compact('cliente', 'pagamentos'));

        } catch (Exception $e) {
            return redirect()->route('clientes.index')
                ->with('error', 'Erro ao carregar histórico de pagamentos: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os detalhes de uma compra específica
     */
    public function detalhesCompra($id, $ticket)
    {
        try {
            $cliente = cliente::findOrFail($id);

            $ticketData = Ticket::where('id_cliente', $id)
                ->where('ticket', $ticket)
                ->with(['parcelasRelacao' => function($query) {
                    $query->orderBy('data_vencimento', 'asc');
                }])
                ->firstOrFail();

            // Buscar produtos da compra
            $produtos = $this->buscarProdutosDaCompra($ticketData->ticket);

            return view('cliente.compra-detalhes', compact('cliente', 'ticketData', 'produtos'));

        } catch (Exception $e) {
            return redirect()->route('clientes.historico.compras', $id)
                ->with('error', 'Erro ao carregar detalhes da compra: ' . $e->getMessage());
        }
    }

    /**
     * Gera duplicata da compra
     */
    public function gerarDuplicata($id, $ticket)
    {
        try {
            $cliente = cliente::findOrFail($id);

            $ticketData = Ticket::where('id_cliente', $id)
                ->where('ticket', $ticket)
                ->with(['parcelasRelacao' => function($query) {
                    $query->orderBy('data_vencimento', 'asc');
                }])
                ->firstOrFail();

            // Buscar produtos da compra para informações adicionais
            $produtos = $this->buscarProdutosDaCompra($ticketData->ticket);

            return view('cliente.duplicata', compact('cliente', 'ticketData', 'produtos'));

        } catch (Exception $e) {
            return redirect()->route('clientes.historico.compras', $id)
                ->with('error', 'Erro ao gerar duplicata: ' . $e->getMessage());
        }
    }

    /**
     * Gera carnê de pagamento
     */
    public function gerarCarne($id, $ticket)
    {
        try {
            $cliente = cliente::findOrFail($id);

            $ticketData = Ticket::where('id_cliente', $id)
                ->where('ticket', $ticket)
                ->with(['parcelasRelacao' => function($query) {
                    $query->orderBy('data_vencimento', 'asc');
                }])
                ->firstOrFail();

            // Buscar produtos da compra para informações adicionais
            $produtos = $this->buscarProdutosDaCompra($ticketData->ticket);

            return view('cliente.carne', compact('cliente', 'ticketData', 'produtos'));

        } catch (Exception $e) {
            return redirect()->route('clientes.historico.compras', $id)
                ->with('error', 'Erro ao gerar carnê: ' . $e->getMessage());
        }
    }

    /**
     * Envia mensagem de aviso para o cliente
     */
    public function enviarMensagem(Request $request, $id, $ticket)
    {
        try {
            $cliente = cliente::findOrFail($id);

            $ticketData = Ticket::where('id_cliente', $id)
                ->where('ticket', $ticket)
                ->firstOrFail();

            $request->validate([
                'mensagem' => 'required|string|max:500',
                'tipo' => 'required|in:sms,whatsapp,email'
            ]);

            $mensagem = $request->mensagem;
            $tipo = $request->tipo;

            // Se for WhatsApp, gerar link e redirecionar
            if ($tipo === 'whatsapp') {
                // Limpar o número do telefone (remover caracteres especiais)
                $telefone = preg_replace('/[^0-9]/', '', $cliente->telefone);

                // Garantir que o número tenha o código do país (55 para Brasil)
                if (!str_starts_with($telefone, '55')) {
                    $telefone = '55' . $telefone;
                }

                // Criar mensagem padrão se não foi fornecida uma personalizada
                if (empty($mensagem) || $mensagem === 'Mensagem padrão') {
                    $linkHistorico = url("/parcelas/");
                    $mensagemPadrao = "Joécio calçados informa: Olá {$cliente->nome}, compra realizada no valor de {$ticketData->valor_formatado}. Acompanhe suas parcelas através do link: {$linkHistorico}";
                    $mensagem = $mensagemPadrao;
                }

                // Codificar a mensagem para URL
                $mensagemCodificada = urlencode($mensagem);

                // Gerar link do WhatsApp
                $whatsappUrl = "https://wa.me/{$telefone}?text={$mensagemCodificada}";

                // Log da ação
                \Log::info("Link WhatsApp gerado para cliente {$cliente->nome}: {$whatsappUrl}");

                // Retornar com o link para abrir em nova aba
                return redirect()->route('clientes.compra', [$id, $ticket])
                    ->with('success', 'Link do WhatsApp gerado com sucesso!')
                    ->with('whatsapp_url', $whatsappUrl);
            }

            // Para outros tipos (SMS, email), implementar lógica específica
            \Log::info("Mensagem enviada para cliente {$cliente->nome} via {$tipo}: {$mensagem}");

            return redirect()->route('clientes.compra', [$id, $ticket])
                ->with('success', 'Mensagem enviada com sucesso!');

        } catch (Exception $e) {
            return redirect()->route('clientes.historico.compras', $id)
                ->with('error', 'Erro ao enviar mensagem: ' . $e->getMessage());
        }
    }

    /**
     * Busca os produtos de uma compra específica
     */
    private function buscarProdutosDaCompra($ticket)
    {
        try {
            // Buscar a primeira parcela para determinar a cidade/bd
            $parcela = Parcela::where('ticket', $ticket)->first();

            if (!$parcela || !$parcela->bd) {
                return collect();
            }

            // Determinar a tabela de vendas baseada no bd
            $tabelaVendas = $this->determinarTabelaVendas($parcela->bd);

            if (!$tabelaVendas) {
                return collect();
            }

            // Buscar as vendas do ticket
            $vendas = DB::table($tabelaVendas)
                ->where('ticket', $ticket)
                ->get();

            if ($vendas->isEmpty()) {
                return collect();
            }

            // Agrupar por produto e calcular quantidades
            $produtosAgrupados = $vendas->groupBy('id_produto')->map(function ($vendasProduto) {
                $primeiraVenda = $vendasProduto->first();

                return (object) [
                    'id_produto' => $primeiraVenda->id_produto,
                    'quantidade' => $vendasProduto->count(),
                    'valor_unitario' => $primeiraVenda->preco_venda ?? $primeiraVenda->preco,
                    'valor_total' => ($primeiraVenda->preco_venda ?? $primeiraVenda->preco) * $vendasProduto->count(),
                    'numeracao' => $primeiraVenda->numeracao ?? 'N/A',
                    'desconto' => $primeiraVenda->desconto ?? 0
                ];
            });

            // Buscar informações dos produtos
            $idsProdutos = $produtosAgrupados->pluck('id_produto')->toArray();
            $produtos = DB::table('produtos')
                ->whereIn('id', $idsProdutos)
                ->get()
                ->keyBy('id');

            // Combinar informações de venda com dados do produto
            return $produtosAgrupados->map(function ($vendaProduto) use ($produtos) {
                $produto = $produtos->get($vendaProduto->id_produto);

                return (object) [
                    'id' => $vendaProduto->id_produto,
                    'nome' => $produto ? $produto->produto : "Produto ID {$vendaProduto->id_produto}",
                    'codigo' => $produto ? $produto->codigo : "ID-{$vendaProduto->id_produto}",
                    'marca' => $produto ? $produto->marca : 'N/A',
                    'grupo' => $produto ? $produto->grupo : 'N/A',
                    'subgrupo' => $produto ? $produto->subgrupo : 'N/A',
                    'numeracao' => $vendaProduto->numeracao,
                    'quantidade' => $vendaProduto->quantidade,
                    'valor_unitario' => $vendaProduto->valor_unitario,
                    'valor_total' => $vendaProduto->valor_total,
                    'desconto' => $vendaProduto->desconto,
                    'imagem' => $produto ? $produto->foto : null,
                    'produto_encontrado' => $produto ? true : false
                ];
            })->values();

        } catch (Exception $e) {
            \Log::error('Erro ao buscar produtos da compra: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Determina a tabela de vendas baseada no campo bd
     */
    private function determinarTabelaVendas($bd)
    {
        // Mapear os valores do campo bd para as tabelas de vendas
        $mapeamento = [
            'tabira' => 'vendas_tabira',
            'princesa' => 'vendas_princesa',
            'vendas_tabira' => 'vendas_tabira',
            'vendas_princesa' => 'vendas_princesa'
        ];

        $bdLower = strtolower($bd);

        return $mapeamento[$bdLower] ?? null;
    }

    /**
     * Envia mensagem WhatsApp sobre pagamento
     */
    public function enviarWhatsappPagamento(Request $request, $clienteId, $pagamentoId)
    {
        try {
            $cliente = cliente::findOrFail($clienteId);
            $pagamento = Pagamento::findOrFail($pagamentoId);

            // Verificar se o pagamento pertence ao cliente
            if ($pagamento->id_cliente != $clienteId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pagamento não encontrado para este cliente.'
                ], 404);
            }

            // Verificar se o cliente tem telefone
            if (empty($cliente->telefone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Número de telefone não encontrado para este cliente.'
                ], 400);
            }

            // Formatar número do telefone
            $telefone = preg_replace('/[^0-9]/', '', $cliente->telefone);
            
            // Garantir que o número tenha o código do país (55 para Brasil)
            if (!str_starts_with($telefone, '55')) {
                $telefone = '55' . $telefone;
            }

            // Criar mensagem
            $dataFormatada = $pagamento->data->format('d/m/Y');
            $mensagem = "Joécio calçados informa: Pagamento de parcela efetuado dia {$dataFormatada}, acesse o comprovante através do link: " . url('/parcelas/');
            
            // Codificar mensagem para URL
            $mensagemCodificada = urlencode($mensagem);
            
            // Gerar URL do WhatsApp
            $whatsappUrl = "https://wa.me/{$telefone}?text={$mensagemCodificada}";

            return response()->json([
                'success' => true,
                'whatsapp_url' => $whatsappUrl
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar link do WhatsApp: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibe comprovante para impressão
     */
    public function imprimirComprovantePagamento($clienteId, $pagamentoId)
    {
        try {
            $cliente = cliente::findOrFail($clienteId);
            $pagamento = Pagamento::with(['parcelas' => function($query) {
                $query->orderBy('numero', 'asc');
            }])->findOrFail($pagamentoId);

            // Verificar se o pagamento pertence ao cliente
            if ($pagamento->id_cliente != $clienteId) {
                return redirect()->route('clientes.historico.pagamentos', $clienteId)
                    ->with('error', 'Pagamento não encontrado para este cliente.');
            }

            return view('cliente.comprovante-pagamento', compact('cliente', 'pagamento'));

        } catch (Exception $e) {
            return redirect()->route('clientes.historico.pagamentos', $clienteId)
                ->with('error', 'Erro ao gerar comprovante: ' . $e->getMessage());
        }
    }

    /**
     * Cancela um pagamento
     */
    public function cancelarPagamento(Request $request, $clienteId, $pagamentoId)
    {
        try {
            DB::beginTransaction();

            $cliente = cliente::findOrFail($clienteId);
            $pagamento = Pagamento::findOrFail($pagamentoId);

            // Verificar se o pagamento pertence ao cliente
            if ($pagamento->id_cliente != $clienteId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pagamento não encontrado para este cliente.'
                ], 404);
            }

            // Buscar parcelas relacionadas ao pagamento
            $parcelas = Parcela::where('ticket_pagamento', $pagamento->ticket)->get();

            // Atualizar parcelas - resetar campos de pagamento
            foreach ($parcelas as $parcela) {
                $parcela->update([
                    'data_pagamento' => null,
                    'hora' => null,
                    'valor_pago' => null,
                    'dinheiro' => null,
                    'pix' => null,
                    'cartao' => null,
                    'metodo' => null,
                    'id_vendedor' => null,
                    'ticket_pagamento' => null,
                    'status' => 'aguardando pagamento'
                ]);
            }

            // Deletar o pagamento
            $pagamento->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pagamento cancelado com sucesso!'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar pagamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibe lista de clientes ociosos
     */
    public function clientesOciosos(Request $request)
    {
        try {
            // Query para buscar clientes ociosos
            $query = Cliente::select(['id', 'nome', 'cpf', 'telefone', 'ociosidade', 'status'])
                ->whereNotNull('ociosidade')
                ->whereRaw('DATEDIFF(CURDATE(), ociosidade) >= 150')
                ->whereDoesntHave('tickets', function($query) {
                    $query->where('spc', true);
                })
                ->where('status', '!=', 'inativo')
                ->orderBy('ociosidade', 'asc');

            // Aplicar paginação
            $clientes = $query->paginate(20);

            // Calcular dias de ociosidade para cada cliente
            foreach ($clientes as $cliente) {
                if ($cliente->ociosidade) {
                    $cliente->dias_ociosos = Carbon::parse($cliente->ociosidade)->diffInDays(Carbon::now());
                } else {
                    $cliente->dias_ociosos = 0;
                }
            }

            return view('cliente.ociosos', compact('clientes'));

        } catch (Exception $e) {
            \Log::error('Erro ao carregar clientes ociosos: ' . $e->getMessage());
            return redirect()->route('clientes.index')
                ->with('error', 'Erro ao carregar lista de clientes ociosos: ' . $e->getMessage());
        }
    }

    /**
     * Envia mensagem WhatsApp para cliente ocioso
     */
    public function enviarMensagemOcioso($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);

            // Validar se cliente possui telefone
            if (empty($cliente->telefone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente não possui número de telefone cadastrado.'
                ], 400);
            }

            // Validar se cliente ainda está ocioso (150+ dias)
            if ($cliente->ociosidade) {
                $diasOciosos = Carbon::parse($cliente->ociosidade)->diffInDays(Carbon::now());
                if ($diasOciosos < 150) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cliente não atende mais aos critérios de ociosidade.'
                    ], 400);
                }
            }

            // Extrair nomes usando método auxiliar
            $doisPrimeirosNomes = $this->extrairPrimeirosNomes($cliente->nome);

            // Formatar número do telefone
            $telefone = preg_replace('/[^0-9]/', '', $cliente->telefone);
            if (!str_starts_with($telefone, '55')) {
                $telefone = '55' . $telefone;
            }

            // Gerar mensagem personalizada
            $mensagem = "Bom dia, {$doisPrimeirosNomes}, tudo bem com você? Estamos sentindo sua falta, notamos sua ausência de nossa loja nos últimos tempos. Confira nossas novidades no instagram @joecio_calcados. Você é um cliente especial para nós. Seu crediário continua ativo, esperamos por o seu retorno em uma de nossas lojas, estamos de braços abertos!";

            // Atualizar campo ociosidade para data atual
            $cliente->update([
                'ociosidade' => Carbon::now()
            ]);

            // Codificar mensagem para URL
            $mensagemCodificada = urlencode($mensagem);

            // Gerar URL do WhatsApp
            $whatsappUrl = "https://wa.me/{$telefone}?text={$mensagemCodificada}";

            // Log da ação
            \Log::info("Mensagem de reativação enviada para cliente {$cliente->nome} (ID: {$id})");

            return response()->json([
                'success' => true,
                'whatsapp_url' => $whatsappUrl,
                'message' => 'Link do WhatsApp gerado com sucesso!'
            ]);

        } catch (Exception $e) {
            \Log::error('Erro ao enviar mensagem para cliente ocioso: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar solicitação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extrai os primeiros nomes, verificando se o segundo é uma conjunção
     */
    private function extrairPrimeirosNomes($nomeCompleto)
    {
        $nomes = explode(' ', trim($nomeCompleto));
        $conjuncoes = ['da', 'de', 'do', 'das', 'dos', 'e', 'del', 'della', 'di', 'du', 'van', 'von', 'la', 'le', 'el'];
        
        if (count($nomes) >= 2 && in_array(strtolower($nomes[1]), $conjuncoes)) {
            // Se o segundo nome é uma conjunção, usar apenas o primeiro
            return $nomes[0];
        } else {
            // Caso contrário, usar os dois primeiros nomes
            return implode(' ', array_slice($nomes, 0, 2));
        }
    }

}
