<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'login' => 'admin',
            'password' => bcrypt('password'),
            'cidade' => 'tabira',
            'nivel' => 'administrador',
            'status' => 'ativo',
            'image' => 'default.jpg',
            'cadastro_produtos' => true,
            'ajuste_estoque' => true,
            'vendas_crediario' => true,
            'limite' => true,
            'recebimentos' => true,
        ]);

        // Seed default penalty configuration
        $this->call([
            MultaConfiguracaoSeeder::class,
        ]);
    }
}
