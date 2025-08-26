<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\Parcela;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreditPurchaseReturnTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $cliente;
    protected $ticket;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar usuário de teste
        $this->user = User::factory()->create();
        
        // Criar cliente de teste
        $this->cliente = Cliente::factory()->create();
        
        // Criar ticket de teste
        $this->ticket = Ticket::factory()->create([
            'id_cliente' => $this->cliente->id,
            'ticket' => 'TEST123',
            'valor' => 150.00,
            'entrada' => 30.00,
            'parcelas' => 4
        ]);
    }

    public function test_purchase_history_shows_return_button_for_returnable_purchase()
    {
        // Criar parcelas não pagas
        Parcela::factory()->count(4)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'aguardando pagamento',
            'bd' => 'tabira'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico.compras', $this->cliente->id));

        $response->assertStatus(200);
        $response->assertSee('Devolução');
        $response->assertSee('confirmarDevolucao');
        $response->assertDontSee('cursor-not-allowed');
    }

    public function test_purchase_history_shows_disabled_return_button_for_non_returnable_purchase()
    {
        // Criar parcelas com uma paga
        Parcela::factory()->count(3)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'aguardando pagamento',
            'bd' => 'tabira'
        ]);

        Parcela::factory()->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'pago',
            'bd' => 'tabira'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.historico.compras', $this->cliente->id));

        $response->assertStatus(200);
        $response->assertSee('Devolução');
        $response->assertSee('cursor-not-allowed');
        $response->assertSee('disabled');
    }

    public function test_successful_return_process_via_api()
    {
        // Criar parcelas não pagas
        Parcela::factory()->count(4)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'aguardando pagamento',
            'bd' => 'tabira'
        ]);

        // Criar tabelas de teste
        $this->createTestTables();

        // Inserir dados de teste
        DB::table('vendas_tabira')->insert([
            [
                'ticket' => $this->ticket->ticket,
                'id_produto' => 1,
                'data_estorno' => null,
                'baixa_fiscal' => false
            ],
            [
                'ticket' => $this->ticket->ticket,
                'id_produto' => 2,
                'data_estorno' => null,
                'baixa_fiscal' => false
            ]
        ]);

        DB::table('estoque_tabira')->insert([
            ['id_produto' => 1, 'quantidade' => 10],
            ['id_produto' => 2, 'quantidade' => 5]
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.devolucao', [$this->cliente->id, $this->ticket->ticket]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Devolução processada com sucesso!',
            'ticket' => $this->ticket->ticket
        ]);

        // Verificar se as parcelas foram atualizadas
        $parcelasDevolvidas = Parcela::where('ticket', $this->ticket->ticket)
            ->where('status', 'devolucao')
            ->count();

        $this->assertEquals(4, $parcelasDevolvidas);

        // Verificar se as vendas foram atualizadas
        $vendasEstornadas = DB::table('vendas_tabira')
            ->where('ticket', $this->ticket->ticket)
            ->whereNotNull('data_estorno')
            ->where('baixa_fiscal', true)
            ->count();

        $this->assertEquals(2, $vendasEstornadas);

        // Verificar se o estoque foi atualizado
        $estoque1 = DB::table('estoque_tabira')->where('id_produto', 1)->first();
        $estoque2 = DB::table('estoque_tabira')->where('id_produto', 2)->first();

        $this->assertEquals(11, $estoque1->quantidade); // 10 + 1
        $this->assertEquals(6, $estoque2->quantidade);  // 5 + 1

        // Limpar tabelas de teste
        $this->dropTestTables();
    }

    public function test_return_fails_for_purchase_with_paid_installments()
    {
        // Criar parcelas com uma paga
        Parcela::factory()->count(3)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'aguardando pagamento',
            'bd' => 'tabira'
        ]);

        Parcela::factory()->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'pago',
            'bd' => 'tabira'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.devolucao', [$this->cliente->id, $this->ticket->ticket]));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false
        ]);
        $response->assertJsonFragment([
            'message' => 'Esta compra não pode ser devolvida. Verifique se não há parcelas pagas.'
        ]);
    }

    public function test_return_fails_for_nonexistent_ticket()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.devolucao', [$this->cliente->id, 'NONEXISTENT']));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Ticket não encontrado para este cliente.'
        ]);
    }

    public function test_return_fails_for_ticket_from_different_client()
    {
        // Criar outro cliente
        $outroCliente = Cliente::factory()->create();
        
        // Criar ticket para outro cliente
        $outroTicket = Ticket::factory()->create([
            'id_cliente' => $outroCliente->id,
            'ticket' => 'OTHER123'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.devolucao', [$this->cliente->id, $outroTicket->ticket]));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Ticket não encontrado para este cliente.'
        ]);
    }

    public function test_return_fails_for_already_returned_purchase()
    {
        // Criar parcelas já devolvidas
        Parcela::factory()->count(4)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'devolucao',
            'bd' => 'tabira'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.devolucao', [$this->cliente->id, $this->ticket->ticket]));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false
        ]);
    }

    public function test_unauthenticated_user_cannot_access_return_endpoint()
    {
        $response = $this->postJson(route('clientes.devolucao', [$this->cliente->id, $this->ticket->ticket]));

        $response->assertStatus(401);
    }

    public function test_ticket_model_can_be_returned_method()
    {
        // Criar parcelas não pagas
        Parcela::factory()->count(4)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'aguardando pagamento',
            'bd' => 'tabira'
        ]);

        $this->assertTrue($this->ticket->canBeReturned());

        // Pagar uma parcela
        $parcela = Parcela::where('ticket', $this->ticket->ticket)->first();
        $parcela->update([
            'status' => 'pago'
        ]);

        // Recarregar o ticket
        $this->ticket->refresh();
        $this->assertFalse($this->ticket->canBeReturned());
    }

    public function test_ticket_model_is_devolvida_method()
    {
        // Inicialmente não devolvida
        $this->assertFalse($this->ticket->isDevolvida());

        // Criar parcelas devolvidas
        Parcela::factory()->count(4)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'devolucao',
            'bd' => 'tabira'
        ]);

        // Recarregar o ticket
        $this->ticket->refresh();
        $this->assertTrue($this->ticket->isDevolvida());
    }

    public function test_return_process_is_transactional()
    {
        // Criar parcelas não pagas
        Parcela::factory()->count(4)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'aguardando pagamento',
            'bd' => 'invalid_city' // Cidade inválida para forçar erro
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.devolucao', [$this->cliente->id, $this->ticket->ticket]));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false
        ]);

        // Verificar se nenhuma parcela foi alterada (rollback funcionou)
        $parcelasDevolvidas = Parcela::where('ticket', $this->ticket->ticket)
            ->where('status', 'devolucao')
            ->count();

        $this->assertEquals(0, $parcelasDevolvidas);
    }

    private function createTestTables()
    {
        DB::statement('CREATE TABLE IF NOT EXISTS vendas_tabira (
            id_vendas INT AUTO_INCREMENT PRIMARY KEY,
            ticket VARCHAR(60),
            id_produto INT,
            data_estorno DATE NULL,
            baixa_fiscal BOOLEAN DEFAULT FALSE
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS estoque_tabira (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_produto INT,
            quantidade INT DEFAULT 0
        )');
    }

    private function dropTestTables()
    {
        DB::statement('DROP TABLE IF EXISTS vendas_tabira');
        DB::statement('DROP TABLE IF EXISTS estoque_tabira');
    }
}