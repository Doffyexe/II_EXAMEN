<?php

namespace App\Services;

use App\Exceptions\CuposInsuficientesException;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Salida;
use Illuminate\Support\Facades\DB;

/**
 * Clase de control del caso de uso "Registrar reserva".
 */
class ServicioReservas
{
    /**
     * @param  array<int, array{salida_id: int, cantidad_personas: int}>  $items
     *
     * @throws CuposInsuficientesException
     */
    public function reservar(int $clienteId, array $items): Reserva
    {
        // Todo o nada: si algo falla dentro de la transacción, se revierte todo.
        return DB::transaction(function () use ($clienteId, $items) {
            $cliente = Cliente::findOrFail($clienteId);

            // Bloqueo pesimista (SELECT ... FOR UPDATE) de las salidas involucradas.
            // Si otro cliente intenta reservar las mismas salidas, espera a que esta
            // transacción termine y luego lee los cupos ya actualizados.
            // Se ordenan por id para que dos transacciones siempre bloqueen en el
            // mismo orden y no se produzca un interbloqueo (deadlock).
            $salidas = Salida::with('tour')
                ->whereIn('id', array_column($items, 'salida_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 1) Verificar cupos de TODAS las salidas antes de escribir nada.
            foreach ($items as $item) {
                $salida = $salidas[$item['salida_id']];

                if ($salida->cupos_disponibles < $item['cantidad_personas']) {
                    throw new CuposInsuficientesException(
                        $salida->id,
                        $salida->cupos_disponibles,
                        $item['cantidad_personas'],
                    );
                }
            }

            // 2) Registrar la reserva, sus ítems y descontar cupos.
            $reserva = $cliente->reservas()->create([
                'fecha' => now(),
                'total' => 0,
            ]);

            $total = 0;

            foreach ($items as $item) {
                $salida = $salidas[$item['salida_id']];
                $cantidad = (int) $item['cantidad_personas'];

                // Se guarda el precio vigente del tour al momento de reservar.
                $precioUnitario = (float) $salida->tour->precio_por_persona;

                $reserva->items()->create([
                    'salida_id' => $salida->id,
                    'cantidad_personas' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                ]);

                $salida->decrement('cupos_disponibles', $cantidad);

                $total += $precioUnitario * $cantidad;
            }

            $reserva->update(['total' => round($total, 2)]);

            return $reserva->load('items');
        });
    }
}
