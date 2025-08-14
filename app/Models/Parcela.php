<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Parcela extends Model
{
    use HasFactory;

    protected $table = 'parcelas';
    protected $primaryKey = 'id_parcelas';
    public $timestamps = false;

    protected $fillable = [
        'ticket',
        'id_cliente',
        'id_autorizado',
        'numero',
        'data_vencimento',
        'data_pagamento',
        'hora',
        'valor_parcela',
        'valor_pago',
        'dinheiro',
        'pix',
        'cartao',
        'metodo',
        'id_vendedor',
        'status',
        'bd',
        'ticket_pagamento',
        'lembrete'
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'valor_parcela' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'dinheiro' => 'decimal:2',
        'pix' => 'decimal:2',
        'cartao' => 'decimal:2',
        'id_cliente' => 'integer',
        'id_autorizado' => 'integer',
        'id_vendedor' => 'integer'
    ];

    /**
     * Relacionamento com Ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket', 'ticket');
    }

    /**
     * Relacionamento com Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(cliente::class, 'id_cliente', 'id');
    }

    /**
     * Relacionamento com Autorizado
     */
    public function autorizado(): BelongsTo
    {
        return $this->belongsTo(Autorizado::class, 'id_autorizado', 'id');
    }

    /**
     * Verifica se a parcela está vencida
     */
    public function isVencida(): bool
    {
        if ($this->isPaga() || $this->status === 'devolucao') {
            return false;
        }

        return $this->data_vencimento < Carbon::today();
    }

    /**
     * Verifica se a parcela foi paga
     */
    public function isPaga(): bool
    {
        return !is_null($this->data_pagamento) &&
               in_array(strtolower($this->status), ['pago', 'paga', 'quitado', 'quitada']);
    }

    /**
     * Verifica se é devolução
     */
    public function isDevolucao(): bool
    {
        return strtolower($this->status) === 'devolucao';
    }

    /**
     * Retorna a cor do status baseada nas regras de negócio
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->isDevolucao()) {
            return 'text-yellow-600 bg-yellow-50'; // Amarelo
        }

        if ($this->isPaga()) {
            return 'text-green-600 bg-green-50'; // Verde
        }

        if ($this->isVencida()) {
            return 'text-red-600 bg-red-50'; // Vermelho
        }

        return 'text-gray-600 bg-gray-50'; // Preto/Cinza
    }

    /**
     * Retorna o texto do status formatado
     */
    public function getStatusTextoAttribute(): string
    {
        if ($this->isDevolucao()) {
            return 'Devolução';
        }

        if ($this->isPaga()) {
            return 'Pago';
        }

        if ($this->isVencida()) {
            return 'Vencido';
        }

        return 'Em aberto';
    }

    /**
     * Formata o valor da parcela para exibição
     */
    public function getValorFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_parcela, 2, ',', '.');
    }

    /**
     * Formata a data de vencimento para exibição
     */
    public function getVencimentoFormatadoAttribute(): string
    {
        return $this->data_vencimento->format('d/m/Y');
    }

    /**
     * Formata a data de pagamento para exibição
     */
    public function getPagamentoFormatadoAttribute(): string
    {
        return $this->data_pagamento ? $this->data_pagamento->format('d/m/Y') : '-';
    }

    /**
     * Calcula quantos dias de atraso ou antecipação no pagamento
     */
    public function getDiasAtrasoOuAntecipacaoAttribute(): ?int
    {
        if (!$this->isPaga() || !$this->data_pagamento) {
            return null;
        }

        return $this->data_pagamento->diffInDays($this->data_vencimento, false);
    }
}
