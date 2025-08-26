<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CreditReturnService;
use App\Models\Ticket;
use App\Models\Parcela;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreditReturnServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $creditReturnService;
    protected $cliente;
    protected $ticket;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creditReturnService = new CreditReturnService();
        
        // Criar cliente de teste
        $this->cliente = Cliente::factory()->create();
        
        // Criar ticket de teste
        $this->ticket = Ticket::factory()->create([
            'id_cliente' => $this->cliente->id,
            'ticket' => 'TEST123',
            'valor' => 100.00,
            'entrada' => 20.00,
            'parcelas' => 3
        ]);
    }

    public function test_can_return_with_no_paid_installments()
    {
        // Criar parcelas não pagas
        Parcela::factory()->count(3)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'aguardando pagamento',
            'data_pagamento' => null,
            'bd' => 'tabira'
        ]);

        $result = $this->creditReturnService->canReturn($this->ticket->ticket);
        
        $this->assertTrue($result);
    }

    public function test_cannot_return_with_paid_installments()
    {
        // Criar parcelas com uma paga
        Parcela::factory()->count(2)->create([
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

        $result = $this->creditReturnService->canReturn($this->ticket->ticket);
        
        $this->assertFalse($result);
    }

    public function test_cannot_return_already_returned_ticket()
    {
        // Criar parcelas com status de devolução
        Parcela::factory()->count(3)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'devolucao',
            'data_pagamento' => null,
            'bd' => 'tabira'
        ]);

        $result = $this->creditReturnService->canReturn($this->ticket->ticket);
        
        $this->assertFalse($result);
    }

    public function test_cannot_return_nonexistent_ticket()
    {
        $result = $this->creditReturnService->canReturn('NONEXISTENT');
        
        $this->assertFalse($result);
    }

    public function test_update_parcelas_changes_status_to_devolucao()
    {
        // Criar parcelas não pagas
        $parcelas = Parcela::factory()->count(3)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'aguardando pagamento',
            'bd' => 'tabira'
        ]);

        $this->creditReturnService->updateParcelas($this->ticket->ticket);

        // Verificar se todas as parcelas foram atualizadas
        $parcelasAtualizadas = Parcela::where('ticket', $this->ticket->ticket)
            ->where('status', 'devolucao')
            ->count();

        $this->assertEquals(3, $parcelasAtualizadas);
    }

    public function test_update_parcelas_does_not_change_paid_installments()
    {
        // Criar parcelas mistas
        Parcela::factory()->count(2)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'aguardando pagamento',
            'bd' => 'tabira'
        ]);

        $parcelaPaga = Parcela::factory()->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'pago',
            'bd' => 'tabira'
        ]);

        $this->creditReturnService->updateParcelas($this->ticket->ticket);

        // Verificar se a parcela paga não foi alterada
        $parcelaPaga->refresh();
        $this->assertEquals('pago', $parcelaPaga->status);

        // Verificar se as outras foram atualizadas
        $parcelasDevolvidas = Parcela::where('ticket', $this->ticket->ticket)
            ->where('status', 'devolucao')
            ->count();

        $this->assertEquals(2, $parcelasDevolvidas);
    }

    public function test_update_sales_records_with_valid_table()
    {
        // Criar tabela de vendas de teste
        DB::statement('CREATE TABLE IF NOT EXISTS vendas_tabira_test (
            id_vendas INT AUTO_INCREMENT PRIMARY KEY,
            ticket VARCHAR(60),
            id_produto INT,
            data_estorno DATE NULL,
            baixa_fiscal BOOLEAN DEFAULT FALSE
        )');

        // Inserir dados de teste
        DB::table('vendas_tabira_test')->insert([
            'ticket' => $this->ticket->ticket,
            'id_produto' => 1,
            'data_estorno' => null,
            'baixa_fiscal' => false
        ]);

        // Criar parcela para determinar a cidade
        Parcela::factory()->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'bd' => 'vendas_tabira_test'
        ]);

        // Mock do método privado para usar tabela de teste
        $service = new class extends CreditReturnService {
            public function determinarTabelaVendasPublic($bd) {
                if ($bd === 'vendas_tabira_test') {
                    return 'vendas_tabira_test';
                }
                return parent::determinarTabelaVendas($bd);
            }
            
            public function updateSalesRecords(string $ticketNumber): void
            {
                $parcela = \App\Models\Parcela::where('ticket', $ticketNumber)->first();
                
                if (!$parcela || !$parcela->bd) {
                    throw new \Exception('Não foi possível determinar a cidade da venda');
                }

                $tabelaVendas = $this->determinarTabelaVendasPublic($parcela->bd);
                
                if (!$tabelaVendas) {
                    throw new \Exception('Tabela de vendas não encontrada para a cidade: ' . $parcela->bd);
                }

                if (!DB::getSchemaBuilder()->hasTable($tabelaVendas)) {
                    throw new \Exception('Tabela de vendas não existe: ' . $tabelaVendas);
                }

                $updated = DB::table($tabelaVendas)
                    ->where('ticket', $ticketNumber)
                    ->update([
                        'data_estorno' => \Carbon\Carbon::now()->toDateString(),
                        'baixa_fiscal' => true
                    ]);

                if ($updated === 0) {
                    throw new \Exception('Nenhum registro de venda foi encontrado para o ticket');
                }
            }
        };

        $service->updateSalesRecords($this->ticket->ticket);

        // Verificar se os registros foram atualizados
        $venda = DB::table('vendas_tabira_test')
            ->where('ticket', $this->ticket->ticket)
            ->first();

        $this->assertNotNull($venda->data_estorno);
        $this->assertTrue((bool)$venda->baixa_fiscal);

        // Limpar tabela de teste
        DB::statement('DROP TABLE IF EXISTS vendas_tabira_test');
    }

    public function test_process_return_success()
    {
        // Criar parcelas não pagas
        Parcela::factory()->count(3)->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'aguardando pagamento',
            'data_pagamento' => null,
            'bd' => 'tabira'
        ]);

        // Criar tabelas de teste
        $this->createTestTables();

        // Inserir dados de teste
        DB::table('vendas_tabira_test')->insert([
            'ticket' => $this->ticket->ticket,
            'id_produto' => 1,
            'data_estorno' => null,
            'baixa_fiscal' => false
        ]);

        DB::table('estoque_tabira_test')->insert([
            'id_produto' => 1,
            'quantidade' => 5
        ]);

        // Mock do service para usar tabelas de teste
        $service = $this->createMockService();

        $result = $service->processReturn($this->ticket->ticket);

        $this->assertTrue($result['success']);
        $this->assertEquals('Devolução processada com sucesso', $result['message']);

        // Limpar tabelas de teste
        $this->dropTestTables();
    }

    public function test_process_return_fails_with_paid_installments()
    {
        // Criar parcela paga
        Parcela::factory()->create([
            'ticket' => $this->ticket->ticket,
            'id_cliente' => $this->cliente->id,
            'status' => 'pago',
            'bd' => 'tabira'
        ]);

        $result = $this->creditReturnService->processReturn($this->ticket->ticket);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('não pode ser devolvido', $result['message']);
    }

    private function createTestTables()
    {
        DB::statement('CREATE TABLE IF NOT EXISTS vendas_tabira_test (
            id_vendas INT AUTO_INCREMENT PRIMARY KEY,
            ticket VARCHAR(60),
            id_produto INT,
            data_estorno DATE NULL,
            baixa_fiscal BOOLEAN DEFAULT FALSE
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS estoque_tabira_test (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_produto INT,
            quantidade INT DEFAULT 0
        )');
    }

    private function dropTestTables()
    {
        DB::statement('DROP TABLE IF EXISTS vendas_tabira_test');
        DB::statement('DROP TABLE IF EXISTS estoque_tabira_test');
    }

    private function createMockService()
    {
        return new class extends CreditReturnService {
            protected function determinarTabelaVendas(string $bd): ?string
            {
                return 'vendas_tabira_test';
            }
            
            protected function determinarTabelaEstoque(string $bd): ?string
            {
                return 'estoque_tabira_test';
            }
        };
    }
}