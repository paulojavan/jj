<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FluxoCaixaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FluxoCaixaManutencao extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fluxo-caixa:manutencao 
                            {--limpar-cache : Limpa o cache do fluxo de caixa}
                            {--verificar-tabelas : Verifica se todas as tabelas necessárias existem}
                            {--estatisticas : Mostra estatísticas do sistema}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comandos de manutenção para o sistema de fluxo de caixa';

    protected $fluxoCaixaService;

    public function __construct(FluxoCaixaService $fluxoCaixaService)
    {
        parent::__construct();
        $this->fluxoCaixaService = $fluxoCaixaService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Iniciando manutenção do Fluxo de Caixa...');

        if ($this->option('limpar-cache')) {
            $this->limparCache();
        }

        if ($this->option('verificar-tabelas')) {
            $this->verificarTabelas();
        }

        if ($this->option('estatisticas')) {
            $this->mostrarEstatisticas();
        }

        if (!$this->option('limpar-cache') && !$this->option('verificar-tabelas') && !$this->option('estatisticas')) {
            $this->info('Executando manutenção completa...');
            $this->limparCache();
            $this->verificarTabelas();
            $this->mostrarEstatisticas();
        }

        $this->info('✅ Manutenção concluída!');
    }

    private function limparCache()
    {
        $this->info('🧹 Limpando cache do fluxo de caixa...');
        
        try {
            $this->fluxoCaixaService->limparCache();
            $this->info('✅ Cache limpo com sucesso!');
        } catch (\Exception $e) {
            $this->error('❌ Erro ao limpar cache: ' . $e->getMessage());
        }
    }

    private function verificarTabelas()
    {
        $this->info('🔍 Verificando tabelas do sistema...');
        
        $cidades = config('fluxo-caixa.cidades_suportadas', []);
        $tabelasProblema = [];

        foreach ($cidades as $codigo => $config) {
            $this->line("Verificando cidade: {$config['nome']}");
            
            // Verificar tabela de vendas
            if (!$this->tabelaExiste($config['tabela_vendas'])) {
                $tabelasProblema[] = $config['tabela_vendas'];
                $this->warn("  ⚠️  Tabela de vendas não encontrada: {$config['tabela_vendas']}");
            } else {
                $this->info("  ✅ Tabela de vendas OK: {$config['tabela_vendas']}");
            }

            // Verificar tabela de despesas
            if (!$this->tabelaExiste($config['tabela_despesas'])) {
                $tabelasProblema[] = $config['tabela_despesas'];
                $this->warn("  ⚠️  Tabela de despesas não encontrada: {$config['tabela_despesas']}");
            } else {
                $this->info("  ✅ Tabela de despesas OK: {$config['tabela_despesas']}");
            }
        }

        // Verificar tabelas principais
        $tabelasPrincipais = ['users', 'parcelas', 'cidades'];
        foreach ($tabelasPrincipais as $tabela) {
            if (!$this->tabelaExiste($tabela)) {
                $tabelasProblema[] = $tabela;
                $this->error("  ❌ Tabela principal não encontrada: {$tabela}");
            } else {
                $this->info("  ✅ Tabela principal OK: {$tabela}");
            }
        }

        if (empty($tabelasProblema)) {
            $this->info('✅ Todas as tabelas estão OK!');
        } else {
            $this->error('❌ Problemas encontrados em ' . count($tabelasProblema) . ' tabelas.');
        }
    }

    private function mostrarEstatisticas()
    {
        $this->info('📊 Coletando estatísticas do sistema...');
        
        try {
            // Estatísticas básicas
            $totalUsuarios = DB::table('users')->where('status', 'ativo')->count();
            $totalCidades = DB::table('cidades')->count();
            $totalParcelas = DB::table('parcelas')->count();

            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Usuários Ativos', $totalUsuarios],
                    ['Cidades Cadastradas', $totalCidades],
                    ['Total de Parcelas', number_format($totalParcelas, 0, ',', '.')],
                ]
            );

            // Estatísticas por cidade
            $this->info('📍 Estatísticas por cidade:');
            $cidades = config('fluxo-caixa.cidades_suportadas', []);
            
            foreach ($cidades as $codigo => $config) {
                if ($this->tabelaExiste($config['tabela_vendas'])) {
                    $totalVendas = DB::table($config['tabela_vendas'])->count();
                    $this->line("  {$config['nome']}: {$totalVendas} vendas registradas");
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ Erro ao coletar estatísticas: ' . $e->getMessage());
        }
    }

    private function tabelaExiste($nomeTabela)
    {
        try {
            DB::select("SELECT 1 FROM {$nomeTabela} LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
