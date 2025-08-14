<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Ticket;
use App\Models\Parcela;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_belongs_to_cliente()
    {
        $cliente = Cliente::factory()->create();
        $ticket = Ticket::factory()->create(['id_cliente' => $cliente->id]);

        $this->assertInstanceOf(Cliente::class, $ticket->cliente);
        $this->assertEquals($cliente->id, $ticket->cliente->id);
    }

    public function test_ticket_has_many_parcelas()
    {
        $ticket = Ticket::factory()->create();
        $parcela = Parcela::factory()->create(['ticket' => $ticket->ticket]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $ticket->parcelas);
        $this->assertEquals(1, $ticket->parcelas->count());
        $this->assertEquals($parcela->id_parcelas, $ticket->parcelas->first()->id_parcelas);
    }

    public function test_is_devolvida_returns_true_when_has_devolucao_parcela()
    {
        $ticket = Ticket::factory()->create();
        Parcela::factory()->create([
            'ticket' => $ticket->ticket,
            'status' => 'devolucao'
        ]);

        $this->assertTrue($ticket->isDevolvida());
    }

    public function test_is_devolvida_returns_false_when_no_devolucao_parcela()
    {
        $ticket = Ticket::factory()->create();
        Parcela::factory()->create([
            'ticket' => $ticket->ticket,
            'status' => 'pago'
        ]);

        $this->assertFalse($ticket->isDevolvida());
    }

    public function test_valor_financiado_attribute()
    {
        $ticket = Ticket::factory()->create([
            'valor' => 1000.00,
            'entrada' => 200.00
        ]);

        $this->assertEquals(800.00, $ticket->valor_financiado);
    }

    public function test_valor_formatado_attribute()
    {
        $ticket = Ticket::factory()->create(['valor' => 1234.56]);

        $this->assertEquals('R$ 1.234,56', $ticket->valor_formatado);
    }

    public function test_entrada_formatada_attribute()
    {
        $ticket = Ticket::factory()->create(['entrada' => 123.45]);

        $this->assertEquals('R$ 123,45', $ticket->entrada_formatada);
    }

    public function test_data_formatada_attribute()
    {
        $ticket = Ticket::factory()->create([
            'data' => '2024-01-15 14:30:00'
        ]);

        $this->assertEquals('15/01/2024 14:30', $ticket->data_formatada);
    }
}