<?php

namespace App\Http\Controllers;

use App\Services\ServicioReservas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    /**
     * Valida la entrada y delega el caso de uso a ServicioReservas.
     */
    public function store(Request $request, ServicioReservas $servicio): JsonResponse
    {
        $datos = $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.salida_id' => ['required', 'integer', 'distinct', 'exists:salidas,id'],
            'items.*.cantidad_personas' => ['required', 'integer', 'min:1'],
        ]);

        $reserva = $servicio->reservar($datos['cliente_id'], $datos['items']);

        return response()->json($reserva, 201);
    }
}
