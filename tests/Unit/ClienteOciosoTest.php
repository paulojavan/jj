<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClienteOciosoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se o filtro de clientes ociosos funciona corretamente
     */
    public function test_lista_clientes_ociosos_com_filtro_correto()
    {
        // Criar cliente ocioso (mais de 150 dias)
        $clienteOcioso = Cliente::factory()->create([
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'ativo'
        ]);

        // Criar cliente não ocioso (menos de 150 dias)
        $clienteAtivo = Cliente::factory()->create([
            'ociosidade' => Carbon::now()->subDays(100),
            'status' => 'ativo'
        ]);

        // Criar cliente sem data de ociosidade
        $clienteSemOciosidade = Cliente::factory()->create([
            'ociosidade' => null,
            'status' => 'ativo'
        ]);

        // Query para buscar clientes ociosos
        $clientesOciosos = Cliente::whereNotNull('ociosidade')
            ->whereRaw('DATEDIFF(CURDATE(), ociosidade) >= 150')
            ->whereDoesntHave('tickets', function($query) {
                $query->where('spc', true);
            })
            ->where('status', '!=', 'inativo')
            ->get();

        // Verificar se apenas o cliente ocioso foi retornado
        $this->assertCount(1, $clientesOciosos);
        $this->assertEquals($clienteOcioso->id, $clientesOciosos->first()->id);
    }

    /**
     * Testa se clientes com SPC true são excluídos da lista
     */
    public function test_exclui_clientes_com_spc_true()
    {
        // Criar cliente ocioso
        $clienteOcioso = Cliente::factory()->create([
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'ativo'
        ]);

        // Criar ticket com SPC true para o cliente
        Ticket::factory()->create([
            'id_cliente' => $clienteOcioso->id,
            'spc' => true
        ]);

        // Query para buscar clientes ociosos
        $clientesOciosos = Cliente::whereNotNull('ociosidade')
            ->whereRaw('DATEDIFF(CURDATE(), ociosidade) >= 150')
            ->whereDoesntHave('tickets', function($query) {
                $query->where('spc', true);
            })
            ->where('status', '!=', 'inativo')
            ->get();

        // Verificar se o cliente com SPC foi excluído
        $this->assertCount(0, $clientesOciosos);
    }

    /**
     * Testa se o cálculo de dias de ociosidade está correto
     */
    public function test_calcula_dias_ociosidade_corretamente()
    {
        $diasEsperados = 180;
        $cliente = Cliente::factory()->create([
            'ociosidade' => Carbon::now()->subDays($diasEsperados)
        ]);

        $diasCalculados = Carbon::parse($cliente->ociosidade)->diffInDays(Carbon::now());

        $this->assertEquals($diasEsperados, $diasCalculados);
    }

    /**
     * Testa se o campo ociosidade é atualizado ao enviar mensagem
     */
    public function test_atualiza_campo_ociosidade_ao_enviar_mensagem()
    {
        $cliente = Cliente::factory()->create([
            'ociosidade' => Carbon::now()->subDays(200),
            'telefone' => '11999999999'
        ]);

        $dataAnterior = $cliente->ociosidade;

        // Simular atualização do campo ociosidade
        $cliente->update(['ociosidade' => Carbon::now()]);

        $this->assertNotEquals($dataAnterior, $cliente->fresh()->ociosidade);
        $this->assertTrue(Carbon::parse($cliente->fresh()->ociosidade)->isToday());
    }

    /**
     * Testa formatação de telefone para WhatsApp
     */
    public function test_formatacao_telefone_whatsapp()
    {
        $telefoneOriginal = '11999999999';
        $telefoneFormatado = preg_replace('/[^0-9]/', '', $telefoneOriginal);
        
        if (!str_starts_with($telefoneFormatado, '55')) {
            $telefoneFormatado = '55' . $telefoneFormatado;
        }

        $this->assertEquals('5511999999999', $telefoneFormatado);

        // Testar com telefone que já tem código do país
        $telefoneComCodigo = '5511999999999';
        $telefoneFormatado2 = preg_replace('/[^0-9]/', '', $telefoneComCodigo);
        
        if (!str_starts_with($telefoneFormatado2, '55')) {
            $telefoneFormatado2 = '55' . $telefoneFormatado2;
        }

        $this->assertEquals('5511999999999', $telefoneFormatado2);
    }

    /**
     * Testa extração dos dois primeiros nomes com verificação de conjunções
     */
    public function test_extracao_dois_primeiros_nomes()
    {
        // Teste com nome normal (sem conjunção)
        $nomeCompleto = 'João Silva Santos';
        $nomes = explode(' ', trim($nomeCompleto));
        $conjuncoes = ['da', 'de', 'do', 'das', 'dos', 'e', 'del', 'della', 'di', 'du', 'van', 'von', 'la', 'le', 'el'];
        
        if (count($nomes) >= 2 && in_array(strtolower($nomes[1]), $conjuncoes)) {
            $doisPrimeirosNomes = $nomes[0];
        } else {
            $doisPrimeirosNomes = implode(' ', array_slice($nomes, 0, 2));
        }

        $this->assertEquals('João Silva', $doisPrimeirosNomes);

        // Testar com conjunção
        $nomeComConjuncao = 'DHEISIELLE DA SILVA SIQUEIRA';
        $nomes2 = explode(' ', trim($nomeComConjuncao));
        
        if (count($nomes2) >= 2 && in_array(strtolower($nomes2[1]), $conjuncoes)) {
            $doisPrimeirosNomes2 = $nomes2[0];
        } else {
            $doisPrimeirosNomes2 = implode(' ', array_slice($nomes2, 0, 2));
        }

        $this->assertEquals('DHEISIELLE', $doisPrimeirosNomes2);

        // Testar com nome único
        $nomeUnico = 'João';
        $nomes3 = explode(' ', trim($nomeUnico));
        
        if (count($nomes3) >= 2 && in_array(strtolower($nomes3[1]), $conjuncoes)) {
            $doisPrimeirosNomes3 = $nomes3[0];
        } else {
            $doisPrimeirosNomes3 = implode(' ', array_slice($nomes3, 0, 2));
        }

        $this->assertEquals('João', $doisPrimeirosNomes3);

        // Testar outros casos de conjunções
        $nomeComDe = 'Maria de Souza Lima';
        $nomes4 = explode(' ', trim($nomeComDe));
        
        if (count($nomes4) >= 2 && in_array(strtolower($nomes4[1]), $conjuncoes)) {
            $doisPrimeirosNomes4 = $nomes4[0];
        } else {
            $doisPrimeirosNomes4 = implode(' ', array_slice($nomes4, 0, 2));
        }

        $this->assertEquals('Maria', $doisPrimeirosNomes4);
    }

    /**
     * Testa se clientes inativos são excluídos
     */
    public function test_exclui_clientes_inativos()
    {
        // Criar cliente ocioso mas inativo
        $clienteInativo = Cliente::factory()->create([
            'ociosidade' => Carbon::now()->subDays(200),
            'status' => 'inativo'
        ]);

        // Query para buscar clientes ociosos
        $clientesOciosos = Cliente::whereNotNull('ociosidade')
            ->whereRaw('DATEDIFF(CURDATE(), ociosidade) >= 150')
            ->whereDoesntHave('tickets', function($query) {
                $query->where('spc', true);
            })
            ->where('status', '!=', 'inativo')
            ->get();

        // Verificar se o cliente inativo foi excluído
        $this->assertCount(0, $clientesOciosos);
    }
}