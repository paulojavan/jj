<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstoqueTabira extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'estoque_tabira';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_produto',
        'numero',
        'quantidade',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // Assuming no timestamps based on table name convention

    /**
     * Get the product that owns the stock.
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto');
    }
}
