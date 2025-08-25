<?php

namespace App\Services;

use App\Models\Parcela;
use App\Models\MultaConfiguracao;
use Carbon\Carbon;

class CalculoParcelaService
{
    /**
     * Calcula o valor da parcela com juros e multa
     */
    public function calcularValorComJurosMulta(Parcela $parcela): float
    {
        $multaConfig = MultaConfiguracao::first();
        $today = Carbon::today();
        $dataVencimento = Carbon::parse($parcela->data_vencimento);
        $diffDias = $today->diffInDays($dataVencimento, false);
        
        $diasAtraso = $diffDias < 0 ? abs($diffDias) : 0;
        $valorAPagar = $parcela->valor_parcela;
        
        if ($diasAtraso > $multaConfig->dias_carencia) {
            $taxaJurosDiaria = $multaConfig->taxa_juros / 30;
            $diasParaJuros = min($diasAtraso, $multaConfig->dias_cobranca);
            $valorJuros = ($parcela->valor_parcela * ($taxaJurosDiaria / 100)) * $diasParaJuros;
            $valorMulta = $parcela->valor_parcela * ($multaConfig->taxa_multa / 100);
            $valorAPagar += $valorMulta + $valorJuros;
        }
        
        return round($valorAPagar, 2);
    }

    /**
     * Calcula o valor com informações detalhadas
     */
    public function calcularValorDetalhado(Parcela $parcela): object
    {
        $multaConfig = MultaConfiguracao::first();
        $today = Carbon::today();
        $dataVencimento = Carbon::parse($parcela->data_vencimento);
        $diffDias = $today->diffInDays($dataVencimento, false);
        
        $diasAtraso = $diffDias < 0 ? abs($diffDias) : 0;
        $valorOriginal = $parcela->valor_parcela;
        $valorJuros = 0;
        $valorMulta = 0;
        
        if ($diasAtraso > $multaConfig->dias_carencia) {
            $taxaJurosDiaria = $multaConfig->taxa_juros / 30;
            $diasParaJuros = min($diasAtraso, $multaConfig->dias_cobranca);
            $valorJuros = ($parcela->valor_parcela * ($taxaJurosDiaria / 100)) * $diasParaJuros;
            $valorMulta = $parcela->valor_parcela * ($multaConfig->taxa_multa / 100);
        }
        
        $valorTotal = $valorOriginal + $valorMulta + $valorJuros;
        
        return (object) [
            'valor_original' => round($valorOriginal, 2),
            'valor_juros' => round($valorJuros, 2),
            'valor_multa' => round($valorMulta, 2),
            'valor_total' => round($valorTotal, 2),
            'dias_atraso' => $diasAtraso
        ];
    }
}