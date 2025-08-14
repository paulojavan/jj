<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PaymentProfileService;
use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\Parcela;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentProfileService();
    }

    public function test_calculate_payment_behavior_atrasado()
    {
        $cliente = Cliente::factory()->create();
        $ticket = Ticket::factory()->create(['id_cliente' => $cliente->id]);

        // Criar parcelas com pagamentos atrasados (maioria)
        Parcela::factory()->create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket->ticket,
            'data_vencimento' => Carbon::parse('2024-01-10'),
            'data_pagamento' => Carbon::parse('2024-01-15'), // 5 dias de atraso
            'status' => 'pago'
        ]);

        Parcela::factory()->create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket->ticket,
            'data_vencimento' => Carbon::parse('2024-02-10'),
            'data_pagamento' => Carbon::parse('2024-02-12'), // 2 dias de atraso
            'status' => 'pago'
        ]);

        $behavior = $this->service->calculatePaymentBehavior($cliente->id);
        $this->assertEquals('atrasado', $behavior);
    }

    public function test_calculate_payment_behavior_no_dia()
    {
        $cliente = Cliente::factory()->create();
        $ticket = Ticket::factory()->create(['id_cliente' => $cliente->id]);

        // Criar parcelas pagas no dia
        Parcela::factory()->create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket->ticket,
            'data_vencimento' => Carbon::parse('2024-01-10'),
            'data_pagamento' => Carbon::parse('2024-01-10'), // No dia
            'status' => 'pago'
        ]);

        Parcela::factory()->create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket->ticket,
            'data_vencimento' => Carbon::parse('2024-02-10'),
            'data_pagamento' => Carbon::parse('2024-02-10'), // No dia
            'status' => 'pago'
        ]);

        $behavior = $this->service->calculatePaymentBehavior($cliente->id);
        $this->assertEquals('no_dia', $behavior);
    }

    public function test_calculate_payment_behavior_adiantado()
    {
        $cliente = Cliente::factory()->create();
        $ticket = Ticket::factory()->create(['id_cliente' => $cliente->id]);

        // Criar parcelas pagas antecipadamente
        for ($i = 0; $i < 5; $i++) {
            Parcela::factory()->create([
                'id_cliente' => $cliente->id,
                'ticket' => $ticket->ticket,
                'data_vencimento' => Carbon::parse('2024-01-' . (10 + $i)),
                'data_pagamento' => Carbon::parse('2024-01-' . (5 + $i)), // 5 dias antecipado
                'status' => 'pago'
            ]);
        }

        $behavior = $this->service->calculatePaymentBehavior($cliente->id);
        $this->assertEquals('adiantado', $behavior);
    }

    public function test_calculate_return_rate()
    {
        $cliente = Cliente::factory()->create();

        // Criar 10 tickets
        for ($i = 0; $i < 10; $i++) {
            $ticket = Ticket::factory()->create(['id_cliente' => $cliente->id]);
            
            // 2 tickets com devolução
            if ($i < 2) {
                Parcela::factory()->create([
                    'id_cliente' => $cliente->id,
                    'ticket' => $ticket->ticket,
                    'status' => 'devolucao'
                ]);
            } else {
                Parcela::factory()->create([
                    'id_cliente' => $cliente->id,
                    'ticket' => $ticket->ticket,
                    'status' => 'pago'
                ]);
            }
        }

        $returnRate = $this->service->calculateReturnRate($cliente->id);
        $this->assertEquals(20.0, $returnRate); // 2/10 = 20%
    }

    public function test_calculate_total_purchased_excluding_returns()
    {
        $cliente = Cliente::factory()->create();

        // Ticket normal - deve ser incluído
        $ticket1 = Ticket::factory()->create([
            'id_cliente' => $cliente->id,
            'valor' => 1000.00
        ]);
        Parcela::factory()->create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket1->ticket,
            'status' => 'pago'
        ]);

        // Ticket com devolução - deve ser excluído
        $ticket2 = Ticket::factory()->create([
            'id_cliente' => $cliente->id,
            'valor' => 500.00
        ]);
        Parcela::factory()->create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket2->ticket,
            'status' => 'devolucao'
        ]);

        $total = $this->service->calculateTotalPurchased($cliente->id);
        $this->assertEquals(1000.00, $total);
    }

    public function test_get_first_purchase_date()
    {
        $cliente = Cliente::factory()->create();

        Ticket::factory()->create([
            'id_cliente' => $cliente->id,
            'data' => Carbon::parse('2024-02-15 10:00:00')
        ]);

        Ticket::factory()->create([
            'id_cliente' => $cliente->id,
            'data' => Carbon::parse('2024-01-10 14:30:00') // Primeira compra
        ]);

        $firstPurchase = $this->service->getFirstPurchaseDate($cliente->id);
        $this->assertEquals('10/01/2024', $firstPurchase);
    }

    public function test_analyze_purchase_frequency_regular()
    {
        $cliente = Cliente::factory()->create();

        // Criar compras com intervalos regulares (aproximadamente 30 dias)
        $dates = [
            '2024-01-01',
            '2024-02-01',
            '2024-03-01',
            '2024-04-01',
            '2024-05-01'
        ];

        foreach ($dates as $date) {
            Ticket::factory()->create([
                'id_cliente' => $cliente->id,
                'data' => Carbon::parse($date)
            ]);
        }

        $frequency = $this->service->analyzePurchaseFrequency($cliente->id);
        $this->assertEquals('regular', $frequency);
    }

    public function test_analyze_purchase_frequency_irregular()
    {
        $cliente = Cliente::factory()->create();

        // Criar compras com intervalos irregulares
        $dates = [
            '2024-01-01',
            '2024-01-05', // 4 dias
            '2024-03-15', // 69 dias
            '2024-03-20', // 5 dias
            '2024-08-01'  // 134 dias
        ];

        foreach ($dates as $date) {
            Ticket::factory()->create([
                'id_cliente' => $cliente->id,
                'data' => Carbon::parse($date)
            ]);
        }

        $frequency = $this->service->analyzePurchaseFrequency($cliente->id);
        $this->assertEquals('irregular', $frequency);
    }

    public function test_get_payment_statistics()
    {
        $cliente = Cliente::factory()->create();
        $ticket = Ticket::factory()->create(['id_cliente' => $cliente->id]);

        // Parcela paga com atraso
        Parcela::factory()->create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket->ticket,
            'data_vencimento' => Carbon::parse('2024-01-10'),
            'data_pagamento' => Carbon::parse('2024-01-15'), // 5 dias de atraso
            'status' => 'pago'
        ]);

        // Parcela paga no dia
        Parcela::factory()->create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket->ticket,
            'data_vencimento' => Carbon::parse('2024-02-10'),
            'data_pagamento' => Carbon::parse('2024-02-10'), // No dia
            'status' => 'pago'
        ]);

        // Parcela paga antecipada
        Parcela::factory()->create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket->ticket,
            'data_vencimento' => Carbon::parse('2024-03-10'),
            'data_pagamento' => Carbon::parse('2024-03-07'), // 3 dias antecipado
            'status' => 'pago'
        ]);

        $stats = $this->service->getPaymentStatistics($cliente->id);

        $this->assertEquals(3, $stats['total_parcelas_pagas']);
        $this->assertEquals(1, $stats['parcelas_em_atraso']);
        $this->assertEquals(1, $stats['parcelas_no_dia']);
        $this->assertEquals(1, $stats['parcelas_adiantadas']);
        $this->assertEquals(5, $stats['maior_atraso']);
        $this->assertEquals(3, $stats['maior_antecipacao']);
    }

    public function test_is_high_return_rate()
    {
        $this->assertTrue($this->service->isHighReturnRate(20.0));
        $this->assertFalse($this->service->isHighReturnRate(10.0));
        $this->assertFalse($this->service->isHighReturnRate(15.0));
        $this->assertTrue($this->service->isHighReturnRate(15.1));
    }

    public function test_get_payment_behavior_description()
    {
        $this->assertEquals('Cliente costuma pagar com atraso', 
            $this->service->getPaymentBehaviorDescription('atrasado'));
        $this->assertEquals('Cliente costuma pagar no prazo', 
            $this->service->getPaymentBehaviorDescription('no_dia'));
        $this->assertEquals('Cliente costuma pagar antecipadamente', 
            $this->service->getPaymentBehaviorDescription('adiantado'));
    }

    public function test_get_purchase_frequency_description()
    {
        $this->assertEquals('Faz compras com frequência regular', 
            $this->service->getPurchaseFrequencyDescription('regular'));
        $this->assertEquals('Faz compras esporadicamente', 
            $this->service->getPurchaseFrequencyDescription('irregular'));
    }

    public function test_calculate_profile_complete()
    {
        $cliente = Cliente::factory()->create();
        $ticket = Ticket::factory()->create([
            'id_cliente' => $cliente->id,
            'valor' => 1000.00
        ]);

        Parcela::factory()->create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket->ticket,
            'data_vencimento' => Carbon::parse('2024-01-10'),
            'data_pagamento' => Carbon::parse('2024-01-10'),
            'status' => 'pago'
        ]);

        $profile = $this->service->calculateProfile($cliente->id);

        $this->assertArrayHasKey('payment_behavior', $profile);
        $this->assertArrayHasKey('return_rate', $profile);
        $this->assertArrayHasKey('total_purchased', $profile);
        $this->assertArrayHasKey('first_purchase', $profile);
        $this->assertArrayHasKey('purchase_frequency', $profile);
        $this->assertArrayHasKey('payment_statistics', $profile);
    }
}