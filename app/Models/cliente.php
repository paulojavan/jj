<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cliente extends Model
{
    /** @use HasFactory<\Database\Factories\ClienteFactory> */
    use HasFactory;
    protected $fillable = [
        'nome',
        'apelido',
        'rg',
        'cpf',
        'mae',
        'pai',
        'telefone',
        'nascimento',
        'nome_referencia',
        'numero_referencia',
        'parentesco_referencia',
        'referencia_comercial1',
        'telefone_referencia_comercial1',
        'referencia_comercial2',
        'telefone_referencia_comercial2',
        'referencia_comercial3',
        'telefone_referencia_comercial3',
        'foto',
        'rg_frente',
        'rg_verso',
        'cpf_foto',
        'rua',
        'numero',
        'bairro',
        'referencia',
        'cidade',
        'limite',
        'renda',
        'pasta',
        'obs',
        'atualizacao',
        'status',
        'token',
		'ociosidade',
		'cobranca',
    ];

    public function autorizados()
    {
        return $this->hasMany(Autorizado::class, 'idCliente');
    }

    /**
     * Relacionamento com Tickets (compras)
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_cliente', 'id');
    }

    /**
     * Relacionamento com Parcelas
     */
    public function parcelas()
    {
        return $this->hasMany(Parcela::class, 'id_cliente', 'id');
    }
}
