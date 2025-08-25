<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Parcela;
use App\Models\Ticket;
use App\Services\CalculoParcelaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NegativacaoController extends Controller
{
    protected $calculoService;

    public function __construct(CalculoParcelaService $calculoService)
    {
        $this->calculoService = $calculoService;
    }

    /**
     * Lista clientes elegíveis para negativação
     */
    public function index()
    {
        $clientes = Cliente::whereHas('parcelas', function ($query) {
            $query->where('status', 'aguardando pagamento')
                  ->where('data_vencimento', '<', Carbon::now()->subDays(60));
        })
        ->whereDoesntHave('tickets', function ($query) {
            $query->where('spc', true);
        })
        ->where('status', '!=', 'inativo')
        ->with(['parcelas' => function ($query) {
            $query->where('status', 'aguardando pagamento')
                  ->where('data_vencimento', '<', Carbon::now()->subDays(60));
        }])
        ->paginate(20);

        // Calcular valores para cada cliente
        foreach ($clientes as $cliente) {
            $valorTotal = 0;
            $quantidadeParcelas = 0;
            
            foreach ($cliente->parcelas as $parcela) {
                $valorTotal += $this->calculoService->calcularValorComJurosMulta($parcela);
                $quantidadeParcelas++;
            }
            
            $cliente->valor_total_negativacao = $valorTotal;
            $cliente->quantidade_parcelas_atraso = $quantidadeParcelas;
        }

        return view('negativacao.index', compact('clientes'));
    }

    /**
     * Exibe detalhes de um cliente elegível para negativação
     */
    public function show(Cliente $cliente)
    {
        // Verificar se o cliente é elegível
        if (!$cliente->isElegivelNegativacao()) {
            return redirect()->route('negativacao.index')
                ->with('error', 'Cliente não é elegível para negativação.');
        }

        // Buscar parcelas do titular em atraso
        $parcelasTitular = $cliente->parcelas()
            ->whereNull('id_autorizado')
            ->where('status', 'aguardando pagamento')
            ->where('data_vencimento', '<', Carbon::now()->subDays(60))
            ->orderBy('data_vencimento')
            ->get();

        // Buscar parcelas dos autorizados em atraso
        $parcelasAutorizados = $cliente->parcelas()
            ->whereNotNull('id_autorizado')
            ->where('status', 'aguardando pagamento')
            ->where('data_vencimento', '<', Carbon::now()->subDays(60))
            ->with('autorizado')
            ->orderBy('data_vencimento')
            ->get()
            ->groupBy('id_autorizado');

        // Calcular valores com juros e multa
        $valorTotalTitular = 0;
        foreach ($parcelasTitular as $parcela) {
            $calculo = $this->calculoService->calcularValorDetalhado($parcela);
            $parcela->valor_calculado = $calculo;
            $valorTotalTitular += $calculo->valor_total;
        }

        $valorTotalAutorizados = 0;
        foreach ($parcelasAutorizados as $autorizadoId => $parcelas) {
            foreach ($parcelas as $parcela) {
                $calculo = $this->calculoService->calcularValorDetalhado($parcela);
                $parcela->valor_calculado = $calculo;
                $valorTotalAutorizados += $calculo->valor_total;
            }
        }

        $valorTotalGeral = $valorTotalTitular + $valorTotalAutorizados;

        return view('negativacao.show', compact(
            'cliente', 
            'parcelasTitular', 
            'parcelasAutorizados',
            'valorTotalTitular',
            'valorTotalAutorizados', 
            'valorTotalGeral'
        ));
    }

    /**
     * Processa a negativação de um cliente
     */
    public function negativar(Cliente $cliente)
    {
        // Verificar se o cliente é elegível
        if (!$cliente->isElegivelNegativacao()) {
            return redirect()->back()
                ->with('error', 'Cliente não é elegível para negativação.');
        }

        DB::beginTransaction();
        try {
            // 1. Buscar parcelas aguardando pagamento
            $parcelas = $cliente->parcelas()
                ->where('status', 'aguardando pagamento')
                ->get();

            if ($parcelas->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'Nenhuma parcela encontrada para negativação.');
            }

            // 2. Buscar tickets relacionados às parcelas
            $ticketsIds = $parcelas->pluck('ticket')->unique()->filter();

            if ($ticketsIds->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'Nenhum ticket encontrado para as parcelas.');
            }

            // 3. Atualizar campo spc nos tickets
            $ticketsAtualizados = Ticket::whereIn('ticket', $ticketsIds)
                ->update(['spc' => true]);

            // 4. Atualizar status do cliente
            $cliente->update([
                'status' => 'inativo',
                'obs' => 'cliente negativado'
            ]);

            // Log da operação
            Log::info('Cliente negativado', [
                'cliente_id' => $cliente->id,
                'cliente_nome' => $cliente->nome,
                'tickets_afetados' => $ticketsIds->toArray(),
                'quantidade_tickets' => $ticketsAtualizados,
                'usuario' => auth()->user()->nome ?? 'Sistema'
            ]);

            DB::commit();

            return redirect()->route('negativacao.negativados')
                ->with('success', "Cliente {$cliente->nome} foi negativado com sucesso. {$ticketsAtualizados} compra(s) foram marcadas no SPC.");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erro ao negativar cliente', [
                'cliente_id' => $cliente->id,
                'erro' => $e->getMessage(),
                'usuario' => auth()->user()->nome ?? 'Sistema'
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao processar negativação. Tente novamente.');
        }
    }

    /**
     * Lista clientes negativados (baseado em tickets com spc = true)
     */
    public function negativados()
    {
        // Buscar clientes que têm tickets com spc = true
        $clientesIds = Ticket::where('spc', true)
            ->distinct()
            ->pluck('id_cliente')
            ->filter(); // Remove valores null

        $clientes = Cliente::whereIn('id', $clientesIds)
            ->with(['tickets' => function ($query) {
                $query->where('spc', true);
            }])
            ->paginate(20);

        // Calcular informações adicionais para cada cliente
        foreach ($clientes as $cliente) {
            // Buscar parcelas não pagas de tickets negativados
            $parcelasNegativadas = $cliente->parcelas()
                ->whereHas('ticket', function ($query) {
                    $query->where('spc', true);
                })
                ->where('status', '!=', 'pago')
                ->get();

            // Buscar parcelas com id_vendedor = 1 de tickets negativados (não retornadas ao sistema)
            $parcelasNaoRetornadas = $cliente->parcelas()
                ->whereHas('ticket', function ($query) {
                    $query->where('spc', true);
                })
                ->where('id_vendedor', 1)
                ->get();

            $valorTotal = 0;
            $totalParcelas = 0;

            // Contabilizar parcelas não pagas
            foreach ($parcelasNegativadas as $parcela) {
                $valorTotal += $this->calculoService->calcularValorComJurosMulta($parcela);
                $totalParcelas++;
            }

            // Contabilizar parcelas não retornadas (id_vendedor = 1)
            foreach ($parcelasNaoRetornadas as $parcela) {
                // Evitar contabilizar a mesma parcela duas vezes
                if (!$parcelasNegativadas->contains('id', $parcela->id)) {
                    $valorTotal += $this->calculoService->calcularValorComJurosMulta($parcela);
                    $totalParcelas++;
                }
            }

            $cliente->valor_total_negativado = $valorTotal;
            $cliente->quantidade_parcelas_negativadas = $totalParcelas;
            
            // Data da negativação (buscar a data do primeiro ticket negativado)
            $primeiroTicketNegativado = $cliente->tickets()
                ->where('spc', true)
                ->orderBy('data')
                ->first();
            
            $cliente->data_negativacao = $primeiroTicketNegativado ? $primeiroTicketNegativado->data : null;
        }

        return view('negativacao.negativados', compact('clientes'));
    }

    /**
     * Exibe detalhes de um cliente negativado
     */
    public function showNegativado(Cliente $cliente)
    {
        // Verificar se o cliente tem tickets negativados
        $temTicketsNegativados = $cliente->tickets()->where('spc', true)->exists();
        
        if (!$temTicketsNegativados) {
            return redirect()->route('negativacao.negativados')
                ->with('error', 'Cliente não possui tickets negativados.');
        }

        // Buscar parcelas não pagas do titular de tickets negativados
        $parcelasTitular = $cliente->parcelas()
            ->whereNull('id_autorizado')
            ->whereHas('ticket', function ($query) {
                $query->where('spc', true);
            })
            ->where('status', '!=', 'pago')
            ->orderBy('data_vencimento')
            ->get();

        // Buscar parcelas não pagas dos autorizados de tickets negativados
        $parcelasAutorizados = $cliente->parcelas()
            ->whereNotNull('id_autorizado')
            ->whereHas('ticket', function ($query) {
                $query->where('spc', true);
            })
            ->where('status', '!=', 'pago')
            ->with('autorizado')
            ->orderBy('data_vencimento')
            ->get()
            ->groupBy('id_autorizado');

        // Buscar parcelas com id_vendedor = 1 de tickets negativados (não retornadas ao sistema)
        $parcelasNaoRetornadas = $cliente->parcelas()
            ->whereHas('ticket', function ($query) {
                $query->where('spc', true);
            })
            ->where('id_vendedor', 1)
            ->orderBy('data_vencimento')
            ->get();

        // Calcular valores atualizados com juros e multa
        $valorTotalTitular = 0;
        foreach ($parcelasTitular as $parcela) {
            $calculo = $this->calculoService->calcularValorDetalhado($parcela);
            $parcela->valor_calculado = $calculo;
            $valorTotalTitular += $calculo->valor_total;
        }

        $valorTotalAutorizados = 0;
        foreach ($parcelasAutorizados as $autorizadoId => $parcelas) {
            foreach ($parcelas as $parcela) {
                $calculo = $this->calculoService->calcularValorDetalhado($parcela);
                $parcela->valor_calculado = $calculo;
                $valorTotalAutorizados += $calculo->valor_total;
            }
        }

        // Calcular valores das parcelas não retornadas (id_vendedor = 1)
        $valorTotalNaoRetornadas = 0;
        foreach ($parcelasNaoRetornadas as $parcela) {
            $calculo = $this->calculoService->calcularValorDetalhado($parcela);
            $parcela->valor_calculado = $calculo;
            $valorTotalNaoRetornadas += $calculo->valor_total;
        }

        $valorTotalGeral = $valorTotalTitular + $valorTotalAutorizados + $valorTotalNaoRetornadas;

        // Buscar compras negativadas (tickets com spc = true)
        $comprasNegativadas = $cliente->tickets()
            ->where('spc', true)
            ->orderBy('data', 'desc')
            ->get();

        return view('negativacao.show-negativado', compact(
            'cliente',
            'parcelasTitular',
            'parcelasAutorizados',
            'parcelasNaoRetornadas',
            'valorTotalTitular',
            'valorTotalAutorizados',
            'valorTotalNaoRetornadas',
            'valorTotalGeral',
            'comprasNegativadas'
        ));
    }

    /**
     * Retorna parcelas de um cliente negativado
     */
    public function retornarParcelas(Cliente $cliente)
    {
        // Verificar se o cliente tem tickets negativados
        $temTicketsNegativados = $cliente->tickets()->where('spc', true)->exists();
        
        if (!$temTicketsNegativados) {
            return redirect()->back()
                ->with('error', 'Cliente não possui tickets negativados.');
        }

        DB::beginTransaction();
        try {
            // Buscar parcelas de tickets negativados com id_vendedor = 1
            $parcelas = $cliente->parcelas()
                ->whereHas('ticket', function ($query) {
                    $query->where('spc', true);
                })
                ->where('id_vendedor', 1)
                ->get();

            if ($parcelas->isEmpty()) {
                return redirect()->back()
                    ->with('warning', 'Nenhuma parcela encontrada para retorno.');
            }

            $parcelasAfetadas = 0;
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
                $parcelasAfetadas++;
            }

            // Log da operação
            Log::info('Parcelas retornadas', [
                'cliente_id' => $cliente->id,
                'cliente_nome' => $cliente->nome,
                'parcelas_afetadas' => $parcelasAfetadas,
                'usuario' => auth()->user()->nome ?? 'Sistema'
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', "{$parcelasAfetadas} parcela(s) foram retornadas com sucesso.");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erro ao retornar parcelas', [
                'cliente_id' => $cliente->id,
                'erro' => $e->getMessage(),
                'usuario' => auth()->user()->nome ?? 'Sistema'
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao retornar parcelas. Tente novamente.');
        }
    }

    /**
     * Remove a negativação de um cliente
     */
    public function removerNegativacao(Cliente $cliente)
    {
        // Verificar se o cliente tem tickets negativados
        $temTicketsNegativados = $cliente->tickets()->where('spc', true)->exists();
        
        if (!$temTicketsNegativados) {
            return redirect()->back()
                ->with('error', 'Cliente não possui tickets negativados.');
        }

        DB::beginTransaction();
        try {
            // Buscar todos os tickets com spc = true do cliente
            $tickets = $cliente->tickets()
                ->where('spc', true)
                ->get();

            if ($tickets->isEmpty()) {
                return redirect()->back()
                    ->with('warning', 'Nenhum ticket negativado encontrado.');
            }

            $ticketsAfetados = 0;
            foreach ($tickets as $ticket) {
                $ticket->update(['spc' => null]);
                $ticketsAfetados++;
            }

            // Reativar o cliente
            $cliente->update([
                'status' => 'ativo',
                'obs' => null
            ]);

            // Log da operação
            Log::info('Negativação removida', [
                'cliente_id' => $cliente->id,
                'cliente_nome' => $cliente->nome,
                'tickets_afetados' => $ticketsAfetados,
                'usuario' => auth()->user()->nome ?? 'Sistema'
            ]);

            DB::commit();

            return redirect()->route('negativacao.negativados')
                ->with('success', "Negativação do cliente {$cliente->nome} foi removida com sucesso. {$ticketsAfetados} compra(s) foram retiradas do SPC.");

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erro ao remover negativação', [
                'cliente_id' => $cliente->id,
                'erro' => $e->getMessage(),
                'usuario' => auth()->user()->nome ?? 'Sistema'
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao remover negativação. Tente novamente.');
        }
    }
}