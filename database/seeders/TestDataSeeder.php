<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Criar cidade de teste
        DB::table('cidades')->insertOrIgnore([
            'id' => 1,
            'cidade' => 'Tabira',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Criar usuário de teste
        DB::table('users')->insertOrIgnore([
            'id' => 1,
            'name' => 'Vendedor Teste',
            'login' => 'vendedor',
            'password' => Hash::make('123456'),
            'cidade' => 1,
            'nivel' => 'usuario',
            'status' => 'ativo',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Criar cliente de teste
        DB::table('clientes')->insertOrIgnore([
            'id' => 1,
            'nome' => 'João Silva',
            'apelido' => 'João',
            'rg' => '1234567',
            'cpf' => '12345678901',
            'telefone' => '87999999999',
            'nome_referencia' => 'Maria Silva',
            'numero_referencia' => '87888888888',
            'parentesco_referencia' => 'Esposa',
            'referencia_comercial1' => 'Loja ABC',
            'telefone_referencia_comercial1' => '87777777777',
            'referencia_comercial2' => 'Empresa XYZ',
            'telefone_referencia_comercial2' => '87666666666',
            'referencia_comercial3' => 'Comércio 123',
            'telefone_referencia_comercial3' => '87555555555',
            'foto' => 'default.jpg',
            'rg_frente' => 'rg_frente.jpg',
            'rg_verso' => 'rg_verso.jpg',
            'cpf_foto' => 'cpf.jpg',
            'rua' => 'Rua das Flores',
            'numero' => 123,
            'bairro' => 'Centro',
            'referencia' => 'Próximo ao mercado',
            'cidade' => 'Tabira',
            'limite' => 1000.00,
            'renda' => '2000.00',
            'status' => 'ativo',
            'atualizacao' => now()->toDateString(),
            'token' => '123456',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Criar marca de teste
        DB::table('marcas')->insertOrIgnore([
            'id' => 1,
            'marca' => 'Nike',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Criar grupo de teste
        DB::table('grupos')->insertOrIgnore([
            'id' => 1,
            'grupo' => 'Tênis',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Criar subgrupo de teste
        DB::table('subgrupos')->insertOrIgnore([
            'id' => 1,
            'subgrupo' => 'Esportivo',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Criar produto de teste
        DB::table('produtos')->insertOrIgnore([
            'id' => 1,
            'produto' => 'Tênis Nike Air Max',
            'marca' => 'Nike',
            'genero' => 'Masculino',
            'grupo' => 'Tênis',
            'subgrupo' => 'Esportivo',
            'codigo' => 'TNK001',
            'quantidade' => '10',
            'num1' => 38,
            'num2' => 44,
            'preco' => 299.90,
            'foto' => 'tenis_nike.jpg',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Criar tabela de estoque para Tabira
        DB::statement('CREATE TABLE IF NOT EXISTS estoque_tabira (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            id_produto int NOT NULL,
            numero int NOT NULL,
            quantidade int NOT NULL,
            PRIMARY KEY (id)
        )');

        // Criar estoque de teste
        for ($numero = 38; $numero <= 44; $numero++) {
            DB::table('estoque_tabira')->insertOrIgnore([
                'id_produto' => 1,
                'numero' => $numero,
                'quantidade' => 5
            ]);
        }

        // Criar tabela de vendas para Tabira
        DB::statement('CREATE TABLE IF NOT EXISTS vendas_tabira (
            id_vendas bigint unsigned NOT NULL AUTO_INCREMENT,
            id_vendedor int NOT NULL,
            id_vendedor_atendente int NULL,
            id_produto int NOT NULL,
            data_venda date NOT NULL,
            hora time NOT NULL,
            data_estorno date NULL,
            valor_dinheiro double(15,2) NOT NULL DEFAULT 0,
            valor_pix double(15,2) NOT NULL DEFAULT 0,
            valor_cartao double(15,2) NOT NULL DEFAULT 0,
            valor_crediario double(15,2) NOT NULL DEFAULT 0,
            preco double(15,2) NOT NULL,
            preco_venda double(15,2) NOT NULL,
            desconto tinyint(1) NOT NULL DEFAULT 0,
            alerta tinyint(1) NOT NULL DEFAULT 0,
            baixa_fiscal tinyint(1) NOT NULL DEFAULT 0,
            numeracao varchar(10) NOT NULL,
            pedido_devolucao date NULL,
            reposicao varchar(255) NOT NULL DEFAULT "",
            bd varchar(255) NOT NULL DEFAULT "",
            ticket varchar(60) NOT NULL DEFAULT "",
            PRIMARY KEY (id_vendas)
        )');

        echo "Dados de teste criados com sucesso!\n";
        echo "Cliente: João Silva (ID: 1, Token: 123456, Limite: R$ 1.000,00)\n";
        echo "Produto: Tênis Nike Air Max (ID: 1, Preço: R$ 299,90)\n";
        echo "Usuário: vendedor / 123456\n";
    }
}