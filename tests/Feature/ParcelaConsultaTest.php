<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\cliente;
use App\Models\Parcela;
use App\Models\Autorizado;
use App\Models\MultaConfiguracao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ParcelaConsultaTest extends TestCase
{
    // use RefreshDatabase; // Comentado temporariamente devido a problemas de migração
    
    private User $user;
    private cliente $cliente;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar usuário para autenticação
        $this->user = User::factory()->create();
        
        // Criar cliente de teste
        $this->cliente = cliente::factory()->create([
            'cpf' => '123.456.789-01',
            'nome' => 'João da Silva',
            'foto' => 'foto.jpg',
            'pasta' => 'pasta-teste'
        ]);
        
        // Criar configuração de multa
        MultaConfiguracao::create([
            'taxa_multa' => 2.0,
            'taxa_juros' => 3.0,
            'dias_carencia' => 5,
            'dias_cobranca' => 30
        ]);
    }
    
    public function test_exibe_pagina_de_consulta()
    {
        $response = $this->actingAs($this->user)
            ->get(route('parcelas.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('parcelas.consulta');
        $response->assertSee('Acompanhe suas Parcelas');
        $response->assertSee('Digite seu CPF');
    }
    
    public function test_consulta_com_cpf_invalido()
    {
        $response = $this->actingAs($this->user)
            ->post(route('parcelas.consultar'), [
                'cpf' => '123.456.789-00' // CPF inválido
            ]);
        
        $response->assertRedirect(route('parcelas.index'));
        $response->assertSessionHasErrors(['cpf']);
    }
    
    public function test_consulta_com_cpf_inexistente()
    {
        $response = $this->actingAs($this->user)
            ->post(route('parcelas.consultar'), [
                'cpf' => '987.654.321-00' // CPF válido mas inexistente
            ]);
        
        $response->assertRedirect(route('parcelas.index'));
        $response->assertSessionHas('error', 'Cliente não encontrado com este CPF');
    }
    
    public function test_consulta_cliente_sem_parcelas_pendentes()
    {
        $response = $this->actingAs($this->user)
            ->post(route('parcelas.consultar'), [
                'cpf' => $this->cliente->cpf
            ]);
        
        $response->assertStatus(200);
        $response->assertViewIs('parcelas.resultado');
        $response->assertViewHas('cliente', $this->cliente);
        $response->assertSee('Nenhuma parcela pendente encontrada');
    }
    
    public function test_consulta_cliente_com_parcelas_do_titular()
    {
        // Criar parcelas do titular
        Parcela::create([
            'ticket' => 'TICKET001',
            'id_cliente' => $this->cliente->id,
            'id_autorizado' => null,
            'numero' => '1/3',
            'data_vencimento' => Carbon::now()->subDays(10),
            'valor_parcela' => 100.00,
            'status' => 'aguardando pagamento',
            'bd' => 'teste'
        ]);
        
        Parcela::create([
            'ticket' => 'TICKET001',
            'id_cliente' => $this->cliente->id,
            'id_autorizado' => null,
            'numero' => '2/3',
            'data_vencimento' => Carbon::now()->addDays(30),
            'valor_parcela' => 100.00,
            'status' => 'aguardando pagamento',
            'bd' => 'teste'
        ]);
        
        $response = $this->actingAs($this->user)
            ->post(route('parcelas.consultar'), [
                'cpf' => $this->cliente->cpf
            ]);
        
        $response->assertStatus(200);
        $response->assertViewIs('parcelas.resultado');
        $response->assertViewHas('cliente', $this->cliente);
        $response->assertSee('Parcelas do Titular');
        $response->assertSee('TICKET001');
        $response->assertSee('1/3');
        $response->assertSee('2/3');
        $response->assertSee('10 dias de atraso'); // Primeira parcela vencida
    }
    
    public function test_consulta_cliente_com_parcelas_de_autorizados()
    {
        // Criar autorizado
        $autorizado = Autorizado::create([
            'idCliente' => $this->cliente->id,
            'nome' => 'Maria Autorizada',
            'rg' => '123456789',
            'cpf' => '987.654.321-01',
            'foto' => 'foto_autorizada.jpg',
            'rg_frente' => 'rg_frente.jpg',
            'rg_verso' => 'rg_verso.jpg',
            'cpf_foto' => 'cpf_foto.jpg',
            'pasta' => 'pasta-autorizada'
        ]);
        
        // Criar parcela do autorizado
        Parcela::create([
            'ticket' => 'TICKET002',
            'id_cliente' => $this->cliente->id,
            'id_autorizado' => $autorizado->id,
            'numero' => '1/2',
            'data_vencimento' => Carbon::now()->subDays(5),
            'valor_parcela' => 150.00,
            'status' => 'aguardando pagamento',
            'bd' => 'teste'
        ]);
        
        $response = $this->actingAs($this->user)
            ->post(route('parcelas.consultar'), [
                'cpf' => $this->cliente->cpf
            ]);
        
        $response->assertStatus(200);
        $response->assertViewIs('parcelas.resultado');
        $response->assertSee('Parcelas de Maria Autorizada');
        $response->assertSee('TICKET002');
        $response->assertSee('1/2');
    }
    
    public function test_calculo_de_multa_e_juros_em_parcela_vencida()
    {
        // Criar parcela vencida há 15 dias (além da carência de 5 dias)
        Parcela::create([
            'ticket' => 'TICKET003',
            'id_cliente' => $this->cliente->id,
            'id_autorizado' => null,
            'numero' => '1/1',
            'data_vencimento' => Carbon::now()->subDays(15),
            'valor_parcela' => 100.00,
            'status' => 'aguardando pagamento',
            'bd' => 'teste'
        ]);
        
        $response = $this->actingAs($this->user)
            ->post(route('parcelas.consultar'), [
                'cpf' => $this->cliente->cpf
            ]);
        
        $response->assertStatus(200);
        $response->assertSee('Inclui multa e juros');
        
        // Valor esperado:
        // Parcela: 100.00
        // Multa: 100 * 2% = 2.00
        // Juros: 100 * (3%/30) * (15-5) = 100 * 0.001 * 10 = 1.00
        // Total: 103.00
        $response->assertSee('R$ 103,00');
    }
    
    public function test_parcela_dentro_da_carencia_nao_tem_multa_juros()
    {
        // Criar parcela vencida há 3 dias (dentro da carência de 5 dias)
        Parcela::create([
            'ticket' => 'TICKET004',
            'id_cliente' => $this->cliente->id,
            'id_autorizado' => null,
            'numero' => '1/1',
            'data_vencimento' => Carbon::now()->subDays(3),
            'valor_parcela' => 100.00,
            'status' => 'aguardando pagamento',
            'bd' => 'teste'
        ]);
        
        $response = $this->actingAs($this->user)
            ->post(route('parcelas.consultar'), [
                'cpf' => $this->cliente->cpf
            ]);
        
        $response->assertStatus(200);
        $response->assertDontSee('Inclui multa e juros');
        $response->assertSee('R$ 100,00'); // Valor original sem acréscimos
    }
    
    public function test_navegacao_para_nova_consulta()
    {
        $response = $this->actingAs($this->user)
            ->post(route('parcelas.consultar'), [
                'cpf' => $this->cliente->cpf
            ]);
        
        $response->assertStatus(200);
        $response->assertSee('Nova Consulta');
        
        // Verificar se o link está correto
        $response->assertSee(route('parcelas.index'));
    }
    
    public function test_exibe_informacoes_do_cliente_na_pagina_de_resultado()
    {
        $response = $this->actingAs($this->user)
            ->post(route('parcelas.consultar'), [
                'cpf' => $this->cliente->cpf
            ]);
        
        $response->assertStatus(200);
        $response->assertSee($this->cliente->nome);
        $response->assertSee($this->cliente->cpf);
    }
    
    public function test_formulario_tem_validacao_javascript()
    {
        $response = $this->actingAs($this->user)
            ->get(route('parcelas.index'));
        
        $response->assertStatus(200);
        $response->assertSee('addEventListener');
        $response->assertSee('cpf-mask'); // Verifica se tem código de máscara
    }
}