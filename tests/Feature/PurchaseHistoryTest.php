<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\Parcela;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->cliente = Cliente::factory()->create();
    }

    public function test_client_search_page_shows_history_button()
    {
        $response = $this->actingAs($this->user)
            ->get(route('clientes.index', ['cliente' => $this->cliente->nome]));

        $response->assertStatus(200);
        $response->assertSee('Histórico');
        $response->assertSee(route('clientes.historico', $this->cliente->id));
    }

    public function test_purchase_history_page_loads_successfully()
    {
        $ticket = Ticket::factory()->create(['id_cliente' => $this->cliente->id]);
        Parcela::factory()->create(['id_cliente' => $this->cliente->id, 'ticket' => $ticket->ticket]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico', $this->cliente->id));

        $response->assertStatus(200);
        $response->assertSee('Histórico de Compras');
        $response->assertSee($this->cliente->nome);
        $response->assertSee($ticket->ticket);
    }

    public function test_purchase_history_shows_purchases_ordered_by_date_desc()
    {
        $ticket1 = Ticket::factory()->create([
            'id_cliente' => $this->cliente->id,
            'data' => Carbon::parse('2024-01-01'),
            'ticket' => 'TICKET001'
        ]);

        $ticket2 = Ticket::factory()->create([
            'id_cliente' => $this->cliente->id,
            'data' => Carbon::parse('2024-02-01'),
            'ticket' => 'TICKET002'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico', $this->cliente->id));

        $response->assertStatus(200);
        
        // Verifica se TICKET002 (mais recente) aparece antes de TICKET001
        $content = $response->getContent();
        $pos1 = strpos($content, 'TICKET002');
        $pos2 = strpos($content, 'TICKET001');
        
        $this->assertLessThan($pos2, $pos1);
    }

    public function test_purchase_history_pagination_works()
    {
        // Criar 25 tickets para testar paginação (20 por página)
        for ($i = 1; $i <= 25; $i++) {
            Ticket::factory()->create([
                'id_cliente' => $this->cliente->id,
                'ticket' => 'TICKET' . str_pad($i, 3, '0', STR_PAD_LEFT)
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico', $this->cliente->id));

        $response->assertStatus(200);
        
        // Deve mostrar apenas 20 tickets na primeira página
        $response->assertSee('TICKET025'); // Mais recente
        $response->assertDontSee('TICKET005'); // Deve estar na segunda página
        
        // Deve ter links de paginação
        $response->assertSee('Próxima');
    }

    public function test_purchase_details_page_loads_successfully()
    {
        $ticket = Ticket::factory()->create(['id_cliente' => $this->cliente->id]);
        $parcela = Parcela::factory()->create([
            'id_cliente' => $this->cliente->id,
            'ticket' => $ticket->ticket
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.compra', [$this->cliente->id, $ticket->ticket]));

        $response->assertStatus(200);
        $response->assertSee('Detalhes da Compra');
        $response->assertSee($ticket->ticket);
        $response->assertSee('Parcela ' . $parcela->numero);
    }

    public function test_purchase_details_shows_action_buttons()
    {
        $ticket = Ticket::factory()->create(['id_cliente' => $this->cliente->id]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.compra', [$this->cliente->id, $ticket->ticket]));

        $response->assertStatus(200);
        $response->assertSee('Gerar Duplicata');
        $response->assertSee('Carnê de Pagamento');
        $response->assertSee('Mensagem de Aviso');
    }

    public function test_installment_status_colors_display_correctly()
    {
        $ticket = Ticket::factory()->create(['id_cliente' => $this->cliente->id]);

        // Parcela paga - deve ser verde
        $parcelaPaga = Parcela::factory()->paid()->create([
            'id_cliente' => $this->cliente->id,
            'ticket' => $ticket->ticket,
            'numero' => '1'
        ]);

        // Parcela vencida - deve ser vermelha
        $parcelaVencida = Parcela::factory()->overdue()->create([
            'id_cliente' => $this->cliente->id,
            'ticket' => $ticket->ticket,
            'numero' => '2'
        ]);

        // Parcela devolução - deve ser amarela
        $parcelaDevolucao = Parcela::factory()->returned()->create([
            'id_cliente' => $this->cliente->id,
            'ticket' => $ticket->ticket,
            'numero' => '3'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico', $this->cliente->id));

        $response->assertStatus(200);
        $response->assertSee('text-green-600'); // Pago
        $response->assertSee('text-red-600');   // Vencido
        $response->assertSee('text-yellow-600'); // Devolução
    }

    public function test_payment_profile_displays_correctly()
    {
        $ticket = Ticket::factory()->create([
            'id_cliente' => $this->cliente->id,
            'valor' => 1000.00
        ]);

        Parcela::factory()->paid()->create([
            'id_cliente' => $this->cliente->id,
            'ticket' => $ticket->ticket,
            'data_vencimento' => Carbon::parse('2024-01-10'),
            'data_pagamento' => Carbon::parse('2024-01-10'), // Pago no dia
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico', $this->cliente->id));

        $response->assertStatus(200);
        $response->assertSee('Perfil de Pagamento');
        $response->assertSee('Comportamento de Pagamento');
        $response->assertSee('Taxa de Devolução');
        $response->assertSee('Total Comprado');
        $response->assertSee('R$ 1.000,00');
    }

    public function test_duplicata_page_loads_successfully()
    {
        $ticket = Ticket::factory()->create(['id_cliente' => $this->cliente->id]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.duplicata', [$this->cliente->id, $ticket->ticket]));

        $response->assertStatus(200);
        $response->assertSee('DUPLICATA DE COMPRA');
        $response->assertSee($this->cliente->nome);
        $response->assertSee($ticket->ticket);
    }

    public function test_carne_page_loads_successfully()
    {
        $ticket = Ticket::factory()->create(['id_cliente' => $this->cliente->id]);
        Parcela::factory()->create([
            'id_cliente' => $this->cliente->id,
            'ticket' => $ticket->ticket
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.carne', [$this->cliente->id, $ticket->ticket]));

        $response->assertStatus(200);
        $response->assertSee('CARNÊ DE PAGAMENTO');
        $response->assertSee($this->cliente->nome);
        $response->assertSee($ticket->ticket);
    }

    public function test_message_sending_works()
    {
        $ticket = Ticket::factory()->create(['id_cliente' => $this->cliente->id]);

        $response = $this->actingAs($this->user)
            ->post(route('clientes.mensagem', [$this->cliente->id, $ticket->ticket]), [
                'mensagem' => 'Lembrete de pagamento',
                'tipo' => 'sms'
            ]);

        $response->assertRedirect(route('clientes.compra', [$this->cliente->id, $ticket->ticket]));
        $response->assertSessionHas('success', 'Mensagem enviada com sucesso!');
    }

    public function test_message_validation_works()
    {
        $ticket = Ticket::factory()->create(['id_cliente' => $this->cliente->id]);

        $response = $this->actingAs($this->user)
            ->post(route('clientes.mensagem', [$this->cliente->id, $ticket->ticket]), [
                'mensagem' => '', // Mensagem vazia
                'tipo' => 'invalid' // Tipo inválido
            ]);

        $response->assertSessionHasErrors(['mensagem', 'tipo']);
    }

    public function test_nonexistent_client_returns_404()
    {
        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico', 999999));

        $response->assertStatus(404);
    }

    public function test_nonexistent_ticket_redirects_with_error()
    {
        $response = $this->actingAs($this->user)
            ->get(route('clientes.compra', [$this->cliente->id, 'NONEXISTENT']));

        $response->assertRedirect(route('clientes.historico', $this->cliente->id));
        $response->assertSessionHas('error');
    }

    public function test_client_with_no_purchases_shows_empty_state()
    {
        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico', $this->cliente->id));

        $response->assertStatus(200);
        $response->assertSee('Nenhuma compra encontrada');
        $response->assertSee('Este cliente ainda não realizou nenhuma compra');
    }

    public function test_high_return_rate_warning_displays()
    {
        // Criar 10 tickets, 3 com devolução (30% de taxa de devolução)
        for ($i = 1; $i <= 10; $i++) {
            $ticket = Ticket::factory()->create(['id_cliente' => $this->cliente->id]);
            
            if ($i <= 3) {
                Parcela::factory()->returned()->create([
                    'id_cliente' => $this->cliente->id,
                    'ticket' => $ticket->ticket
                ]);
            } else {
                Parcela::factory()->paid()->create([
                    'id_cliente' => $this->cliente->id,
                    'ticket' => $ticket->ticket
                ]);
            }
        }

        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico', $this->cliente->id));

        $response->assertStatus(200);
        $response->assertSee('Taxa alta de devolução');
        $response->assertSee('text-red-600'); // Cor vermelha para taxa alta
    }

    public function test_expandable_cards_javascript_functionality()
    {
        $ticket = Ticket::factory()->create(['id_cliente' => $this->cliente->id]);
        Parcela::factory()->create([
            'id_cliente' => $this->cliente->id,
            'ticket' => $ticket->ticket
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico', $this->cliente->id));

        $response->assertStatus(200);
        $response->assertSee('toggleCard'); // Função JavaScript
        $response->assertSee('onclick'); // Evento de clique
        $response->assertSee('fas fa-chevron-down'); // Ícone de expansão
    }
}