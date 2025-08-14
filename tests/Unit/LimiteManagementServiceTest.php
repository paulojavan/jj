<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\LimiteManagementService;
use App\Models\cliente;
use App\Models\User;
use App\Models\LimiteLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;

class LimiteManagementServiceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected $limiteManagementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->limiteManagementService = new LimiteManagementService();
    }

    /** @test */
    public function it_updates_client_limit_successfully()
    {
        $cliente = cliente::factory()->create(['limite' => 100.00]);
        $user = User::factory()->create();

        $resultado = $this->limiteManagementService->atualizarLimite(
            $cliente->id,
            200.00,
            $user->id
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals('Limite atualizado com sucesso!', $resultado['message']);
        
        // Verificar se o limite foi atualizado no banco
        $cliente->refresh();
        $this->assertEquals(200.00, $cliente->limite);
        
        // Verificar se o log foi criado
        $this->assertDatabaseHas('limite_logs', [
            'cliente_id' => $cliente->id,
            'usuario_id' => $user->id,
            'acao' => 'limite_alterado',
            'valor_anterior' => '100',
            'valor_novo' => '200'
        ]);
    }

    /** @test */
    public function it_rejects_negative_limit_values()
    {
        $cliente = cliente::factory()->create(['limite' => 100.00]);
        $user = User::factory()->create();

        $resultado = $this->limiteManagementService->atualizarLimite(
            $cliente->id,
            -50.00,
            $user->id
        );

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('número positivo', $resultado['message']);
        
        // Verificar se o limite não foi alterado
        $cliente->refresh();
        $this->assertEquals(100.00, $cliente->limite);
    }

    /** @test */
    public function it_changes_client_status_successfully()
    {
        $cliente = cliente::factory()->create(['status' => 'inativo']);
        $user = User::factory()->create();

        $resultado = $this->limiteManagementService->alterarStatus(
            $cliente->id,
            'ativo',
            $user->id
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals('Status atualizado com sucesso!', $resultado['message']);
        
        // Verificar se o status foi atualizado no banco
        $cliente->refresh();
        $this->assertEquals('ativo', $cliente->status);
        
        // Verificar se o log foi criado
        $this->assertDatabaseHas('limite_logs', [
            'cliente_id' => $cliente->id,
            'usuario_id' => $user->id,
            'acao' => 'status_alterado',
            'valor_anterior' => 'inativo',
            'valor_novo' => 'ativo'
        ]);
    }

    /** @test */
    public function it_rejects_invalid_status_values()
    {
        $cliente = cliente::factory()->create(['status' => 'ativo']);
        $user = User::factory()->create();

        $resultado = $this->limiteManagementService->alterarStatus(
            $cliente->id,
            'invalido',
            $user->id
        );

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('ativo', $resultado['message']);
        
        // Verificar se o status não foi alterado
        $cliente->refresh();
        $this->assertEquals('ativo', $cliente->status);
    }

    /** @test */
    public function it_logs_changes_correctly()
    {
        $cliente = cliente::factory()->create();
        $user = User::factory()->create();

        $resultado = $this->limiteManagementService->registrarLog(
            $cliente->id,
            'limite_alterado',
            '100.00',
            '200.00',
            $user->id,
            'Teste de log'
        );

        $this->assertTrue($resultado);
        
        $this->assertDatabaseHas('limite_logs', [
            'cliente_id' => $cliente->id,
            'usuario_id' => $user->id,
            'acao' => 'limite_alterado',
            'valor_anterior' => '100.00',
            'valor_novo' => '200.00',
            'observacoes' => 'Teste de log'
        ]);
    }
}