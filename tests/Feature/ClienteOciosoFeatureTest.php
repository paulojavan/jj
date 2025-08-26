<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClienteOciosoFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar usuário para autenticação
        $this->user = User::factory()->create([
            'nivel' => 'funcionario'
        ]);
    }

    /**
     * Testa acesso à página de clientes ociosos
     */
    public function test_acesso_pagina_clientes_ociosos()
    {
        $response = $this->actingAs($this->user)
            ->get(route('clientes.ociosos'));

        $response->assertStatus(200);
        $response->assertViewIs('cliente.ociosos');
        $response->assertSee('Clientes Ociosos');
    }

    /**
     * Testa se página exibe clientes ociosos corretamente
     */
    public function test_exibe_clientes_ociosos_na_pagina()
    {
        // Criar cliente ocioso
        $clienteOcioso = Cliente::factory()->create([
            'nome' => 'João Silva Santos',
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'ativo',
            'telefone' => '11999999999'
        ]);

        // Criar cliente não ocioso
        $clienteAtivo = Cliente::factory()->create([
            'ociosidade' => Carbon::now()->subDays(100),
            'status' => 'ativo'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.ociosos'));

        $response->assertStatus(200);
        $response->assertSee('João Silva Santos');
        $response->assertDontSee($clienteAtivo->nome);
    }

    /**
     * Testa envio de mensagem WhatsApp atualiza ociosidade
     */
    public function test_envio_mensagem_whatsapp_atualiza_ociosidade()
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'João Silva Santos',
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'ativo',
            'telefone' => '11999999999'
        ]);

        $dataAnterior = $cliente->ociosidade;

        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.mensagem.ocioso', $cliente->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'whatsapp_url',
            'message'
        ]);

        // Verificar se a ociosidade foi atualizada
        $cliente->refresh();
        $this->assertNotEquals($dataAnterior, $cliente->ociosidade);
        $this->assertTrue(Carbon::parse($cliente->ociosidade)->isToday());
    }

    /**
     * Testa geração correta do link WhatsApp
     */
    public function test_link_whatsapp_gerado_corretamente()
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'João Silva Santos',
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'ativo',
            'telefone' => '11999999999'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.mensagem.ocioso', $cliente->id));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertStringContains('https://wa.me/5511999999999', $data['whatsapp_url']);
        $this->assertStringContains('Bom%20dia%2C%20Jo%C3%A3o%20Silva', $data['whatsapp_url']);
        $this->assertStringContains('joecio_calcados', $data['whatsapp_url']);
    }

    /**
     * Testa geração correta do link WhatsApp com nome que tem conjunção
     */
    public function test_link_whatsapp_com_conjuncao()
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'DHEISIELLE DA SILVA SIQUEIRA',
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'ativo',
            'telefone' => '11999999999'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.mensagem.ocioso', $cliente->id));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertStringContains('https://wa.me/5511999999999', $data['whatsapp_url']);
        $this->assertStringContains('Bom%20dia%2C%20DHEISIELLE', $data['whatsapp_url']);
        $this->assertStringNotContains('DA%20SILVA', $data['whatsapp_url']);
        $this->assertStringContains('joecio_calcados', $data['whatsapp_url']);
    }

    /**
     * Testa se cliente é removido da lista após contato
     */
    public function test_cliente_removido_da_lista_apos_contato()
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'João Silva Santos',
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'ativo',
            'telefone' => '11999999999'
        ]);

        // Verificar que cliente aparece na lista antes do contato
        $response = $this->actingAs($this->user)
            ->get(route('clientes.ociosos'));
        
        $response->assertSee('João Silva Santos');

        // Enviar mensagem
        $this->actingAs($this->user)
            ->postJson(route('clientes.mensagem.ocioso', $cliente->id));

        // Verificar que cliente não aparece mais na lista
        $response = $this->actingAs($this->user)
            ->get(route('clientes.ociosos'));
        
        $response->assertDontSee('João Silva Santos');
    }

    /**
     * Testa comportamento com lista vazia
     */
    public function test_comportamento_com_lista_vazia()
    {
        $response = $this->actingAs($this->user)
            ->get(route('clientes.ociosos'));

        $response->assertStatus(200);
        $response->assertSee('Nenhum cliente ocioso encontrado');
        $response->assertSee('Todos os clientes estão ativos');
    }

    /**
     * Testa erro quando cliente não tem telefone
     */
    public function test_erro_cliente_sem_telefone()
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'João Silva Santos',
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'ativo',
            'telefone' => null
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.mensagem.ocioso', $cliente->id));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Cliente não possui número de telefone cadastrado.'
        ]);
    }

    /**
     * Testa erro quando cliente não está mais ocioso
     */
    public function test_erro_cliente_nao_mais_ocioso()
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'João Silva Santos',
            'ociosidade' => Carbon::now()->subDays(100), // Menos de 150 dias
            'status' => 'ativo',
            'telefone' => '11999999999'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('clientes.mensagem.ocioso', $cliente->id));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Cliente não atende mais aos critérios de ociosidade.'
        ]);
    }

    /**
     * Testa se clientes com SPC não aparecem na lista
     */
    public function test_clientes_com_spc_nao_aparecem()
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'João Silva Santos',
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'ativo',
            'telefone' => '11999999999'
        ]);

        // Criar ticket com SPC
        Ticket::factory()->create([
            'id_cliente' => $cliente->id,
            'spc' => true
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.ociosos'));

        $response->assertStatus(200);
        $response->assertDontSee('João Silva Santos');
    }

    /**
     * Testa paginação da lista
     */
    public function test_paginacao_lista_clientes()
    {
        // Criar 25 clientes ociosos
        Cliente::factory()->count(25)->create([
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'ativo',
            'telefone' => '11999999999'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('clientes.ociosos'));

        $response->assertStatus(200);
        
        // Verificar se há links de paginação
        $response->assertViewHas('clientes');
        $clientes = $response->viewData('clientes');
        $this->assertTrue($clientes->hasPages());
    }

    /**
     * Testa acesso não autenticado
     */
    public function test_acesso_nao_autenticado_redirecionado()
    {
        $response = $this->get(route('clientes.ociosos'));

        $response->assertRedirect(route('login'));
    }
}