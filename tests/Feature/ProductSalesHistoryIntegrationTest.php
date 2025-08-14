<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ProductSalesHistoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $produto;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user with city
        $this->user = User::factory()->create([
            'cidade' => 1,
            'name' => 'Test User',
            'nivel' => 'usuario'
        ]);
        
        // Create test product
        $this->produto = Produto::factory()->create([
            'produto' => 'Test Product',
            'preco' => 100.00
        ]);
        
        // Create test city
        DB::table('cidades')->insert([
            'id' => 1,
            'cidade' => 'Test City',
            'status' => 'ativa'
        ]);
        
        // Create test tables
        $this->createTestTables();
        $this->insertTestData();
    }

    public function test_product_detail_page_displays_sales_history()
    {
        $response = $this->actingAs($this->user)
            ->get(route('produtos.exibir', $this->produto));

        $response->assertStatus(200);
        $response->assertSee('Últimas 30 Vendas');
        $response->assertSee('Test User'); // Seller name
        $response->assertSee('R$ 50,00'); // Payment values
        $response->assertSee('40'); // Size
        $response->assertSee('Devolução'); // Return button
        $response->assertSee('Trocar'); // Exchange button
    }

    public function test_product_detail_page_shows_returned_items_with_red_background()
    {
        // Insert a returned sale
        DB::table('vendas_test_city')->insert([
            'id_vendedor' => $this->user->id,
            'id_produto' => $this->produto->id,
            'data_venda' => now()->subDays(1)->toDateString(),
            'valor_dinheiro' => 100.00,
            'valor_pix' => 0.00,
            'valor_cartao' => 0.00,
            'numeracao' => '41',
            'data_estorno' => now()->toDateString(), // Returned
            'ticket' => null
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('produtos.exibir', $this->produto));

        $response->assertStatus(200);
        $response->assertSee('bg-red-100'); // Red background class
        $response->assertSee('DEVOLVIDO'); // Returned status
    }

    public function test_return_processing_workflow()
    {
        // Get a sale ID from test data
        $sale = DB::table('vendas_test_city')
            ->where('id_produto', $this->produto->id)
            ->whereNull('data_estorno')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson(route('produtos.processReturn'), [
                'sale_id' => $sale->id
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        // Verify sale was marked as returned
        $updatedSale = DB::table('vendas_test_city')->where('id', $sale->id)->first();
        $this->assertNotNull($updatedSale->data_estorno);

        // Verify inventory was updated
        $stock = DB::table('estoque_test_city')
            ->where('id_produto', $this->produto->id)
            ->where('numero', $sale->numeracao)
            ->first();
        $this->assertEquals(6, $stock->quantidade); // Original 5 + 1 returned
        
        // Verify main product quantity was updated
        $produto = DB::table('produtos')->where('id', $this->produto->id)->first();
        $this->assertEquals($this->produto->quantidade + 1, $produto->quantidade);
    }

    public function test_exchange_processing_workflow()
    {
        // Get a sale ID from test data
        $sale = DB::table('vendas_test_city')
            ->where('id_produto', $this->produto->id)
            ->whereNull('data_estorno')
            ->first();

        $response = $this->actingAs($this->user)
            ->postJson(route('produtos.processExchange'), [
                'sale_id' => $sale->id,
                'new_size' => '42'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        // Verify sale numeracao was updated
        $updatedSale = DB::table('vendas_test_city')->where('id', $sale->id)->first();
        $this->assertEquals('42', $updatedSale->numeracao);

        // Verify original size inventory was incremented
        $originalStock = DB::table('estoque_test_city')
            ->where('id_produto', $this->produto->id)
            ->where('numero', $sale->numeracao)
            ->first();
        $this->assertEquals(6, $originalStock->quantidade); // Original 5 + 1

        // Verify new size inventory was decremented
        $newStock = DB::table('estoque_test_city')
            ->where('id_produto', $this->produto->id)
            ->where('numero', '42')
            ->first();
        $this->assertEquals(2, $newStock->quantidade); // Original 3 - 1
        
        // Verify main product quantity was NOT changed (exchange doesn't affect total)
        $produto = DB::table('produtos')->where('id', $this->produto->id)->first();
        $this->assertEquals($this->produto->quantidade, $produto->quantidade);
    }

    public function test_available_sizes_ajax_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('produtos.availableSizes', $this->produto));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['numero', 'quantidade']
        ]);

        $sizes = $response->json();
        
        // Should only return sizes with stock > 0
        foreach ($sizes as $size) {
            $this->assertGreaterThan(0, $size['quantidade']);
        }
    }

    public function test_unauthorized_user_cannot_access_sales_operations()
    {
        // Test without authentication
        $response = $this->postJson(route('produtos.processReturn'), [
            'sale_id' => 1
        ]);
        $response->assertStatus(401);

        $response = $this->postJson(route('produtos.processExchange'), [
            'sale_id' => 1,
            'new_size' => '42'
        ]);
        $response->assertStatus(401);
    }

    public function test_validation_errors_for_invalid_requests()
    {
        // Test return with invalid sale_id
        $response = $this->actingAs($this->user)
            ->postJson(route('produtos.processReturn'), [
                'sale_id' => 'invalid'
            ]);
        $response->assertStatus(422);

        // Test exchange with missing new_size
        $response = $this->actingAs($this->user)
            ->postJson(route('produtos.processExchange'), [
                'sale_id' => 1
            ]);
        $response->assertStatus(422);

        // Test exchange with invalid sale_id
        $response = $this->actingAs($this->user)
            ->postJson(route('produtos.processExchange'), [
                'sale_id' => 'invalid',
                'new_size' => '42'
            ]);
        $response->assertStatus(422);
    }

    public function test_responsive_design_elements()
    {
        $response = $this->actingAs($this->user)
            ->get(route('produtos.exibir', $this->produto));

        $response->assertStatus(200);
        
        // Check for responsive classes
        $response->assertSee('overflow-x-auto'); // Responsive table
        $response->assertSee('min-w-full'); // Full width table
        $response->assertSee('whitespace-nowrap'); // Prevent text wrapping
    }

    public function test_error_handling_for_non_existent_sale()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('produtos.processReturn'), [
                'sale_id' => 99999 // Non-existent ID
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false
        ]);
        $response->assertJsonFragment([
            'message' => 'Venda não encontrada.'
        ]);
    }

    public function test_concurrent_operations_integrity()
    {
        // Get a sale for testing
        $sale = DB::table('vendas_test_city')
            ->where('id_produto', $this->produto->id)
            ->whereNull('data_estorno')
            ->first();

        // Simulate concurrent return attempts
        $response1 = $this->actingAs($this->user)
            ->postJson(route('produtos.processReturn'), [
                'sale_id' => $sale->id
            ]);

        $response2 = $this->actingAs($this->user)
            ->postJson(route('produtos.processReturn'), [
                'sale_id' => $sale->id
            ]);

        // First should succeed
        $response1->assertStatus(200);
        $response1->assertJson(['success' => true]);

        // Second should fail (already returned)
        $response2->assertStatus(200);
        $response2->assertJson(['success' => false]);
        $response2->assertJsonFragment([
            'message' => 'Este item já foi devolvido.'
        ]);
    }

    // Helper methods

    private function createTestTables()
    {
        DB::statement('CREATE TABLE IF NOT EXISTS vendas_test_city (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_vendedor INTEGER,
            id_produto INTEGER,
            data_venda DATE,
            valor_dinheiro DECIMAL(10,2),
            valor_pix DECIMAL(10,2),
            valor_cartao DECIMAL(10,2),
            numeracao VARCHAR(10),
            data_estorno DATE NULL,
            ticket VARCHAR(60) NULL
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS estoque_test_city (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_produto INTEGER,
            numero VARCHAR(10),
            quantidade INTEGER
        )');
    }

    private function insertTestData()
    {
        // Insert test sales
        for ($i = 1; $i <= 5; $i++) {
            DB::table('vendas_test_city')->insert([
                'id_vendedor' => $this->user->id,
                'id_produto' => $this->produto->id,
                'data_venda' => now()->subDays($i)->toDateString(),
                'valor_dinheiro' => 50.00,
                'valor_pix' => 25.00,
                'valor_cartao' => 25.00,
                'numeracao' => '40',
                'data_estorno' => null,
                'ticket' => null
            ]);
        }

        // Insert test stock
        DB::table('estoque_test_city')->insert([
            [
                'id_produto' => $this->produto->id,
                'numero' => '40',
                'quantidade' => 5
            ],
            [
                'id_produto' => $this->produto->id,
                'numero' => '41',
                'quantidade' => 3
            ],
            [
                'id_produto' => $this->produto->id,
                'numero' => '42',
                'quantidade' => 3
            ]
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up test tables
        DB::statement('DROP TABLE IF EXISTS vendas_test_city');
        DB::statement('DROP TABLE IF EXISTS estoque_test_city');
        
        parent::tearDown();
    }
}