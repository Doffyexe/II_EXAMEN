<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * - Cliente 1 —— 0..* Reserva (bidireccional).
 * - Reserva 0..* ——> 1..* Salida, con ItemReserva como clase asociación.
 */
class Reserva extends Model
{
    protected $fillable = ['cliente_id', 'fecha', 'total'];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Salidas reservadas, con los atributos de la clase asociación en el pivot.
     */
    public function salidas(): BelongsToMany
    {
        return $this->belongsToMany(Salida::class, 'item_reservas')
            ->using(ItemReserva::class)
            ->withPivot(['id', 'cantidad_personas', 'precio_unitario'])
            ->withTimestamps();
    }

    /**
     * Acceso directo a los objetos de la clase asociación.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ItemReserva::class);
    }
}
