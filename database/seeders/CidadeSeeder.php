<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cidade;
use Illuminate\Container\Attributes\DB;

class CidadeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::table('cidades')->insert([
            [
                'cidade' => 'tabira',
                'status' => 'ativa',
            ],
            [
                'cidade' => 'princesa',
                'status' => 'ativa',
            ],
            [
                'cidade' => 'tavares',
                'status' => 'inativa',
            ],
            [
                'cidade' => 'agua_branca',
                'status' => 'inativa',
            ],
        ]);
    }
}
