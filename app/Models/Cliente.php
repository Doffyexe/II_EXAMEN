<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cliente 1 —— 0..* Reserva (bidireccional).
 */
class Cliente extends Model
{
    protected $fillable = ['nombre', 'correo'];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }
}
