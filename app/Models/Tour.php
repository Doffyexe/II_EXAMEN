<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tour ◆1 —— 0..* Salida (composición, bidireccional). Las salidas se
 * eliminan en cascada junto con su tour (ver migración de salidas).
 */
class Tour extends Model
{
    protected $fillable = ['nombre', 'precio_por_persona', 'duracion_horas'];

    protected function casts(): array
    {
        return [
            'precio_por_persona' => 'decimal:2',
            'duracion_horas' => 'integer',
        ];
    }

    public function salidas(): HasMany
    {
        return $this->hasMany(Salida::class);
    }
}
