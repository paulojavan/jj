<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultaConfiguracao extends Model
{
    use HasFactory;

    protected $table = 'multa_configuracoes';

    protected $fillable = [
        'taxa_multa',
        'taxa_juros',
        'dias_cobranca',
        'dias_carencia',
    ];

    protected $casts = [
        'taxa_multa' => 'decimal:2',
        'taxa_juros' => 'decimal:2',
        'dias_cobranca' => 'integer',
        'dias_carencia' => 'integer',
    ];

    /**
     * Obtém ou cria a configuração de multa (singleton pattern)
     */
    public static function getConfiguracao()
    {
        $configuracao = self::first();
        
        if (!$configuracao) {
            $configuracao = self::create([
                'taxa_multa' => 2.00,
                'taxa_juros' => 1.00,
                'dias_cobranca' => 30,
                'dias_carencia' => 5,
            ]);
        }
        
        return $configuracao;
    }

    /**
     * Regras de validação para o modelo
     */
    public static function validationRules()
    {
        return [
            'taxa_multa' => 'required|numeric|min:0|max:100',
            'taxa_juros' => 'required|numeric|min:0|max:100',
            'dias_cobranca' => 'required|integer|min:1|max:365',
            'dias_carencia' => 'required|integer|min:0|max:90|lte:dias_cobranca',
        ];
    }

    /**
     * Mensagens de validação personalizadas
     */
    public static function validationMessages()
    {
        return [
            'taxa_multa.required' => 'A taxa de multa é obrigatória.',
            'taxa_multa.numeric' => 'A taxa de multa deve ser um número.',
            'taxa_multa.min' => 'A taxa de multa deve ser no mínimo 0%.',
            'taxa_multa.max' => 'A taxa de multa deve ser no máximo 100%.',
            
            'taxa_juros.required' => 'A taxa de juros é obrigatória.',
            'taxa_juros.numeric' => 'A taxa de juros deve ser um número.',
            'taxa_juros.min' => 'A taxa de juros deve ser no mínimo 0%.',
            'taxa_juros.max' => 'A taxa de juros deve ser no máximo 100%.',
            
            'dias_cobranca.required' => 'Os dias para cobrança são obrigatórios.',
            'dias_cobranca.integer' => 'Os dias para cobrança devem ser um número inteiro.',
            'dias_cobranca.min' => 'Os dias para cobrança devem ser no mínimo 1.',
            'dias_cobranca.max' => 'Os dias para cobrança devem ser no máximo 365.',
            
            'dias_carencia.required' => 'Os dias de carência são obrigatórios.',
            'dias_carencia.integer' => 'Os dias de carência devem ser um número inteiro.',
            'dias_carencia.min' => 'Os dias de carência devem ser no mínimo 0.',
            'dias_carencia.max' => 'Os dias de carência devem ser no máximo 90.',
            'dias_carencia.lte' => 'Os dias de carência não podem ser maiores que os dias para cobrança.',
        ];
    }
}
