<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\Parcela;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentProfileService
{
    /**
     * Calcula o perfil completo de pagamento do cliente
     */
    public function calculateProfile(int $clienteId): array
    {
        $cliente = Cliente::findOrFail($clienteId);
        
        return [
            'payment_behavior' => $this->calculatePaymentBehavior($clienteId),
            'return_rate' => $this->calculateReturnRate($clienteId),
            'total_purchased' => $this->calculateTotalPurchased($clienteId),
            'first_purchase' => $this->getFirstPurchaseDate($clienteId),
            'purchase_frequency' => $this->analyzePurchaseFrequency($clienteId),
            'total_purchases' => $this->getTotalPurchases($clienteId),
            'returned_purchases' => $this->getReturnedPurchases($clienteId),
            'average_purchase_value' => $this->getAveragePurchaseValue($clienteId),
            'payment_statistics' => $this->getPaymentStatistics($clienteId)
        ];
    }

    /**
     * Analisa o comportamento de pagamento (atrasado/no dia/adiantado)
     */
    public function calculatePaymentBehavior(int $clienteId): string
    {
        $parcelas = Parcela::where('id_cliente', $clienteId)
            ->whereNotNull('data_pagamento')
            ->whereNotIn('status', ['devolucao'])
            ->get();

        if ($parcelas->isEmpty()) {
            return 'sem_historico';
        }

        $totalParcelas = $parcelas->count();
        $atrasadas = 0;
        $noDia = 0;
        $adiantadas = 0;

        foreach ($parcelas as $parcela) {
            $diasDiferenca = $parcela->dias_atraso_ou_antecipacao;
            
            if ($diasDiferenca === null) continue;
            
            if ($diasDiferenca < 0) {
                $atrasadas++;
            } elseif ($diasDiferenca === 0) {
                $noDia++;
            } else {
                $adiantadas++;
            }
        }

        // Calcula percentuais
        $percentualAtrasado = ($atrasadas / $totalParcelas) * 100;
        $percentualNoDia = ($noDia / $totalParcelas) * 100;
        $percentualAdiantado = ($adiantadas / $totalParcelas) * 100;

        // Determina comportamento predominante
        if ($percentualAtrasado >= 50) {
            return 'atrasado';
        } elseif ($percentualAdiantado >= 30) {
            return 'adiantado';
        } else {
            return 'no_dia';
        }
    }

    /**
     * Calcula a taxa de devolução
     */
    public function calculateReturnRate(int $clienteId): float
    {
        $totalCompras = Ticket::where('id_cliente', $clienteId)->count();
        
        if ($totalCompras === 0) {
            return 0.0;
        }

        $comprasDevolvidas = Ticket::where('id_cliente', $clienteId)
            ->whereHas('parcelasRelacao', function ($query) {
                $query->where('status', 'devolucao');
            })
            ->count();

        return round(($comprasDevolvidas / $totalCompras) * 100, 2);
    }

    /**
     * Calcula o total comprado (excluindo devoluções)
     */
    public function calculateTotalPurchased(int $clienteId): float
    {
        return Ticket::where('id_cliente', $clienteId)
            ->whereDoesntHave('parcelasRelacao', function ($query) {
                $query->where('status', 'devolucao');
            })
            ->sum('valor');
    }

    /**
     * Encontra a data da primeira compra
     */
    public function getFirstPurchaseDate(int $clienteId): ?string
    {
        $firstTicket = Ticket::where('id_cliente', $clienteId)
            ->orderBy('data', 'asc')
            ->first();

        return $firstTicket ? $firstTicket->data->format('d/m/Y') : null;
    }

    /**
     * Analisa a frequência de compras (regular/irregular)
     */
    public function analyzePurchaseFrequency(int $clienteId): string
    {
        $tickets = Ticket::where('id_cliente', $clienteId)
            ->orderBy('data', 'asc')
            ->get();

        if ($tickets->count() < 2) {
            return 'insuficiente';
        }

        $intervalos = [];
        for ($i = 1; $i < $tickets->count(); $i++) {
            $intervalo = $tickets[$i]->data->diffInDays($tickets[$i-1]->data);
            $intervalos[] = $intervalo;
        }

        if (empty($intervalos)) {
            return 'insuficiente';
        }

        // Calcula média e desvio padrão dos intervalos
        $media = array_sum($intervalos) / count($intervalos);
        $variancia = array_sum(array_map(function($x) use ($media) {
            return pow($x - $media, 2);
        }, $intervalos)) / count($intervalos);
        $desvioPadrao = sqrt($variancia);

        // Se o desvio padrão é menor que 30% da média, considera regular
        $coeficienteVariacao = ($desvioPadrao / $media) * 100;

        if ($coeficienteVariacao <= 50) {
            return 'regular';
        } else {
            return 'irregular';
        }
    }

    /**
     * Retorna o número total de compras
     */
    public function getTotalPurchases(int $clienteId): int
    {
        return Ticket::where('id_cliente', $clienteId)->count();
    }

    /**
     * Retorna o número de compras devolvidas
     */
    public function getReturnedPurchases(int $clienteId): int
    {
        return Ticket::where('id_cliente', $clienteId)
            ->whereHas('parcelasRelacao', function ($query) {
                $query->where('status', 'devolucao');
            })
            ->count();
    }

    /**
     * Calcula o valor médio das compras
     */
    public function getAveragePurchaseValue(int $clienteId): float
    {
        $totalValue = $this->calculateTotalPurchased($clienteId);
        $totalPurchases = $this->getTotalPurchases($clienteId) - $this->getReturnedPurchases($clienteId);

        return $totalPurchases > 0 ? round($totalValue / $totalPurchases, 2) : 0.0;
    }

    /**
     * Retorna estatísticas detalhadas de pagamento
     */
    public function getPaymentStatistics(int $clienteId): array
    {
        $parcelas = Parcela::where('id_cliente', $clienteId)
            ->whereNotNull('data_pagamento')
            ->whereNotIn('status', ['devolucao'])
            ->get();

        if ($parcelas->isEmpty()) {
            return [
                'total_parcelas_pagas' => 0,
                'media_dias_atraso' => 0,
                'maior_atraso' => 0,
                'maior_antecipacao' => 0,
                'parcelas_em_atraso' => 0,
                'parcelas_no_dia' => 0,
                'parcelas_adiantadas' => 0
            ];
        }

        $diasAtraso = [];
        $atrasadas = 0;
        $noDia = 0;
        $adiantadas = 0;

        foreach ($parcelas as $parcela) {
            $dias = $parcela->dias_atraso_ou_antecipacao;
            if ($dias !== null) {
                $diasAtraso[] = $dias;
                
                if ($dias < 0) {
                    $atrasadas++;
                } elseif ($dias === 0) {
                    $noDia++;
                } else {
                    $adiantadas++;
                }
            }
        }

        return [
            'total_parcelas_pagas' => $parcelas->count(),
            'media_dias_atraso' => !empty($diasAtraso) ? round(array_sum($diasAtraso) / count($diasAtraso), 1) : 0,
            'maior_atraso' => !empty($diasAtraso) ? abs(min($diasAtraso)) : 0,
            'maior_antecipacao' => !empty($diasAtraso) ? max($diasAtraso) : 0,
            'parcelas_em_atraso' => $atrasadas,
            'parcelas_no_dia' => $noDia,
            'parcelas_adiantadas' => $adiantadas
        ];
    }

    /**
     * Retorna uma descrição textual do comportamento de pagamento
     */
    public function getPaymentBehaviorDescription(string $behavior): string
    {
        return match($behavior) {
            'atrasado' => 'Cliente costuma pagar com atraso',
            'no_dia' => 'Cliente costuma pagar no prazo',
            'adiantado' => 'Cliente costuma pagar antecipadamente',
            'sem_historico' => 'Sem histórico de pagamentos suficiente',
            default => 'Comportamento indefinido'
        };
    }

    /**
     * Retorna uma descrição textual da frequência de compras
     */
    public function getPurchaseFrequencyDescription(string $frequency): string
    {
        return match($frequency) {
            'regular' => 'Faz compras com frequência regular',
            'irregular' => 'Faz compras esporadicamente',
            'insuficiente' => 'Histórico insuficiente para análise',
            default => 'Padrão indefinido'
        };
    }

    /**
     * Determina se a taxa de devolução é alta
     */
    public function isHighReturnRate(float $returnRate): bool
    {
        return $returnRate > 15.0; // Considera alta se for maior que 15%
    }
}