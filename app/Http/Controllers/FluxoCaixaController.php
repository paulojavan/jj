<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\FluxoCaixaService;
use App\Http\Requests\FluxoCaixaRequest;
use App\Models\User;

class FluxoCaixaController extends Controller
{
    protected $fluxoCaixaService;

    public function __construct(FluxoCaixaService $fluxoCaixaService)
    {
        $this->middleware('auth');
        $this->fluxoCaixaService = $fluxoCaixaService;
    }

    /**
     * Exibe a página principal do fluxo geral
     */
    public function index()
    {
        $user = Auth::user();
        $permiteEscolherPeriodo = $this->usuarioPermiteEscolherPeriodo($user);
        
        return view('fluxo-caixa.index', compact('user', 'permiteEscolherPeriodo'));
    }

    /**
     * Processa e exibe relatório geral
     */
    public function relatorioGeral(FluxoCaixaRequest $request)
    {
        $user = Auth::user();
        $permiteEscolherPeriodo = $this->usuarioPermiteEscolherPeriodo($user);
        
        try {
            // Obter dados validados com defaults aplicados
            $dadosValidados = $request->validatedWithDefaults();
            
            Log::info('Gerando relatório geral de fluxo de caixa', [
                'usuario' => $user->id,
                'periodo' => $dadosValidados,
                'nivel' => $user->nivel
            ]);
            
            $dados = $this->fluxoCaixaService->obterDadosFluxoGeral(
                $dadosValidados['data_inicio'], 
                $dadosValidados['data_fim'], 
                $user
            );

            return view('fluxo-caixa.index', [
                'user' => $user,
                'dados' => $dados,
                'dataInicio' => $dadosValidados['data_inicio'],
                'dataFim' => $dadosValidados['data_fim'],
                'permiteEscolherPeriodo' => $permiteEscolherPeriodo
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório geral de fluxo de caixa', [
                'usuario' => $user->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao gerar relatório: ' . $this->obterMensagemErroAmigavel($e));
        }
    }

    /**
     * Exibe página do fluxo individualizado
     */
    public function fluxoIndividualizado()
    {
        $user = Auth::user();
        $vendedoresDisponiveis = $this->obterVendedoresDisponiveis($user);
        
        return view('fluxo-caixa.individualizado', compact('user', 'vendedoresDisponiveis'));
    }

    /**
     * Processa relatório individual
     */
    public function relatorioIndividualizado(FluxoCaixaRequest $request)
    {
        $user = Auth::user();
        
        try {
            $dadosValidados = $request->validated();
            
            Log::info('Gerando relatório individual de fluxo de caixa', [
                'usuario' => $user->id,
                'vendedor_selecionado' => $dadosValidados['vendedor_id'],
                'periodo' => [
                    'inicio' => $dadosValidados['data_inicio'],
                    'fim' => $dadosValidados['data_fim']
                ]
            ]);

            $dados = $this->fluxoCaixaService->obterDadosFluxoIndividual(
                $dadosValidados['data_inicio'], 
                $dadosValidados['data_fim'], 
                $dadosValidados['vendedor_id'], 
                $user
            );
            
            $vendedoresDisponiveis = $this->obterVendedoresDisponiveis($user);

            return view('fluxo-caixa.individualizado', [
                'user' => $user,
                'dados' => $dados,
                'dataInicio' => $dadosValidados['data_inicio'],
                'dataFim' => $dadosValidados['data_fim'],
                'vendedorId' => $dadosValidados['vendedor_id'],
                'vendedoresDisponiveis' => $vendedoresDisponiveis
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório individual de fluxo de caixa', [
                'usuario' => $user->id,
                'dados_request' => $request->all(),
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao gerar relatório individual: ' . $this->obterMensagemErroAmigavel($e));
        }
    }

    /**
     * Verifica se o usuário tem permissão para escolher período personalizado
     */
    private function usuarioPermiteEscolherPeriodo($user)
    {
        return $user->nivel === 'administrador';
    }

    /**
     * Obtém regras de validação para datas baseadas nas permissões
     */
    private function obterRegrasValidacaoData($permiteEscolherPeriodo)
    {
        if ($permiteEscolherPeriodo) {
            return [
                'data_inicio' => 'nullable|date',
                'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            ];
        }

        return []; // Vendedores não podem escolher período
    }

    /**
     * Aplica filtros de período baseados no nível do usuário
     */
    private function aplicarFiltrosPeriodo($request, $user)
    {
        if ($user->nivel === 'administrador') {
            // Administrador pode escolher período ou usar padrão (dia atual)
            return [
                'data_inicio' => $request->input('data_inicio', now()->format('Y-m-d')),
                'data_fim' => $request->input('data_fim', now()->format('Y-m-d'))
            ];
        } else {
            // Vendedores só veem o dia atual
            $hoje = now()->format('Y-m-d');
            return [
                'data_inicio' => $hoje,
                'data_fim' => $hoje
            ];
        }
    }

    /**
     * Valida se o usuário tem permissão para ver dados de um vendedor específico
     */
    private function validarPermissaoVendedor($user, $vendedorId)
    {
        if ($user->nivel !== 'administrador' && $user->id != $vendedorId) {
            throw new \Exception('Você não tem permissão para visualizar dados deste vendedor.');
        }
    }

    /**
     * Obtém lista de vendedores disponíveis baseada nas permissões do usuário
     */
    private function obterVendedoresDisponiveis($user)
    {
        if ($user->nivel === 'administrador') {
            // Administrador vê todos os vendedores ativos, exceto outros administradores
            return User::where('status', 'ativo')
                      ->where('nivel', '!=', 'administrador')
                      ->orderBy('name')
                      ->get();
        } else {
            // Vendedor vê apenas a si mesmo
            return collect([$user]);
        }
    }



    /**
     * Converte exceções técnicas em mensagens amigáveis para o usuário
     */
    private function obterMensagemErroAmigavel(\Exception $e): string
    {
        $mensagem = $e->getMessage();
        
        // Erros de tabela não encontrada
        if (str_contains($mensagem, 'Table') && str_contains($mensagem, "doesn't exist")) {
            return 'Dados não disponíveis para a cidade selecionada. Entre em contato com o administrador.';
        }
        
        // Erros de conexão com banco
        if (str_contains($mensagem, 'Connection') || str_contains($mensagem, 'SQLSTATE')) {
            return 'Erro de conexão com o banco de dados. Tente novamente em alguns instantes.';
        }
        
        // Erros de permissão
        if (str_contains($mensagem, 'permissão') || str_contains($mensagem, 'autorizado')) {
            return $mensagem; // Já é uma mensagem amigável
        }
        
        // Erros de validação de dados
        if (str_contains($mensagem, 'não encontrado') || str_contains($mensagem, 'inválido')) {
            return $mensagem; // Já é uma mensagem amigável
        }
        
        // Erro genérico para casos não mapeados
        return 'Ocorreu um erro inesperado. Tente novamente ou entre em contato com o suporte.';
    }

    /**
     * Valida se existem dados mínimos necessários para gerar o relatório
     */
    private function validarDadosMinimos($dados): void
    {
        if (empty($dados)) {
            throw new \Exception('Nenhum dado encontrado para os filtros selecionados.');
        }
        
        // Para relatório geral
        if (isset($dados['cidades']) && empty($dados['cidades'])) {
            throw new \Exception('Nenhuma cidade com dados no período selecionado.');
        }
        
        // Para relatório individual
        if (isset($dados['vendedor']) && !$dados['vendedor']) {
            throw new \Exception('Vendedor não encontrado ou sem permissão de acesso.');
        }
    }
}