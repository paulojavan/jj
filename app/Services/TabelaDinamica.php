<?php

namespace App\Services;

class TabelaDinamica
{
    /**
     * Retorna o nome da tabela de vendas para uma cidade
     */
    public static function vendas($cidade): string
    {
        $cidadeNormalizada = strtolower(str_replace(' ', '_', trim($cidade)));
        return "vendas_{$cidadeNormalizada}";
    }
    
    /**
     * Retorna o nome da tabela de despesas para uma cidade
     */
    public static function despesas($cidade): string
    {
        $cidadeNormalizada = strtolower(str_replace(' ', '_', trim($cidade)));
        return "despesas_{$cidadeNormalizada}";
    }

    /**
     * Retorna o nome da tabela de estoque para uma cidade
     */
    public static function estoque($cidade): string
    {
        $cidadeNormalizada = strtolower(str_replace(' ', '_', trim($cidade)));
        return "estoque_{$cidadeNormalizada}";
    }

    /**
     * Mapeia nomes de cidades para seus equivalentes nas tabelas
     */
    public static function mapearNomeCidade($cidade): string
    {
        $mapeamento = [
            'princesa isabel' => 'princesa',
            'tabira' => 'tabira'
        ];

        $cidadeNormalizada = strtolower(trim($cidade));
        
        return $mapeamento[$cidadeNormalizada] ?? $cidadeNormalizada;
    }

    /**
     * Retorna lista de cidades suportadas
     */
    public static function cidadesSuportadas(): array
    {
        return ['tabira', 'princesa', 'princesa_isabel'];
    }

    /**
     * Verifica se uma cidade é suportada
     */
    public static function cidadeSuportada($cidade): bool
    {
        $cidadeNormalizada = strtolower(str_replace(' ', '_', trim($cidade)));
        return in_array($cidadeNormalizada, self::cidadesSuportadas()) ||
               in_array(self::mapearNomeCidade($cidade), self::cidadesSuportadas());
    }
}