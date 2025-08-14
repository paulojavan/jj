<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    use HasFactory;

    protected $table = 'pagamentos';
    protected $primaryKey = 'id_pagamento';

    protected $fillable = [
        'id_cliente',
        'ticket',
        'data',
    ];

    protected $casts = [
        'data' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function parcelas()
    {
        return $this->hasMany(Parcela::class, 'ticket_pagamento', 'ticket');
    }
}