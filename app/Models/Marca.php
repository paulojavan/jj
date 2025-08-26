<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    /** @use HasFactory<\Database\Factories\MarcaFactory> */
    use HasFactory;
    
    protected $primaryKey = 'id_marca';
    
    protected $fillable = [
        'marca',
        'desconto',
    ];


}
