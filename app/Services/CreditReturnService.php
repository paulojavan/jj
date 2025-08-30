<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Parcela;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class CreditReturnService
{
    /**
     * Verifica se um ticket pode ser devolvido
     */
    public function canReturn(string $ticketNumber): bool
    {
        try {
            // Buscar o ticket
            $ticket = Ticket::where('ticket', $ticketNumber)->first();
            
            if (!$ticket) {
                return false;
            }

            // Verificar se já foi devolvido
            if ($ticket->isDevolvida()) {
                return false;
            }

            // Verificar se alguma parcela foi paga (status diferente de 'aguardando pagamento')
            $parcelasPagas = $ticket->parcelasRelacao()
                ->where('status', '!=', 'aguardando pagamento')
                ->where('status', '!=', 'devolucao') // Excluir parcelas já devolvidas
                ->count();

            return $parcelasPagas === 0;

        } catch (Exception $e) {
            Log::error('Erro ao verificar se ticket pode ser devolvido: ' . $e->getMessage(), [
                'ticket' => $ticketNumber
            ]);
            return false;
        }
    }

    /**
     * Processa a devolução completa de um ticket
     */
    public function processReturn(string $ticketNumber): array
    {
        try {
            DB::beginTransaction();

            // Verificar se pode ser devolvido
            if (!$this->canReturn($ticketNumber)) {
                throw new Exception('Este ticket não pode ser devolvido');
            }

            // Buscar o ticket
            $ticket = Ticket::where('ticket', $ticketNumber)->first();
            
            if (!$ticket) {
                throw new Exception('Ticket não encontrado');
            }

            // Atualizar parcelas
            $this->updateParcelas($ticketNumber);

            // Atualizar registros de vendas
            $this->updateSalesRecords($ticketNumber);

            // Atualizar estoque
            $this->updateInventory($ticketNumber);

            // Log da operação
            Log::info('Devolução processada com sucesso', [
                'ticket' => $ticketNumber,
                'cliente_id' => $ticket->id_cliente,
                'valor' => $ticket->valor,
                'user_id' => auth()->id() ?? 'system'
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Devolução processada com sucesso',
                'ticket' => $ticketNumber
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao processar devolução: ' . $e->getMessage(), [
                'ticket' => $ticketNumber,
                'user_id' => auth()->id() ?? 'system'
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'ticket' => $ticketNumber
            ];
        }
    }

    /**
     * Atualiza o status das parcelas para devolução
     */
    public function updateParcelas(string $ticketNumber): void
    {
        $updated = Parcela::where('ticket', $ticketNumber)
            ->where('status', 'aguardando pagamento')
            ->update(['status' => 'devolucao']);

        if ($updated === 0) {
            throw new Exception('Nenhuma parcela foi atualizada para devolução');
        }

        Log::info('Parcelas atualizadas para devolução', [
            'ticket' => $ticketNumber,
            'parcelas_atualizadas' => $updated
        ]);
    }

    /**
     * Atualiza os registros de vendas com data de estorno e baixa fiscal
     */
    public function updateSalesRecords(string $ticketNumber): void
    {
        // Buscar a primeira parcela para determinar a cidade/bd
        $parcela = Parcela::where('ticket', $ticketNumber)->first();
        
        if (!$parcela || !$parcela->bd) {
            throw new Exception('Não foi possível determinar a cidade da venda');
        }

        // Determinar a tabela de vendas
        $tabelaVendas = $this->determinarTabelaVendas($parcela->bd);
        
        if (!$tabelaVendas) {
            throw new Exception('Tabela de vendas não encontrada para a cidade: ' . $parcela->bd);
        }

        // Verificar se a tabela existe
        if (!DB::getSchemaBuilder()->hasTable($tabelaVendas)) {
            throw new Exception('Tabela de vendas não existe: ' . $tabelaVendas);
        }

        // Atualizar registros de vendas
        $updated = DB::table($tabelaVendas)
            ->where('ticket', $ticketNumber)
            ->update([
                'data_estorno' => Carbon::now()->toDateString(),
                'baixa_fiscal' => true
            ]);

        if ($updated === 0) {
            throw new Exception('Nenhum registro de venda foi encontrado para o ticket');
        }

        Log::info('Registros de vendas atualizados', [
            'ticket' => $ticketNumber,
            'tabela' => $tabelaVendas,
            'registros_atualizados' => $updated
        ]);
    }

    /**
     * Atualiza o estoque dos produtos devolvidos
     */
    public function updateInventory(string $ticketNumber): void
    {
        // Buscar a primeira parcela para determinar a cidade/bd
        $parcela = Parcela::where('ticket', $ticketNumber)->first();
        
        if (!$parcela || !$parcela->bd) {
            throw new Exception('Não foi possível determinar a cidade da venda');
        }

        // Determinar as tabelas
        $tabelaVendas = $this->determinarTabelaVendas($parcela->bd);
        $tabelaEstoque = $this->determinarTabelaEstoque($parcela->bd);
        
        if (!$tabelaVendas || !$tabelaEstoque) {
            throw new Exception('Tabelas não encontradas para a cidade: ' . $parcela->bd);
        }

        // Verificar se as tabelas existem
        if (!DB::getSchemaBuilder()->hasTable($tabelaVendas) || !DB::getSchemaBuilder()->hasTable($tabelaEstoque)) {
            throw new Exception('Uma ou mais tabelas não existem');
        }

        // Buscar produtos vendidos com suas numerações específicas
        $vendas = DB::table($tabelaVendas)
            ->where('ticket', $ticketNumber)
            ->select('id_produto', 'numeracao', DB::raw('COUNT(*) as quantidade'))
            ->groupBy('id_produto', 'numeracao')
            ->get();

        if ($vendas->isEmpty()) {
            throw new Exception('Nenhuma venda encontrada para o ticket');
        }

        // Atualizar estoque para cada produto e numeração específica
        foreach ($vendas as $venda) {
            // Verificar se já existe registro para esta numeração
            $estoqueExistente = DB::table($tabelaEstoque)
                ->where('id_produto', $venda->id_produto)
                ->where('numero', $venda->numeracao)
                ->first();

            if ($estoqueExistente) {
                // Atualizar estoque existente
                DB::table($tabelaEstoque)
                    ->where('id_produto', $venda->id_produto)
                    ->where('numero', $venda->numeracao)
                    ->increment('quantidade', $venda->quantidade);
            } else {
                // Criar novo registro de estoque para esta numeração
                DB::table($tabelaEstoque)->insert([
                    'id_produto' => $venda->id_produto,
                    'numero' => $venda->numeracao,
                    'quantidade' => $venda->quantidade
                ]);
            }

            Log::info('Estoque atualizado por numeração', [
                'ticket' => $ticketNumber,
                'produto_id' => $venda->id_produto,
                'numeracao' => $venda->numeracao,
                'quantidade_devolvida' => $venda->quantidade,
                'tabela_estoque' => $tabelaEstoque
            ]);
        }
    }

    /**
     * Determina a tabela de vendas baseada no campo bd
     */
    private function determinarTabelaVendas(string $bd): ?string
    {
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
     * Determina a tabela de estoque baseada no campo bd
     */
    private function determinarTabelaEstoque(string $bd): ?string
    {
        $mapeamento = [
            'tabira' => 'estoque_tabira',
            'princesa' => 'estoque_princesa',
            'vendas_tabira' => 'estoque_tabira',
            'vendas_princesa' => 'estoque_princesa'
        ];

        $bdLower = strtolower($bd);
        return $mapeamento[$bdLower] ?? null;
    }
}