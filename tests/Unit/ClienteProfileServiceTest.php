<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ClienteProfileService;
use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\Parcela;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;

class ClienteProfileServiceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected $clienteProfileService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clienteProfileService = new ClienteProfileService();
    }

    /** @test */
    public function it_returns_empty_profile_when_no_tickets_exist()
    {
        // Criar um cliente sem tickets
        $cliente = cliente::factory()->create();

        $perfil = $this->clienteProfileService->gerarPerfilCompras($cliente->id);

        $this->assertEquals(0, $perfil['total_compras']);
        $this->assertEquals(0, $perfil['valor_total_gasto']);
        $this->assertEquals(0, $perfil['ticket_medio']);
    }

    /** @test */
    public function it_returns_empty_payment_profile_when_no_parcelas_exist()
    {
        // Criar um cliente sem parcelas
        $cliente = cliente::factory()->create();

        $perfil = $this->clienteProfileService->gerarPerfilPagamentos($cliente->id);

        $this->assertEquals(0, $perfil['pontualidade']['percentual_pontual']);
        $this->assertEquals(0, $perfil['inadimplencia']['parcelas_em_atraso']);
        $this->assertEquals(0, $perfil['risco_calculado']['score']);
    }

    /** @test */
    public function it_calculates_risk_score_correctly()
    {
        $pontualidade = [
            'percentual_pontual' => 80.0,
            'atraso_medio_dias' => 5.0,
            'maior_atraso_dias' => 10
        ];

        $inadimplencia = [
            'parcelas_em_atraso' => 1,
            'valor_em_atraso' => 100.0,
            'percentual_inadimplencia' => 10.0
        ];

        $comportamentoPagamento = [
            'metodos_preferidos' => ['pix' => 5],
            'valor_medio_parcela' => 150.0,
            'prazo_medio_pagamento' => 2.0
        ];

        $risco = $this->clienteProfileService->calcularIndicadoresRisco(
            $pontualidade,
            $inadimplencia,
            $comportamentoPagamento
        );

        $this->assertIsArray($risco);
        $this->assertArrayHasKey('score', $risco);
        $this->assertArrayHasKey('classificacao', $risco);
        $this->assertArrayHasKey('recomendacao_limite', $risco);
        
        // Score deve estar entre 0 e 100
        $this->assertGreaterThanOrEqual(0, $risco['score']);
        $this->assertLessThanOrEqual(100, $risco['score']);
        
        // Classificação deve ser uma das opções válidas
        $this->assertContains($risco['classificacao'], ['baixo', 'medio', 'alto']);
    }
}