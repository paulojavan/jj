<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Produto;
use App\Models\User;
use App\Http\Controllers\ProdutoController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProdutoSalesHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;
    protected $user;
    protected $produto;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new ProdutoController();
        
        // Create test user with city
        $this->user = User::factory()->create([
            'cidade' => 1,
            'name' => 'Test User'
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
    }

    public function test_get_sales_history_with_valid_product()
    {
        Auth::login($this->user);
        
        // Create test sales table
        $this->createTestSalesTable();
        
        // Insert test sales data
        $this->insertTestSalesData();
        
        $salesHistory = $this->controller->getSalesHistory($this->produto->id);
        
        $this->assertNotEmpty($salesHistory);
        $this->assertLessThanOrEqual(30, $salesHistory->count());
        
        // Check if sales are ordered by date descending
        $dates = $salesHistory->pluck('data_venda')->toArray();
        $sortedDates = collect($dates)->sortDesc()->toArray();
        $this->assertEquals($sortedDates, $dates);
    }

    public function test_get_sales_history_with_no_sales()
    {
        Auth::login($this->user);
        
        // Create test sales table but don't insert any data
        $this->createTestSalesTable();
        
        $salesHistory = $this->controller->getSalesHistory($this->produto->id);
        
        $this->assertEmpty($salesHistory);
    }

    public function test_get_sales_history_without_user()
    {
        // Don't login user
        $salesHistory = $this->controller->getSalesHistory($this->produto->id);
        
        $this->assertEmpty($salesHistory);
    }

    public function test_get_available_sizes_with_stock()
    {
        Auth::login($this->user);
        
        // Create test stock table
        $this->createTestStockTable();
        
        // Insert test stock data
        $this->insertTestStockData();
        
        $availableSizes = $this->controller->getAvailableSizes($this->produto->id);
        
        $this->assertNotEmpty($availableSizes);
        
        // Check if only sizes with stock > 0 are returned
        foreach ($availableSizes as $size) {
            $this->assertGreaterThan(0, $size->quantidade);
        }
        
        // Check if sizes are ordered by number
        $numbers = $availableSizes->pluck('numero')->toArray();
        $sortedNumbers = collect($numbers)->sort()->values()->toArray();
        $this->assertEquals($sortedNumbers, $numbers);
    }

    public function test_get_available_sizes_without_stock()
    {
        Auth::login($this->user);
        
        // Create test stock table but don't insert any data
        $this->createTestStockTable();
        
        $availableSizes = $this->controller->getAvailableSizes($this->produto->id);
        
        $this->assertEmpty($availableSizes);
    }

    public function test_process_return_success()
    {
        Auth::login($this->user);
        
        // Create test tables
        $this->createTestSalesTable();
        $this->createTestStockTable();
        
        // Insert test data
        $saleId = $this->insertTestSaleForReturn();
        $this->insertTestStockData();
        
        $request = new Request([
            'sale_id' => $saleId
        ]);
        
        $response = $this->controller->processReturn($request);
        $responseData = $response->getData(true);
        
        $this->assertTrue($responseData['success']);
        $this->assertStringContainsString('sucesso', $responseData['message']);
        
        // Check if sale was marked as returned
        $sale = DB::table('vendas_test_city')->where('id', $saleId)->first();
        $this->assertNotNull($sale->data_estorno);
        
        // Check if inventory was updated
        $stock = DB::table('estoque_test_city')
            ->where('id_produto', $this->produto->id)
            ->where('numero', '40')
            ->first();
        $this->assertEquals(6, $stock->quantidade); // Original 5 + 1 returned
        
        // Check if main product quantity was updated
        $produto = DB::table('produtos')->where('id', $this->produto->id)->first();
        $this->assertEquals($this->produto->quantidade + 1, $produto->quantidade);
    }

    public function test_process_return_already_returned()
    {
        Auth::login($this->user);
        
        // Create test tables
        $this->createTestSalesTable();
        $this->createTestStockTable();
        
        // Insert test data with already returned sale
        $saleId = $this->insertTestSaleForReturn(true); // Already returned
        
        $request = new Request([
            'sale_id' => $saleId
        ]);
        
        $response = $this->controller->processReturn($request);
        $responseData = $response->getData(true);
        
        $this->assertFalse($responseData['success']);
        $this->assertStringContainsString('já foi devolvido', $responseData['message']);
    }

    public function test_process_exchange_success()
    {
        Auth::login($this->user);
        
        // Create test tables
        $this->createTestSalesTable();
        $this->createTestStockTable();
        
        // Insert test data
        $saleId = $this->insertTestSaleForExchange();
        $this->insertTestStockDataForExchange();
        
        $request = new Request([
            'sale_id' => $saleId,
            'new_size' => '42'
        ]);
        
        $response = $this->controller->processExchange($request);
        $responseData = $response->getData(true);
        
        $this->assertTrue($responseData['success']);
        $this->assertStringContainsString('sucesso', $responseData['message']);
        
        // Check if sale numeracao was updated
        $sale = DB::table('vendas_test_city')->where('id', $saleId)->first();
        $this->assertEquals('42', $sale->numeracao);
        
        // Check if original size inventory was incremented
        $originalStock = DB::table('estoque_test_city')
            ->where('id_produto', $this->produto->id)
            ->where('numero', '40')
            ->first();
        $this->assertEquals(6, $originalStock->quantidade); // Original 5 + 1
        
        // Check if new size inventory was decremented
        $newStock = DB::table('estoque_test_city')
            ->where('id_produto', $this->produto->id)
            ->where('numero', '42')
            ->first();
        $this->assertEquals(2, $newStock->quantidade); // Original 3 - 1
    }

    public function test_process_exchange_insufficient_stock()
    {
        Auth::login($this->user);
        
        // Create test tables
        $this->createTestSalesTable();
        $this->createTestStockTable();
        
        // Insert test data
        $saleId = $this->insertTestSaleForExchange();
        $this->insertTestStockDataForExchange();
        
        $request = new Request([
            'sale_id' => $saleId,
            'new_size' => '44' // Size with no stock
        ]);
        
        $response = $this->controller->processExchange($request);
        $responseData = $response->getData(true);
        
        $this->assertFalse($responseData['success']);
        $this->assertStringContainsString('sem estoque', $responseData['message']);
    }

    // Helper methods for creating test data

    private function createTestSalesTable()
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
    }

    private function createTestStockTable()
    {
        DB::statement('CREATE TABLE IF NOT EXISTS estoque_test_city (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_produto INTEGER,
            numero VARCHAR(10),
            quantidade INTEGER
        )');
    }

    private function insertTestSalesData()
    {
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
    }

    private function insertTestStockData()
    {
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
                'quantidade' => 2
            ]
        ]);
    }

    private function insertTestSaleForReturn($alreadyReturned = false)
    {
        return DB::table('vendas_test_city')->insertGetId([
            'id_vendedor' => $this->user->id,
            'id_produto' => $this->produto->id,
            'data_venda' => now()->subDays(1)->toDateString(),
            'valor_dinheiro' => 100.00,
            'valor_pix' => 0.00,
            'valor_cartao' => 0.00,
            'numeracao' => '40',
            'data_estorno' => $alreadyReturned ? now()->toDateString() : null,
            'ticket' => null
        ]);
    }

    private function insertTestSaleForExchange()
    {
        return DB::table('vendas_test_city')->insertGetId([
            'id_vendedor' => $this->user->id,
            'id_produto' => $this->produto->id,
            'data_venda' => now()->subDays(1)->toDateString(),
            'valor_dinheiro' => 100.00,
            'valor_pix' => 0.00,
            'valor_cartao' => 0.00,
            'numeracao' => '40',
            'data_estorno' => null,
            'ticket' => null
        ]);
    }

    private function insertTestStockDataForExchange()
    {
        DB::table('estoque_test_city')->insert([
            [
                'id_produto' => $this->produto->id,
                'numero' => '40',
                'quantidade' => 5
            ],
            [
                'id_produto' => $this->produto->id,
                'numero' => '42',
                'quantidade' => 3
            ],
            [
                'id_produto' => $this->produto->id,
                'numero' => '44',
                'quantidade' => 0 // No stock
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