<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ParcelaConsultaSimpleTest extends TestCase
{
    public function test_rota_parcelas_index_existe()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get('/parcelas');
        
        $response->assertStatus(200);
    }
    
    public function test_rota_parcelas_consultar_existe()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->post('/parcelas/consultar', [
                'cpf' => '123.456.789-01'
            ]);
        
        // Pode retornar 200 (com resultado) ou 302 (redirect com erro)
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }
    
    public function test_controller_parcela_existe()
    {
        $this->assertTrue(class_exists(\App\Http\Controllers\ParcelaController::class));
    }
    
    public function test_service_parcela_calculo_existe()
    {
        $this->assertTrue(class_exists(\App\Services\ParcelaCalculoService::class));
    }
    
    public function test_request_consulta_parcela_existe()
    {
        $this->assertTrue(class_exists(\App\Http\Requests\ConsultaParcelaRequest::class));
    }
}