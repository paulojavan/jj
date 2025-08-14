<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autorizado extends Model
{
    /** @use HasFactory<\Database\Factories\AutorizadoFactory> */
    use HasFactory;

    protected $fillable = [
        'idCliente',
        'nome',
        'rg',
        'cpf',
        'foto',
        'rg_frente',
        'rg_verso',
        'cpf_foto',
        'pasta',
    ];

    public function cliente()
    {
        return $this->belongsTo(cliente::class, 'idCliente');
    }
}
