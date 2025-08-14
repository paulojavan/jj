<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subgrupo extends Model
{
    /** @use HasFactory<\Database\Factories\SubgrupoFactory> */
    use HasFactory;
    protected $fillable = [
        'subgrupo',
    ];
}
