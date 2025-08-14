<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desconto extends Model
{
    use HasFactory;

    protected $fillable = [
        'avista',
        'pix',
        'debito',
        'credito',
    ];

    // Adicione aqui quaisquer relacionamentos, escopos ou outros métodos do modelo
}
