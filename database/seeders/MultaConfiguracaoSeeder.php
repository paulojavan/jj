<?php

namespace Database\Seeders;

use App\Models\MultaConfiguracao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MultaConfiguracaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica se já existe uma configuração para evitar duplicatas
        if (MultaConfiguracao::count() == 0) {
            MultaConfiguracao::create([
                'taxa_multa' => 2.00,      // 2% de multa
                'taxa_juros' => 1.00,      // 1% de juros ao mês
                'dias_cobranca' => 30,     // 30 dias para iniciar cobrança
                'dias_carencia' => 5,      // 5 dias de carência
            ]);
        }
    }
}
