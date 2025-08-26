<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AutorizadoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\SubgrupoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\AuthController; // Adicionado para rotas de autenticação
use App\Http\Controllers\CarrinhoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\ConsultaParcelaController;
use App\Http\Controllers\ParcelaController;
use App\Http\Controllers\MensagemAvisoController;
use App\Http\Controllers\DespesaController;
use App\Http\Controllers\FluxoCaixaController;
use App\Http\Controllers\BaixaFiscalController; // Adicionado para baixa fiscal
use App\Http\Controllers\VerificacaoLimiteController;

// Rotas de Autenticação
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login'); // Mostra o formulário de login
Route::post('/login', [AuthController::class, 'login']); // Processa o login
Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); // Rota para logout

// Rotas para Acompanhamento de Parcelas
Route::get('/parcelas', [ParcelaController::class, 'index'])->name('parcelas.index');
Route::post('/parcelas/consultar', [ParcelaController::class, 'consultar'])->name('parcelas.consultar');


// Rotas Protegidas por Autenticação
Route::middleware(['auth'])->group(function () {

    Route::get('/', function () { return view('welcome'); })->name('dashboard');
    Route::get('/welcome', [AuthController::class, 'welcome'])->name('welcome'); // Rota de boas-vindas (protegida)
    Route::get('/mensagens-aviso', [MensagemAvisoController::class, 'index'])->name('mensagens-aviso.index');
    Route::post('/mensagens-aviso/{id}/enviar-mensagem', [MensagemAvisoController::class, 'enviarMensagem'])->name('mensagens-aviso.enviar-mensagem');

    // Rotas para funcionarios
    Route::get('/funcionario/index/{status?}', [FuncionarioController::class, 'index'])->name('funcionario.index');
    Route::get('/funcionario/cadastro', [FuncionarioController::class, 'cadastro'])->name('funcionario.cadastro');
    Route::post('/funcionario/store', [FuncionarioController::class, 'store'])->name('funcionario.store');
    Route::get('/funcionario/edit/{id}', [FuncionarioController::class, 'edit'])->name('funcionario.edit');
    Route::put('/funcionario/update/{id}', [FuncionarioController::class, 'update'])->name('funcionario.update');

    // Rotas para clientes ociosos (devem vir ANTES da rota resource)
    Route::get('/clientes/ociosos', [ClienteController::class, 'clientesOciosos'])->name('clientes.ociosos');
    Route::post('/clientes/{id}/mensagem-ocioso', [ClienteController::class, 'enviarMensagemOcioso'])->name('clientes.mensagem.ocioso');

    // Rotas para clientes
    Route::resource('clientes', ClienteController::class);
    Route::put('/upload_documentos/{id}', [ClienteController::class, 'uploadDocumentos'])->name('upload_documentos');
    Route::put('/alterar_foto_cliente/{id}', [ClienteController::class, 'alterar_foto_cliente'])->name('alterar_foto_cliente');

    // Rotas para histórico de compras
    Route::get('/clientes/{id}/historico', [ClienteController::class, 'historicoCompras'])->name('clientes.historico.compras');
    Route::get('/clientes/{id}/historico-pagamentos', [ClienteController::class, 'historicoPagamentos'])->name('clientes.historico.pagamentos');
    Route::get('/clientes/{id}/compra/{ticket}', [ClienteController::class, 'detalhesCompra'])->name('clientes.compra');
    Route::get('/clientes/{id}/duplicata/{ticket}', [ClienteController::class, 'gerarDuplicata'])->name('clientes.duplicata');
    Route::get('/clientes/{id}/carne/{ticket}', [ClienteController::class, 'gerarCarne'])->name('clientes.carne');
    Route::post('/clientes/{id}/mensagem/{ticket}', [ClienteController::class, 'enviarMensagem'])->name('clientes.mensagem');

    // Rotas para funcionalidades dos botões do histórico de pagamentos
    Route::post('/clientes/{clienteId}/pagamentos/{pagamentoId}/whatsapp', [ClienteController::class, 'enviarWhatsappPagamento'])->name('clientes.pagamentos.whatsapp');
    Route::get('/clientes/{clienteId}/pagamentos/{pagamentoId}/comprovante', [ClienteController::class, 'imprimirComprovantePagamento'])->name('clientes.pagamentos.comprovante');
    Route::delete('/clientes/{clienteId}/pagamentos/{pagamentoId}/cancelar', [ClienteController::class, 'cancelarPagamento'])->name('clientes.pagamentos.cancelar');



    // Rotas para autorizados
    Route::resource('autorizados', AutorizadoController::class);
    Route::get('/autorizados/create/{cliente_id}', [AutorizadoController::class, 'create'])->name('autorizados.createWithClient');
    Route::put('/upload_documentos_autorizado/{id}', [AutorizadoController::class, 'upload_documentos_autorizado'])->name('upload_documentos_autorizado');
    Route::put('/alterar_foto_autorizado/{id}', [AutorizadoController::class, 'alterar_foto_autorizado'])->name('alterar_foto_autorizado');

    Route::get('/produtos/procurar/', [ProdutoController::class, 'procurar'])->name('produtos.procurar');
    Route::get('/produtos/exibir/{produto}', [ProdutoController::class, 'exibir'])->name('produtos.exibir');
    
    // AJAX routes for product sales management
    Route::post('/produtos/process-return', [ProdutoController::class, 'processReturn'])->name('produtos.processReturn');
    Route::post('/produtos/process-exchange', [ProdutoController::class, 'processExchange'])->name('produtos.processExchange');
    Route::get('/produtos/{produto}/available-sizes', [ProdutoController::class, 'getAvailableSizesAjax'])->name('produtos.availableSizes');

     // Rotas do Carrinho
     Route::post('/carrinho/adicionar/{id}', [CarrinhoController::class, 'adicionar'])->name('carrinho.adicionar');
     Route::get('/carrinho', [CarrinhoController::class, 'exibir'])->name('carrinho.index');
     Route::delete('/carrinho/remover/{itemId}', [CarrinhoController::class, 'remover'])->name('carrinho.remover');

    // Rotas para Horarios
    Route::get('/horarios/edit', [HorarioController::class, 'edit'])->name('horarios.edit');
    Route::put('/horarios', [HorarioController::class, 'update'])->name('horarios.update');
     Route::patch('/carrinho/atualizar/{itemId}', [CarrinhoController::class, 'atualizar'])->name('carrinho.atualizar');
     Route::delete('/carrinho/limpar', [CarrinhoController::class, 'limpar'])->name('carrinho.limpar');
     Route::post('/carrinho/aplicar-desconto', [CarrinhoController::class, 'aplicarDesconto'])->name('carrinho.aplicarDesconto');
     Route::post('/carrinho/finalizar-compra', [CarrinhoController::class, 'finalizarCompra'])->name('carrinho.finalizarCompra');

     // Rotas para Venda Crediário
     Route::get('/carrinho/venda-crediario', [CarrinhoController::class, 'vendaCrediario'])->name('carrinho.venda-crediario');
     Route::get('/carrinho/pesquisar-cliente', [CarrinhoController::class, 'exibirPesquisaCliente'])->name('carrinho.pesquisar-cliente');
     Route::post('/carrinho/pesquisar-cliente', [CarrinhoController::class, 'pesquisarCliente']);
     Route::get('/carrinho/selecionar-cliente/{id}', [CarrinhoController::class, 'selecionarCliente'])->name('carrinho.selecionar-cliente');
     Route::get('/carrinho/configurar-venda-crediario', [CarrinhoController::class, 'configurarVendaCrediario'])->name('carrinho.configurar-venda-crediario');
     Route::post('/carrinho/processar-venda-crediario', [CarrinhoController::class, 'processarVendaCrediario'])->name('carrinho.processar-venda-crediario');
     Route::get('/carrinho/finalizar-venda-crediario', [CarrinhoController::class, 'finalizarVendaCrediario'])->name('carrinho.finalizar-venda-crediario');

    // Rotas para Pagamentos de Parcelas
    Route::get('/pagamentos/cliente/{cliente}', [PagamentoController::class, 'show'])->name('pagamentos.show');
    Route::post('/pagamentos/cliente/{cliente}', [PagamentoController::class, 'store'])->name('pagamentos.store');

    // Rotas para Despesas
    Route::get('/despesas/create', [DespesaController::class, 'create'])->name('despesas.create');
    Route::post('/despesas', [DespesaController::class, 'store'])->name('despesas.store');
    
    // Rotas para Despesas Fixas
    Route::get('/despesas/fixa/create', [DespesaController::class, 'createFixa'])->name('despesas.create.fixa');
    Route::post('/despesas/fixa', [DespesaController::class, 'storeFixa'])->name('despesas.store.fixa');
    Route::put('/despesas/fixa/{id}', [DespesaController::class, 'updateFixa'])->name('despesas.update.fixa');
    Route::delete('/despesas/fixa/{id}', [DespesaController::class, 'destroyFixa'])->name('despesas.destroy.fixa');
    
    // Rotas para Verificação de Despesas
    Route::get('/despesas', [DespesaController::class, 'index'])->name('despesas.index');
    Route::post('/despesas/inserir-fixa', [DespesaController::class, 'inserirDespesaFixa'])->name('despesas.inserir.fixa');
    Route::post('/despesas/atualizar', [DespesaController::class, 'atualizarDespesa'])->name('despesas.atualizar');
    Route::post('/despesas/excluir', [DespesaController::class, 'excluirDespesa'])->name('despesas.excluir');

    // Rotas para Fluxo de Caixa
    Route::get('/fluxo-caixa', [FluxoCaixaController::class, 'index'])->name('fluxo-caixa.index');
    Route::post('/fluxo-caixa/relatorio-geral', [FluxoCaixaController::class, 'relatorioGeral'])->name('fluxo-caixa.relatorio-geral');
    Route::get('/fluxo-caixa/individualizado', [FluxoCaixaController::class, 'fluxoIndividualizado'])->name('fluxo-caixa.individualizado');
    Route::post('/fluxo-caixa/relatorio-individualizado', [FluxoCaixaController::class, 'relatorioIndividualizado'])->name('fluxo-caixa.relatorio-individualizado');

    // Rotas para Baixa Fiscal
    Route::get('/baixa-fiscal', [BaixaFiscalController::class, 'index'])->name('baixa_fiscal.index');
    Route::post('/baixa-fiscal/{cidade}/{idVenda}', [BaixaFiscalController::class, 'darBaixa'])->name('baixa_fiscal.dar_baixa');

    // Rotas para Verificação de Limite
    Route::prefix('verificacao-limite')->name('verificacao-limite.')->group(function () {
        Route::get('/', [VerificacaoLimiteController::class, 'index'])->name('index');
        Route::get('/buscar-clientes', [VerificacaoLimiteController::class, 'buscarClientes'])->name('buscar-clientes');
        Route::get('/perfil-cliente/{id}', [VerificacaoLimiteController::class, 'perfilCliente'])->name('perfil-cliente');
        Route::post('/atualizar-limite/{id}', [VerificacaoLimiteController::class, 'atualizarLimite'])->name('atualizar-limite');
        Route::post('/alterar-status/{id}', [VerificacaoLimiteController::class, 'alterarStatus'])->name('alterar-status');
        Route::get('/historico-alteracoes/{id}', [VerificacaoLimiteController::class, 'historicoAlteracoes'])->name('historico-alteracoes');
    });

    // Rotas para Negativação de Clientes (apenas administradores)
    Route::middleware(['admin'])->prefix('negativacao')->name('negativacao.')->group(function () {
        Route::get('/', [App\Http\Controllers\NegativacaoController::class, 'index'])->name('index');
        Route::get('/cliente/{cliente}', [App\Http\Controllers\NegativacaoController::class, 'show'])->name('show');
        Route::post('/negativar/{cliente}', [App\Http\Controllers\NegativacaoController::class, 'negativar'])->name('negativar');
        Route::get('/negativados', [App\Http\Controllers\NegativacaoController::class, 'negativados'])->name('negativados');
        Route::get('/negativado/{cliente}', [App\Http\Controllers\NegativacaoController::class, 'showNegativado'])->name('show-negativado');
        Route::post('/retornar-parcelas/{cliente}', [App\Http\Controllers\NegativacaoController::class, 'retornarParcelas'])->name('retornar-parcelas');
        Route::post('/remover/{cliente}', [App\Http\Controllers\NegativacaoController::class, 'removerNegativacao'])->name('remover');
    });
    
    // Rotas para produtos (protegidas por middleware)
    Route::middleware(['check.product.access'])->group(function () {
        Route::resource('produtos', ProdutoController::class);
        Route::get('/produtos/distribuicao/{id}', [ProdutoController::class, 'distribuicao'])->name('produtos.distribuicao');
        Route::post('/fazerDistribuicao/{id}', [ProdutoController::class, 'processarDistribuicao'])->name('fazerDistribuicao');



        // Rotas para marca
        Route::resource('marcas', MarcaController::class);

        // Rotas para subgrupo
        Route::resource('subgrupos', SubgrupoController::class);

        // Rotas para grupo
        Route::resource('grupos', GrupoController::class);

        // Rotas para Descontos
        Route::get('/descontos/{desconto}/edit', [App\Http\Controllers\DescontoController::class, 'edit'])->name('descontos.edit');
        Route::put('/descontos/{desconto}', [App\Http\Controllers\DescontoController::class, 'update'])->name('descontos.update');

        // Rotas para Configuração de Multas
        Route::get('/multa-configuracao/edit', [App\Http\Controllers\MultaConfiguracaoController::class, 'edit'])->name('multa-configuracao.edit');
        Route::put('/multa-configuracao/update', [App\Http\Controllers\MultaConfiguracaoController::class, 'update'])->name('multa-configuracao.update');

        // Rotas de horários já definidas acima
    });

});
