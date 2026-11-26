<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * - Parte de la composición de Tour (exactamente 1 tour).
 * - Navega hacia Guia (0..1).
 * - No navega hacia Reserva: la flecha Reserva ——> Salida va en un solo sentido.
 */
class Salida extends Model
{
    protected $fillable = ['tour_id', 'guia_id', 'fecha', 'cupos_disponibles'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'cupos_disponibles' => 'integer',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function guia(): BelongsTo
    {
        return $this->belongsTo(Guia::class);
    }
}
