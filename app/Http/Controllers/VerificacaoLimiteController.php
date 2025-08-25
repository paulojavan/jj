<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\ClienteProfileService;
use App\Services\LimiteManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class VerificacaoLimiteController extends Controller
{
    protected $clienteProfileService;
    protected $limiteManagementService;

    /**
     * Construtor - aplica middleware de autorização
     */
    public function __construct(ClienteProfileService $clienteProfileService, LimiteManagementService $limiteManagementService)
    {
        $this->middleware('verificacao.limite');
        $this->clienteProfileService = $clienteProfileService;
        $this->limiteManagementService = $limiteManagementService;
    }

    /**
     * Exibe a página principal de verificação de limite
     */
    public function index(Request $request)
    {
        return view('verificacao-limite.index');
    }

    /**
     * Busca clientes por nome, apelido ou CPF via AJAX
     */
    public function buscarClientes(Request $request)
    {
        try {
            $termo = $request->get('termo');
            
            if (empty($termo) || strlen($termo) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Digite pelo menos 2 caracteres para buscar'
                ]);
            }

            $query = cliente::query();
            $searchTerm = '%' . $termo . '%';
            
            $clientes = $query->where(function ($q) use ($searchTerm) {
                $q->where('nome', 'like', $searchTerm)
                  ->orWhere('apelido', 'like', $searchTerm)
                  ->orWhere('cpf', 'like', $searchTerm);
            })
            ->select('id', 'nome', 'apelido', 'cpf', 'status', 'limite')
            ->orderBy('nome', 'asc')
            ->limit(10)
            ->get();

            // Formatar CPF para exibição
            $clientes->transform(function ($cliente) {
                $cliente->cpf_formatado = $this->formatarCpf($cliente->cpf);
                return $cliente;
            });

            return response()->json([
                'success' => true,
                'clientes' => $clientes
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Carrega o perfil completo do cliente selecionado
     */
    public function perfilCliente($id)
    {
        try {
            $cliente = cliente::findOrFail($id);

            // Gerar perfis de compras e pagamentos
            $perfilCompras = $this->clienteProfileService->gerarPerfilCompras($id);
            $perfilPagamentos = $this->clienteProfileService->gerarPerfilPagamentos($id);

            // Formatar dados básicos do cliente
            $clienteFormatado = [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
                'apelido' => $cliente->apelido,
                'rg' => $cliente->rg,
                'cpf' => $this->formatarCpf($cliente->cpf),
                'renda' => $cliente->renda ? 'R$ ' . number_format($this->toFloat($cliente->renda), 2, ',', '.') : 'Não informado',
                'status' => $cliente->status,
                'limite_atual' => $cliente->limite ? number_format($this->toFloat($cliente->limite), 2, ',', '.') : '0,00',
                'limite_atual_numerico' => $this->toFloat($cliente->limite ?? 0),
                'foto' => $cliente->foto,
                'pasta' => $cliente->pasta,
                'referencias_comerciais' => [
                    [
                        'nome' => $cliente->referencia_comercial1 ?: 'Não informado',
                        'telefone' => $this->formatarTelefone($cliente->telefone_referencia_comercial1)
                    ],
                    [
                        'nome' => $cliente->referencia_comercial2 ?: 'Não informado',
                        'telefone' => $this->formatarTelefone($cliente->telefone_referencia_comercial2)
                    ],
                    [
                        'nome' => $cliente->referencia_comercial3 ?: 'Não informado',
                        'telefone' => $this->formatarTelefone($cliente->telefone_referencia_comercial3)
                    ]
                ],
                'perfil_compras' => $perfilCompras,
                'perfil_pagamentos' => $perfilPagamentos
            ];

            return response()->json([
                'success' => true,
                'cliente' => $clienteFormatado
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar perfil do cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatar CPF para exibição
     */
    private function formatarCpf($cpf)
    {
        if (empty($cpf)) return 'Não informado';
        
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        
        return $cpf;
    }

    /**
     * Formatar telefone para exibição
     */
    private function formatarTelefone($telefone)
    {
        if (empty($telefone)) return 'Não informado';
        
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7, 4);
        } elseif (strlen($telefone) === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6, 4);
        }
        
        return $telefone;
    }

    /**
     * Atualiza o limite de crédito do cliente via AJAX
     */
    public function atualizarLimite(Request $request, $id)
    {
        try {
            $request->validate([
                'limite' => 'required|numeric|min:0'
            ]);

            $novoLimite = $request->input('limite');
            $resultado = $this->limiteManagementService->atualizarLimite($id, $novoLimite);

            if ($resultado['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $resultado['message'],
                    'limite_formatado' => number_format($this->toFloat($novoLimite), 2, ',', '.'),
                    'limite_numerico' => $novoLimite
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message']
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Altera o status ativo/inativo do cliente via AJAX
     */
    public function alterarStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:ativo,inativo'
            ]);

            $novoStatus = $request->input('status');
            $resultado = $this->limiteManagementService->alterarStatus($id, $novoStatus);

            if ($resultado['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $resultado['message'],
                    'status' => $novoStatus
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message']
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtém histórico de alterações do cliente
     */
    public function historicoAlteracoes($id)
    {
        try {
            $resultado = $this->limiteManagementService->obterHistoricoAlteracoes($id, 20);

            if ($resultado['success']) {
                // Formatar logs para exibição
                $logsFormatados = $resultado['logs']->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'acao' => $log->acao,
                        'valor_anterior' => $log->valor_anterior,
                        'valor_novo' => $log->valor_novo,
                        'observacoes' => $log->observacoes,
                        'usuario_nome' => $log->usuario ? $log->usuario->name : 'Usuário não encontrado',
                        'data_formatada' => $log->created_at->format('d/m/Y H:i:s'),
                        'data_relativa' => $log->created_at->diffForHumans()
                    ];
                });

                return response()->json([
                    'success' => true,
                    'logs' => $logsFormatados
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message']
                ], 400);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar histórico: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Converte valor para float de forma segura
     */
    private function toFloat($value)
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }
        
        // Se já é numérico, converte diretamente
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        // Se é string, tenta limpar e converter
        if (is_string($value)) {
            // Remove caracteres não numéricos exceto vírgula e ponto
            $cleaned = preg_replace('/[^\d,.-]/', '', $value);
            
            // Substitui vírgula por ponto para conversão
            $cleaned = str_replace(',', '.', $cleaned);
            
            // Se ainda não é numérico após limpeza, retorna 0
            if (!is_numeric($cleaned)) {
                return 0.0;
            }
            
            return (float) $cleaned;
        }
        
        // Para outros tipos, tenta conversão direta ou retorna 0
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
