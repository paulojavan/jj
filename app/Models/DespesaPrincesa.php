<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DespesaPrincesa extends Model
{
    use HasFactory;

    protected $table = 'despesas_princesa';
    protected $primaryKey = 'id_despesas';

    protected $fillable = [
        'data',
        'tipo',
        'empresa',
        'numero',
        'valor',
        'status',
        'pagamento',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}