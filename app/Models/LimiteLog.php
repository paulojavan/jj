<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LimiteLog extends Model
{
    use HasFactory;

    protected $table = 'limite_logs';

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'acao',
        'valor_anterior',
        'valor_novo',
        'observacoes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(cliente::class, 'cliente_id');
    }

    /**
     * Relacionamento com User
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Scope para filtrar por cliente
     */
    public function scopeByCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    /**
     * Scope para filtrar por ação
     */
    public function scopeByAcao($query, $acao)
    {
        return $query->where('acao', $acao);
    }

    /**
     * Scope para ordenar por data mais recente
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
