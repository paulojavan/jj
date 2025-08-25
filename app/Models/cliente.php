<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
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

    // Removidos os casts problemáticos - tratamento manual no controller

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

    /**
     * Verifica se o cliente é elegível para negativação
     */
    public function isElegivelNegativacao(): bool
    {
        // Verifica se tem parcelas com mais de 60 dias de atraso
        $temParcelasVencidas = $this->parcelas()
            ->where('status', 'aguardando pagamento')
            ->where('data_vencimento', '<', \Carbon\Carbon::now()->subDays(60))
            ->exists();
        
        // Verifica se não tem tickets já negativados
        $temTicketsNegativados = $this->tickets()
            ->where('spc', true)
            ->exists();
        
        // Cliente deve estar ativo
        $clienteAtivo = $this->status !== 'inativo';
        
        return $temParcelasVencidas && !$temTicketsNegativados && $clienteAtivo;
    }

    /**
     * Verifica se o cliente está negativado
     */
    public function isNegativado(): bool
    {
        return $this->status === 'inativo' && $this->obs === 'cliente negativado';
    }

    /**
     * Retorna parcelas em atraso há mais de 60 dias
     */
    public function parcelasElegiveisNegativacao()
    {
        return $this->parcelas()
            ->where('status', 'aguardando pagamento')
            ->where('data_vencimento', '<', \Carbon\Carbon::now()->subDays(60))
            ->orderBy('data_vencimento');
    }

    /**
     * Retorna parcelas negativadas (de tickets com spc = true)
     */
    public function parcelasNegativadas()
    {
        return $this->parcelas()
            ->whereHas('ticket', function ($query) {
                $query->where('spc', true);
            })
            ->where('status', '!=', 'pago')
            ->orderBy('data_vencimento');
    }
}
