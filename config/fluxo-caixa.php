<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações do Fluxo de Caixa
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para o sistema de fluxo de caixa
    |
    */

    // Limite máximo de dias para consulta (evita consultas muito pesadas)
    'limite_dias_consulta' => env('FLUXO_CAIXA_LIMITE_DIAS', 365),

    // Cache de consultas em minutos
    'cache_consultas_minutos' => env('FLUXO_CAIXA_CACHE_MINUTOS', 5),

    // Limite de registros por página
    'limite_registros_pagina' => env('FLUXO_CAIXA_LIMITE_REGISTROS', 100),

    // Cidades suportadas
    'cidades_suportadas' => [
        'tabira' => [
            'nome' => 'Tabira',
            'tabela_vendas' => 'vendas_tabira',
            'tabela_despesas' => 'despesas_tabira',
        ],
        'princesa' => [
            'nome' => 'Princesa Isabel',
            'tabela_vendas' => 'vendas_princesa',
            'tabela_despesas' => 'despesas_princesa',
        ],
        'tavares' => [
            'nome' => 'Tavares',
            'tabela_vendas' => 'vendas_tavares',
            'tabela_despesas' => 'despesas_tavares',
        ],
        'agua_branca' => [
            'nome' => 'Água Branca',
            'tabela_vendas' => 'vendas_agua_branca',
            'tabela_despesas' => 'despesas_agua_branca',
        ],
    ],

    // Configurações de relatório
    'relatorio' => [
        'mostrar_vendas_zeradas' => env('FLUXO_CAIXA_MOSTRAR_VENDAS_ZERADAS', false),
        'mostrar_recebimentos_zerados' => env('FLUXO_CAIXA_MOSTRAR_RECEBIMENTOS_ZERADOS', false),
        'agrupar_por_data' => env('FLUXO_CAIXA_AGRUPAR_POR_DATA', true),
    ],

    // Configurações de interface
    'interface' => [
        'itens_por_pagina' => env('FLUXO_CAIXA_ITENS_POR_PAGINA', 50),
        'auto_refresh_segundos' => env('FLUXO_CAIXA_AUTO_REFRESH', 300), // 5 minutos
        'mostrar_graficos' => env('FLUXO_CAIXA_MOSTRAR_GRAFICOS', true),
    ],
];