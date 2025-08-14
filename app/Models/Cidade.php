<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'cidade', 'id');
    }
}
