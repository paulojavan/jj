<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\LimiteLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class LimiteManagementService
{
    /**
     * Atualiza o limite de crédito do cliente
     */
    public function atualizarLimite($clienteId, $novoLimite, $usuarioId = null)
    {
        try {
            DB::beginTransaction();

            // Validar entrada
            if (!is_numeric($novoLimite) || $novoLimite < 0) {
                throw new Exception('O valor do limite deve ser um número positivo.');
            }

            $cliente = cliente::findOrFail($clienteId);
            $limiteAnterior = $cliente->limite;
            $usuarioId = $usuarioId ?? Auth::id();

            // Validar se o usuário tem permissão
            if (!$usuarioId) {
                throw new Exception('Usuário não autenticado.');
            }

            // Atualizar o limite
            $cliente->update([
                'limite' => $novoLimite,
                'atualizacao' => now()
            ]);

            // Registrar log da alteração
            $this->registrarLog(
                $clienteId,
                'limite_alterado',
                $limiteAnterior,
                $novoLimite,
                $usuarioId,
                "Limite alterado de R$ " . number_format($limiteAnterior, 2, ',', '.') . 
                " para R$ " . number_format($novoLimite, 2, ',', '.')
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Limite atualizado com sucesso!',
                'limite_anterior' => $limiteAnterior,
                'limite_novo' => $novoLimite
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Erro ao atualizar limite: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Altera o status ativo/inativo do cliente
     */
    public function alterarStatus($clienteId, $novoStatus, $usuarioId = null)
    {
        try {
            DB::beginTransaction();

            // Validar entrada
            if (!in_array($novoStatus, ['ativo', 'inativo'])) {
                throw new Exception('Status deve ser "ativo" ou "inativo".');
            }

            $cliente = cliente::findOrFail($clienteId);
            $statusAnterior = $cliente->status;
            $usuarioId = $usuarioId ?? Auth::id();

            // Validar se o usuário tem permissão
            if (!$usuarioId) {
                throw new Exception('Usuário não autenticado.');
            }

            // Verificar se realmente houve mudança
            if ($statusAnterior === $novoStatus) {
                return [
                    'success' => true,
                    'message' => 'Status já estava definido como ' . $novoStatus,
                    'status_anterior' => $statusAnterior,
                    'status_novo' => $novoStatus
                ];
            }

            // Atualizar o status
            $cliente->update([
                'status' => $novoStatus,
                'atualizacao' => now()
            ]);

            // Aplicar regras de negócio para clientes inativos
            if ($novoStatus === 'inativo') {
                $this->aplicarRegrasClienteInativo($cliente);
            }

            // Registrar log da alteração
            $this->registrarLog(
                $clienteId,
                'status_alterado',
                $statusAnterior,
                $novoStatus,
                $usuarioId,
                "Status alterado de '{$statusAnterior}' para '{$novoStatus}'"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Status atualizado com sucesso!',
                'status_anterior' => $statusAnterior,
                'status_novo' => $novoStatus
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Erro ao alterar status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Registra log de auditoria para alterações
     */
    public function registrarLog($clienteId, $acao, $valorAnterior, $valorNovo, $usuarioId, $observacoes = null)
    {
        try {
            LimiteLog::create([
                'cliente_id' => $clienteId,
                'usuario_id' => $usuarioId,
                'acao' => $acao,
                'valor_anterior' => $valorAnterior,
                'valor_novo' => $valorNovo,
                'observacoes' => $observacoes
            ]);

            return true;

        } catch (Exception $e) {
            \Log::error('Erro ao registrar log de limite: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Aplica regras de negócio para clientes inativos
     */
    private function aplicarRegrasClienteInativo($cliente)
    {
        // Aqui podem ser implementadas regras específicas para clientes inativos
        // Por exemplo: bloquear novas vendas, enviar notificações, etc.
        
        // Por enquanto, apenas registrar no log
        \Log::info("Cliente {$cliente->nome} (ID: {$cliente->id}) foi definido como inativo.");
    }

    /**
     * Obtém histórico de alterações de um cliente
     */
    public function obterHistoricoAlteracoes($clienteId, $limite = 50)
    {
        try {
            $logs = LimiteLog::byCliente($clienteId)
                ->with(['usuario:id,name'])
                ->recent()
                ->limit($limite)
                ->get();

            return [
                'success' => true,
                'logs' => $logs
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao obter histórico: ' . $e->getMessage(),
                'logs' => []
            ];
        }
    }

    /**
     * Valida se o usuário tem permissão para alterar limites
     */
    public function validarPermissao($usuarioId = null)
    {
        try {
            $usuarioId = $usuarioId ?? Auth::id();
            
            if (!$usuarioId) {
                return false;
            }

            $user = Auth::user();
            
            // Verificar se é admin ou tem permissão de limite
            $isAdmin = $user->nivel === 'admin';
            $hasLimitePermission = $user->limite === true || $user->limite === 1 || $user->limite === '1';

            return $isAdmin || $hasLimitePermission;

        } catch (Exception $e) {
            \Log::error('Erro ao validar permissão: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém estatísticas de alterações de limite
     */
    public function obterEstatisticasAlteracoes($periodo = 30)
    {
        try {
            $dataInicio = now()->subDays($periodo);

            $stats = [
                'total_alteracoes_limite' => LimiteLog::byAcao('limite_alterado')
                    ->where('created_at', '>=', $dataInicio)
                    ->count(),
                
                'total_alteracoes_status' => LimiteLog::byAcao('status_alterado')
                    ->where('created_at', '>=', $dataInicio)
                    ->count(),
                
                'usuarios_mais_ativos' => LimiteLog::select('usuario_id', DB::raw('count(*) as total'))
                    ->with(['usuario:id,name'])
                    ->where('created_at', '>=', $dataInicio)
                    ->groupBy('usuario_id')
                    ->orderBy('total', 'desc')
                    ->limit(5)
                    ->get(),
                
                'clientes_mais_alterados' => LimiteLog::select('cliente_id', DB::raw('count(*) as total'))
                    ->with(['cliente:id,nome'])
                    ->where('created_at', '>=', $dataInicio)
                    ->groupBy('cliente_id')
                    ->orderBy('total', 'desc')
                    ->limit(5)
                    ->get()
            ];

            return [
                'success' => true,
                'periodo_dias' => $periodo,
                'estatisticas' => $stats
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage(),
                'estatisticas' => []
            ];
        }
    }
}