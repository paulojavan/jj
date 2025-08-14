<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ParcelaCalculoService;
use App\Models\Parcela;
use App\Models\MultaConfiguracao;
use Carbon\Carbon;

class ParcelaCalculoServiceTest extends TestCase
{
    private ParcelaCalculoService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ParcelaCalculoService();
    }
    
    public function test_calcula_dias_atraso_sem_atraso()
    {
        $dataVencimento = Carbon::now()->addDays(5);
        $diasAtraso = $this->service->calcularDiasAtraso($dataVencimento);
        
        $this->assertEquals(0, $diasAtraso);
    }
    
    public function test_calcula_dias_atraso_com_atraso()
    {
        $dataVencimento = Carbon::now()->subDays(10);
        $diasAtraso = $this->service->calcularDiasAtraso($dataVencimento);
        
        $this->assertEquals(10, $diasAtraso);
    }
    
    public function test_calcula_multa_corretamente()
    {
        $valorParcela = 100.00;
        $taxaMulta = 2.0; // 2%
        
        $multa = $this->service->calcularMulta($valorParcela, $taxaMulta);
        
        $this->assertEquals(2.00, $multa);
    }
    
    public function test_calcula_juros_dentro_da_carencia()
    {
        $valorParcela = 100.00;
        $taxaJuros = 3.0; // 3% ao mês
        $diasAtraso = 5;
        $diasCarencia = 10;
        $diasCobranca = 30;
        
        $juros = $this->service->calcularJuros(
            $valorParcela, 
            $taxaJuros, 
            $diasAtraso, 
            $diasCarencia, 
            $diasCobranca
        );
        
        $this->assertEquals(0, $juros);
    }
    
    public function test_calcula_juros_fora_da_carencia()
    {
        $valorParcela = 100.00;
        $taxaJuros = 3.0; // 3% ao mês = 0.1% ao dia
        $diasAtraso = 15;
        $diasCarencia = 5;
        $diasCobranca = 30;
        
        // Dias efetivos: 15 - 5 = 10 dias
        // Taxa diária: 3% / 30 = 0.1%
        // Juros: 100 * 0.001 * 10 = 1.00
        $juros = $this->service->calcularJuros(
            $valorParcela, 
            $taxaJuros, 
            $diasAtraso, 
            $diasCarencia, 
            $diasCobranca
        );
        
        $this->assertEquals(1.00, $juros);
    }
    
    public function test_limita_juros_aos_dias_maximos_de_cobranca()
    {
        $valorParcela = 100.00;
        $taxaJuros = 3.0; // 3% ao mês
        $diasAtraso = 50;
        $diasCarencia = 5;
        $diasCobranca = 30;
        
        // Dias efetivos limitados: min(50-5, 30) = 30 dias
        // Taxa diária: 3% / 30 = 0.1%
        // Juros: 100 * 0.001 * 30 = 3.00
        $juros = $this->service->calcularJuros(
            $valorParcela, 
            $taxaJuros, 
            $diasAtraso, 
            $diasCarencia, 
            $diasCobranca
        );
        
        $this->assertEquals(3.00, $juros);
    }
    
    public function test_calcula_valor_a_pagar_sem_atraso()
    {
        $parcela = new Parcela([
            'valor_parcela' => 100.00,
            'data_vencimento' => Carbon::now()->addDays(5)
        ]);
        
        $config = new MultaConfiguracao([
            'taxa_multa' => 2.0,
            'taxa_juros' => 3.0,
            'dias_carencia' => 5,
            'dias_cobranca' => 30
        ]);
        
        $valorAPagar = $this->service->calcularValorAPagar($parcela, $config);
        
        $this->assertEquals(100.00, $valorAPagar);
    }
    
    public function test_calcula_valor_a_pagar_com_multa_e_juros()
    {
        $parcela = new Parcela([
            'valor_parcela' => 100.00,
            'data_vencimento' => Carbon::now()->subDays(15)
        ]);
        
        $config = new MultaConfiguracao([
            'taxa_multa' => 2.0, // 2%
            'taxa_juros' => 3.0, // 3% ao mês
            'dias_carencia' => 5,
            'dias_cobranca' => 30
        ]);
        
        // Valor esperado:
        // Parcela: 100.00
        // Multa: 100 * 2% = 2.00
        // Juros: 100 * (3%/30) * (15-5) = 100 * 0.001 * 10 = 1.00
        // Total: 103.00
        $valorAPagar = $this->service->calcularValorAPagar($parcela, $config);
        
        $this->assertEquals(103.00, $valorAPagar);
    }
    
    public function test_formata_valor_corretamente()
    {
        $valor = 1234.56;
        $valorFormatado = $this->service->formatarValor($valor);
        
        $this->assertEquals('R$ 1.234,56', $valorFormatado);
    }
    
    public function test_calcula_valor_zero_sem_erro()
    {
        $parcela = new Parcela([
            'valor_parcela' => 0.00,
            'data_vencimento' => Carbon::now()->subDays(10)
        ]);
        
        $config = new MultaConfiguracao([
            'taxa_multa' => 2.0,
            'taxa_juros' => 3.0,
            'dias_carencia' => 5,
            'dias_cobranca' => 30
        ]);
        
        $valorAPagar = $this->service->calcularValorAPagar($parcela, $config);
        
        $this->assertEquals(0.00, $valorAPagar);
    }
}