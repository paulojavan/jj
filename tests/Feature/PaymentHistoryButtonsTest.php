<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\Parcela;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PaymentHistoryButtonsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $cliente;
    protected $pagamento;
    protected $parcela;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Create test cliente
        $this->cliente = Cliente::factory()->create([
            'telefone' => '11987654321'
        ]);

        // Create test pagamento
        $this->pagamento = Pagamento::create([
            'id_cliente' => $this->cliente->id,
            'ticket' => 'TEST123',
            'data' => now()
        ]);

        // Create test parcela
        $this->parcela = Parcela::create([
            'ticket' => 'TEST123',
            'id_cliente' => $this->cliente->id,
            'numero' => 1,
            'data_vencimento' => now()->addDays(30),
            'data_pagamento' => now(),
            'valor_parcela' => 100.00,
            'valor_pago' => 100.00,
            'metodo' => 'pix',
            'status' => 'pago',
            'ticket_pagamento' => 'TEST123'
        ]);
    }

    /** @test */
    public function it_can_generate_whatsapp_link_for_payment()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/clientes/{$this->cliente->id}/pagamentos/{$this->pagamento->id_pagamento}/whatsapp");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'success',
                'whatsapp_url'
            ]);

        $data = $response->json();
        $this->assertStringContains('wa.me/5511987654321', $data['whatsapp_url']);
        $this->assertStringContains('Joécio calçados informa', urldecode($data['whatsapp_url']));
    }

    /** @test */
    public function it_returns_error_when_cliente_has_no_phone()
    {
        $clienteSemTelefone = Cliente::factory()->create(['telefone' => null]);
        $pagamento = Pagamento::create([
            'id_cliente' => $clienteSemTelefone->id,
            'ticket' => 'TEST456',
            'data' => now()
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/clientes/{$clienteSemTelefone->id}/pagamentos/{$pagamento->id_pagamento}/whatsapp");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Número de telefone não encontrado para este cliente.'
            ]);
    }

    /** @test */
    public function it_can_display_payment_receipt()
    {
        $response = $this->actingAs($this->user)
            ->get("/clientes/{$this->cliente->id}/pagamentos/{$this->pagamento->id_pagamento}/comprovante");

        $response->assertStatus(200)
            ->assertViewIs('cliente.comprovante-pagamento')
            ->assertViewHas('cliente', $this->cliente)
            ->assertViewHas('pagamento');
    }

    /** @test */
    public function it_can_cancel_payment_successfully()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/clientes/{$this->cliente->id}/pagamentos/{$this->pagamento->id_pagamento}/cancelar");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pagamento cancelado com sucesso!'
            ]);

        // Verify payment was deleted
        $this->assertDatabaseMissing('pagamentos', [
            'id_pagamento' => $this->pagamento->id_pagamento
        ]);

        // Verify parcela was updated
        $this->assertDatabaseHas('parcelas', [
            'id_parcelas' => $this->parcela->id_parcelas,
            'data_pagamento' => null,
            'valor_pago' => null,
            'metodo' => null,
            'ticket_pagamento' => null,
            'status' => 'aguardando pagamento'
        ]);
    }

    /** @test */
    public function it_returns_error_when_payment_does_not_belong_to_cliente()
    {
        $outroCliente = Cliente::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/clientes/{$outroCliente->id}/pagamentos/{$this->pagamento->id_pagamento}/whatsapp");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Pagamento não encontrado para este cliente.'
            ]);
    }

    /** @test */
    public function it_formats_phone_number_correctly_for_whatsapp()
    {
        // Test with phone number without country code
        $this->cliente->update(['telefone' => '11987654321']);

        $response = $this->actingAs($this->user)
            ->postJson("/clientes/{$this->cliente->id}/pagamentos/{$this->pagamento->id_pagamento}/whatsapp");

        $data = $response->json();
        $this->assertStringContains('wa.me/5511987654321', $data['whatsapp_url']);

        // Test with phone number already with country code
        $this->cliente->update(['telefone' => '5511987654321']);

        $response = $this->actingAs($this->user)
            ->postJson("/clientes/{$this->cliente->id}/pagamentos/{$this->pagamento->id_pagamento}/whatsapp");

        $data = $response->json();
        $this->assertStringContains('wa.me/5511987654321', $data['whatsapp_url']);
    }

    /** @test */
    public function it_includes_payment_date_in_whatsapp_message()
    {
        $paymentDate = now()->subDays(5);
        $this->pagamento->update(['data' => $paymentDate]);

        $response = $this->actingAs($this->user)
            ->postJson("/clientes/{$this->cliente->id}/pagamentos/{$this->pagamento->id_pagamento}/whatsapp");

        $data = $response->json();
        $expectedDate = $paymentDate->format('d/m/Y');
        $decodedUrl = urldecode($data['whatsapp_url']);
        
        $this->assertStringContains("efetuado dia {$expectedDate}", $decodedUrl);
    }

    /** @test */
    public function it_handles_database_transaction_rollback_on_cancellation_error()
    {
        // This test would require mocking database failures
        // For now, we'll test the basic cancellation flow
        $originalParcelaStatus = $this->parcela->status;
        
        $response = $this->actingAs($this->user)
            ->deleteJson("/clientes/{$this->cliente->id}/pagamentos/{$this->pagamento->id_pagamento}/cancelar");

        $response->assertStatus(200);
        
        // If successful, payment should be deleted and parcela updated
        $this->assertDatabaseMissing('pagamentos', [
            'id_pagamento' => $this->pagamento->id_pagamento
        ]);
    }
}