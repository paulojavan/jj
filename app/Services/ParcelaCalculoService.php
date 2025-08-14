<?php

namespace App\Services;

use App\Models\Parcela;
use App\Models\MultaConfiguracao;
use Carbon\Carbon;

class ParcelaCalculoService
{
    /**
     * Calcula o valor total a pagar para uma parcela
     */
    public function calcularValorAPagar(Parcela $parcela, MultaConfiguracao $config): float
    {
        $valorParcela = $parcela->valor_parcela;
        $diasAtraso = $this->calcularDiasAtraso($parcela->data_vencimento);
        
        // Se não há atraso ou está dentro do período de carência, retorna apenas o valor da parcela
        if ($diasAtraso <= $config->dias_carencia) {
            return $valorParcela;
        }
        
        $multa = $this->calcularMulta($valorParcela, $config->taxa_multa);
        $juros = $this->calcularJuros(
            $valorParcela, 
            $config->taxa_juros, 
            $diasAtraso, 
            $config->dias_carencia, 
            $config->dias_cobranca
        );
        
        return $valorParcela + $multa + $juros;
    }
    
    /**
     * Calcula os dias de atraso baseado na data de vencimento
     */
    public function calcularDiasAtraso($dataVencimento): int
    {
        $dataVencimento = Carbon::parse($dataVencimento);
        $hoje = Carbon::now();
        
        if ($hoje->lte($dataVencimento)) {
            return 0;
        }
        
        return $dataVencimento->diffInDays($hoje);
    }
    
    /**
     * Calcula a multa baseada na taxa percentual
     */
    public function calcularMulta(float $valorParcela, float $taxaMulta): float
    {
        return $valorParcela * ($taxaMulta / 100);
    }
    
    /**
     * Calcula os juros baseado na taxa mensal, convertida para diária
     */
    public function calcularJuros(
        float $valorParcela, 
        float $taxaJuros, 
        int $diasAtraso, 
        int $diasCarencia, 
        int $diasCobranca
    ): float {
        // Se está dentro do período de carência, não cobra juros
        if ($diasAtraso <= $diasCarencia) {
            return 0;
        }
        
        // Calcula os dias efetivos de cobrança (descontando a carência)
        $diasEfetivos = $diasAtraso - $diasCarencia;
        
        // Limita aos dias máximos de cobrança
        $diasEfetivos = min($diasEfetivos, $diasCobranca);
        
        // Converte taxa mensal para diária (divide por 30)
        $taxaDiaria = $taxaJuros / 30 / 100;
        
        return $valorParcela * $taxaDiaria * $diasEfetivos;
    }
    
    /**
     * Formata valor monetário para exibição
     */
    public function formatarValor(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
}