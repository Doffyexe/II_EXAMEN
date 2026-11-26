<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Salida 0..* ——> 0..1 Guia: la flecha apunta hacia Guia, así que solo Salida
 * conoce a su guía. Guia no declara relación hacia Salida (navegabilidad).
 */
class Guia extends Model
{
    protected $fillable = ['nombre', 'telefono'];
}
