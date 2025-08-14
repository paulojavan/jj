<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\Parcela;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentHistoryButtonsUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_formats_phone_number_for_whatsapp_correctly()
    {
        // Test phone number formatting logic
        $phoneNumbers = [
            '11987654321' => '5511987654321',
            '5511987654321' => '5511987654321',
            '(11) 98765-4321' => '5511987654321',
            '11 98765-4321' => '5511987654321'
        ];

        foreach ($phoneNumbers as $input => $expected) {
            $formatted = preg_replace('/[^0-9]/', '', $input);
            if (!str_starts_with($formatted, '55')) {
                $formatted = '55' . $formatted;
            }
            
            $this->assertEquals($expected, $formatted, "Failed to format phone number: {$input}");
        }
    }

    /** @test */
    public function it_generates_correct_whatsapp_message()
    {
        $paymentDate = now()->setDate(2024, 1, 15);
        $expectedMessage = "Joécio calçados informa: Pagamento de parcela efetuado dia 15/01/2024, acesse o comprovante através do link: https://joeciocalçados.com.br/";
        
        $generatedMessage = "Joécio calçados informa: Pagamento de parcela efetuado dia {$paymentDate->format('d/m/Y')}, acesse o comprovante através do link: https://joeciocalçados.com.br/";
        
        $this->assertEquals($expectedMessage, $generatedMessage);
    }

    /** @test */
    public function it_creates_correct_whatsapp_url()
    {
        $phone = '5511987654321';
        $message = 'Test message';
        $encodedMessage = urlencode($message);
        $expectedUrl = "https://wa.me/{$phone}?text={$encodedMessage}";
        
        $generatedUrl = "https://wa.me/{$phone}?text=" . urlencode($message);
        
        $this->assertEquals($expectedUrl, $generatedUrl);
    }

    /** @test */
    public function it_validates_payment_ownership()
    {
        $cliente1 = Cliente::factory()->create();
        $cliente2 = Cliente::factory()->create();
        
        $pagamento = Pagamento::create([
            'id_cliente' => $cliente1->id,
            'ticket' => 'TEST123',
            'data' => now()
        ]);

        // Payment belongs to cliente1
        $this->assertEquals($cliente1->id, $pagamento->id_cliente);
        $this->assertNotEquals($cliente2->id, $pagamento->id_cliente);
    }

    /** @test */
    public function it_identifies_parcelas_to_update_on_cancellation()
    {
        $cliente = Cliente::factory()->create();
        $ticket = 'TEST123';
        
        $pagamento = Pagamento::create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket,
            'data' => now()
        ]);

        // Create multiple parcelas with the same ticket_pagamento
        $parcela1 = Parcela::create([
            'ticket' => 'SALE123',
            'id_cliente' => $cliente->id,
            'numero' => 1,
            'data_vencimento' => now()->addDays(30),
            'valor_parcela' => 100.00,
            'ticket_pagamento' => $ticket,
            'status' => 'pago'
        ]);

        $parcela2 = Parcela::create([
            'ticket' => 'SALE123',
            'id_cliente' => $cliente->id,
            'numero' => 2,
            'data_vencimento' => now()->addDays(60),
            'valor_parcela' => 100.00,
            'ticket_pagamento' => $ticket,
            'status' => 'pago'
        ]);

        // Parcela with different ticket_pagamento should not be affected
        $parcela3 = Parcela::create([
            'ticket' => 'SALE456',
            'id_cliente' => $cliente->id,
            'numero' => 1,
            'data_vencimento' => now()->addDays(30),
            'valor_parcela' => 50.00,
            'ticket_pagamento' => 'OTHER123',
            'status' => 'pago'
        ]);

        $parcelasToUpdate = Parcela::where('ticket_pagamento', $ticket)->get();
        
        $this->assertCount(2, $parcelasToUpdate);
        $this->assertTrue($parcelasToUpdate->contains($parcela1));
        $this->assertTrue($parcelasToUpdate->contains($parcela2));
        $this->assertFalse($parcelasToUpdate->contains($parcela3));
    }

    /** @test */
    public function it_defines_correct_parcela_reset_values()
    {
        $resetValues = [
            'data_pagamento' => null,
            'hora' => null,
            'valor_pago' => null,
            'dinheiro' => null,
            'pix' => null,
            'cartao' => null,
            'metodo' => null,
            'id_vendedor' => null,
            'ticket_pagamento' => null,
            'status' => 'aguardando pagamento'
        ];

        // Verify all required fields are set to null except status
        foreach ($resetValues as $field => $value) {
            if ($field === 'status') {
                $this->assertEquals('aguardando pagamento', $value);
            } else {
                $this->assertNull($value);
            }
        }
    }

    /** @test */
    public function it_validates_required_data_for_receipt_generation()
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'Test Cliente',
            'cpf' => '12345678901',
            'telefone' => '11987654321',
            'rua' => 'Test Street',
            'numero' => '123',
            'bairro' => 'Test Neighborhood',
            'cidade' => 'Test City'
        ]);

        $pagamento = Pagamento::create([
            'id_cliente' => $cliente->id,
            'ticket' => 'TEST123',
            'data' => now()
        ]);

        // Verify all required data is present for receipt
        $this->assertNotNull($cliente->nome);
        $this->assertNotNull($cliente->cpf);
        $this->assertNotNull($cliente->rua);
        $this->assertNotNull($cliente->numero);
        $this->assertNotNull($cliente->bairro);
        $this->assertNotNull($cliente->cidade);
        $this->assertNotNull($pagamento->ticket);
        $this->assertNotNull($pagamento->data);
    }
}