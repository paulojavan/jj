<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DespesaTabiraFixa extends Model
{
    use HasFactory;

    protected $table = 'despesas_tabira_fixa';
    protected $primaryKey = 'id_despesas';

    protected $fillable = [
        'dia',
        'tipo',
        'empresa',
        'numero',
        'valor',
        'data',
    ];

    protected $casts = [
        'dia' => 'integer',
        'valor' => 'decimal:2',
        'data' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}