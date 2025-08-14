<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    /** @use HasFactory<\Database\Factories\ProdutoFactory> */
    use HasFactory;
    protected $fillable = ['produto', 'marca', 'genero', 'grupo', 'subgrupo', 'codigo', 'quantidade', 'num1', 'num2', 'preco', 'foto'];
}
