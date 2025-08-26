<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;
    
    protected $table = 'tickets';
    protected $primaryKey = 'id_ticket';
    public $timestamps = false;
    
    protected $fillable = [
        'id_cliente',
        'ticket',
        'data',
        'valor',
        'entrada',
        'parcelas',
        'spc'
    ];
    
    protected $casts = [
        'data' => 'datetime',
        'valor' => 'decimal:2',
        'entrada' => 'decimal:2',
        'parcelas' => 'integer',
        'id_cliente' => 'integer'
    ];

    /**
     * Relacionamento com Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(cliente::class, 'id_cliente', 'id');
    }

    /**
     * Relacionamento com Parcelas
     */
    public function parcelasRelacao(): HasMany
    {
        return $this->hasMany(Parcela::class, 'ticket', 'ticket');
    }

    /**
     * Relacionamento com Parcelas (alias para evitar conflito com campo parcelas)
     */
    public function getParcelasListaAttribute()
    {
        return $this->parcelasRelacao;
    }

    /**
     * Verifica se a compra foi devolvida
     */
    public function isDevolvida(): bool
    {
        return $this->parcelasRelacao()->where('status', 'devolucao')->exists();
    }

    /**
     * Verifica se a compra pode ser devolvida
     */
    public function canBeReturned(): bool
    {
        // Não pode devolver se já foi devolvida
        if ($this->isDevolvida()) {
            return false;
        }

        // Não pode devolver se alguma parcela foi paga (status diferente de 'aguardando pagamento')
        $parcelasPagas = $this->parcelasRelacao()
            ->where('status', '!=', 'aguardando pagamento')
            ->where('status', '!=', 'devolucao') // Excluir parcelas já devolvidas
            ->count();

        return $parcelasPagas === 0;
    }

    /**
     * Calcula o valor financiado (valor - entrada)
     */
    public function getValorFinanciadoAttribute(): float
    {
        return $this->valor - $this->entrada;
    }

    /**
     * Formata o valor para exibição
     */
    public function getValorFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    /**
     * Formata a entrada para exibição
     */
    public function getEntradaFormatadaAttribute(): string
    {
        return 'R$ ' . number_format($this->entrada, 2, ',', '.');
    }

    /**
     * Formata a data para exibição
     */
    public function getDataFormatadaAttribute(): string
    {
        return $this->data->format('d/m/Y H:i');
    }
}