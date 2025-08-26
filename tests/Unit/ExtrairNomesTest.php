<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExtrairNomesTest extends TestCase
{
    /**
     * Testa extração dos primeiros nomes com verificação de conjunções
     */
    public function test_extracao_primeiros_nomes_com_conjuncoes()
    {
        // Teste com nome normal (sem conjunção)
        $resultado1 = $this->extrairPrimeirosNomes('João Silva Santos');
        $this->assertEquals('João Silva', $resultado1);

        // Teste com conjunção "DA"
        $resultado2 = $this->extrairPrimeirosNomes('DHEISIELLE DA SILVA SIQUEIRA');
        $this->assertEquals('DHEISIELLE', $resultado2);

        // Teste com conjunção "DE"
        $resultado3 = $this->extrairPrimeirosNomes('Maria de Souza Lima');
        $this->assertEquals('Maria', $resultado3);

        // Teste com conjunção "DO"
        $resultado4 = $this->extrairPrimeirosNomes('José do Carmo Silva');
        $this->assertEquals('José', $resultado4);

        // Teste com nome único
        $resultado5 = $this->extrairPrimeirosNomes('João');
        $this->assertEquals('João', $resultado5);

        // Teste com conjunção "DAS"
        $resultado6 = $this->extrairPrimeirosNomes('Ana das Neves Santos');
        $this->assertEquals('Ana', $resultado6);

        // Teste com conjunção "DOS"
        $resultado7 = $this->extrairPrimeirosNomes('Pedro dos Santos Silva');
        $this->assertEquals('Pedro', $resultado7);

        // Teste com conjunção "E"
        $resultado8 = $this->extrairPrimeirosNomes('Carlos e Silva Oliveira');
        $this->assertEquals('Carlos', $resultado8);
    }

    /**
     * Método auxiliar para extrair primeiros nomes
     */
    private function extrairPrimeirosNomes($nomeCompleto)
    {
        $nomes = explode(' ', trim($nomeCompleto));
        $conjuncoes = ['da', 'de', 'do', 'das', 'dos', 'e', 'del', 'della', 'di', 'du', 'van', 'von', 'la', 'le', 'el'];
        
        if (count($nomes) >= 2 && in_array(strtolower($nomes[1]), $conjuncoes)) {
            // Se o segundo nome é uma conjunção, usar apenas o primeiro
            return $nomes[0];
        } else {
            // Caso contrário, usar os dois primeiros nomes
            return implode(' ', array_slice($nomes, 0, 2));
        }
    }
}