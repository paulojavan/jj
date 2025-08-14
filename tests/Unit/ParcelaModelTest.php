<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Parcela;
use App\Models\Ticket;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParcelaModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_parcela_belongs_to_ticket()
    {
        $ticket = Ticket::factory()->create();
        $parcela = Parcela::factory()->create(['ticket' => $ticket->ticket]);

        $this->assertInstanceOf(Ticket::class, $parcela->ticket);
        $this->assertEquals($ticket->ticket, $parcela->ticket->ticket);
    }

    public function test_parcela_belongs_to_cliente()
    {
        $cliente = Cliente::factory()->create();
        $parcela = Parcela::factory()->create(['id_cliente' => $cliente->id]);

        $this->assertInstanceOf(Cliente::class, $parcela->cliente);
        $this->assertEquals($cliente->id, $parcela->cliente->id);
    }

    public function test_is_vencida_returns_true_for_overdue_unpaid_parcela()
    {
        $parcela = Parcela::factory()->create([
            'data_vencimento' => Carbon::yesterday(),
            'data_pagamento' => null,
            'status' => 'em_aberto'
        ]);

        $this->assertTrue($parcela->isVencida());
    }

    public function test_is_vencida_returns_false_for_paid_parcela()
    {
        $parcela = Parcela::factory()->create([
            'data_vencimento' => Carbon::yesterday(),
            'data_pagamento' => Carbon::today(),
            'status' => 'pago'
        ]);

        $this->assertFalse($parcela->isVencida());
    }

    public function test_is_vencida_returns_false_for_devolucao()
    {
        $parcela = Parcela::factory()->create([
            'data_vencimento' => Carbon::yesterday(),
            'data_pagamento' => null,
            'status' => 'devolucao'
        ]);

        $this->assertFalse($parcela->isVencida());
    }

    public function test_is_paga_returns_true_for_paid_parcela()
    {
        $parcela = Parcela::factory()->create([
            'data_pagamento' => Carbon::today(),
            'status' => 'pago'
        ]);

        $this->assertTrue($parcela->isPaga());
    }

    public function test_is_paga_returns_false_for_unpaid_parcela()
    {
        $parcela = Parcela::factory()->create([
            'data_pagamento' => null,
            'status' => 'em_aberto'
        ]);

        $this->assertFalse($parcela->isPaga());
    }

    public function test_is_devolucao_returns_true_for_devolucao_status()
    {
        $parcela = Parcela::factory()->create(['status' => 'devolucao']);

        $this->assertTrue($parcela->isDevolucao());
    }

    public function test_status_color_attribute_returns_correct_colors()
    {
        // Devolução - Amarelo
        $parcela = Parcela::factory()->create(['status' => 'devolucao']);
        $this->assertEquals('text-yellow-600 bg-yellow-50', $parcela->status_color);

        // Pago - Verde
        $parcela = Parcela::factory()->create([
            'status' => 'pago',
            'data_pagamento' => Carbon::today()
        ]);
        $this->assertEquals('text-green-600 bg-green-50', $parcela->status_color);

        // Vencido - Vermelho
        $parcela = Parcela::factory()->create([
            'status' => 'em_aberto',
            'data_vencimento' => Carbon::yesterday(),
            'data_pagamento' => null
        ]);
        $this->assertEquals('text-red-600 bg-red-50', $parcela->status_color);

        // Em aberto - Cinza
        $parcela = Parcela::factory()->create([
            'status' => 'em_aberto',
            'data_vencimento' => Carbon::tomorrow(),
            'data_pagamento' => null
        ]);
        $this->assertEquals('text-gray-600 bg-gray-50', $parcela->status_color);
    }

    public function test_status_texto_attribute_returns_correct_text()
    {
        // Devolução
        $parcela = Parcela::factory()->create(['status' => 'devolucao']);
        $this->assertEquals('Devolução', $parcela->status_texto);

        // Pago
        $parcela = Parcela::factory()->create([
            'status' => 'pago',
            'data_pagamento' => Carbon::today()
        ]);
        $this->assertEquals('Pago', $parcela->status_texto);

        // Vencido
        $parcela = Parcela::factory()->create([
            'status' => 'em_aberto',
            'data_vencimento' => Carbon::yesterday(),
            'data_pagamento' => null
        ]);
        $this->assertEquals('Vencido', $parcela->status_texto);

        // Em aberto
        $parcela = Parcela::factory()->create([
            'status' => 'em_aberto',
            'data_vencimento' => Carbon::tomorrow(),
            'data_pagamento' => null
        ]);
        $this->assertEquals('Em aberto', $parcela->status_texto);
    }

    public function test_valor_formatado_attribute()
    {
        $parcela = Parcela::factory()->create(['valor_parcela' => 123.45]);

        $this->assertEquals('R$ 123,45', $parcela->valor_formatado);
    }

    public function test_vencimento_formatado_attribute()
    {
        $parcela = Parcela::factory()->create([
            'data_vencimento' => '2024-01-15'
        ]);

        $this->assertEquals('15/01/2024', $parcela->vencimento_formatado);
    }

    public function test_pagamento_formatado_attribute()
    {
        // Com data de pagamento
        $parcela = Parcela::factory()->create([
            'data_pagamento' => '2024-01-15'
        ]);
        $this->assertEquals('15/01/2024', $parcela->pagamento_formatado);

        // Sem data de pagamento
        $parcela = Parcela::factory()->create(['data_pagamento' => null]);
        $this->assertEquals('-', $parcela->pagamento_formatado);
    }

    public function test_dias_atraso_ou_antecipacao_attribute()
    {
        // Pagamento em atraso (3 dias)
        $parcela = Parcela::factory()->create([
            'data_vencimento' => Carbon::parse('2024-01-10'),
            'data_pagamento' => Carbon::parse('2024-01-13'),
            'status' => 'pago'
        ]);
        $this->assertEquals(-3, $parcela->dias_atraso_ou_antecipacao);

        // Pagamento antecipado (2 dias)
        $parcela = Parcela::factory()->create([
            'data_vencimento' => Carbon::parse('2024-01-15'),
            'data_pagamento' => Carbon::parse('2024-01-13'),
            'status' => 'pago'
        ]);
        $this->assertEquals(2, $parcela->dias_atraso_ou_antecipacao);

        // Não pago
        $parcela = Parcela::factory()->create([
            'data_pagamento' => null,
            'status' => 'em_aberto'
        ]);
        $this->assertNull($parcela->dias_atraso_ou_antecipacao);
    }
}