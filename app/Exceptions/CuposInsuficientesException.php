<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class CuposInsuficientesException extends RuntimeException
{
    public function __construct(
        public readonly int $salidaId,
        public readonly int $cuposDisponibles,
        public readonly int $cantidadSolicitada,
    ) {
        parent::__construct(
            "La salida {$salidaId} solo tiene {$cuposDisponibles} cupos y se solicitaron {$cantidadSolicitada}."
        );
    }

    /**
     * Laravel llama a este método automáticamente al no capturarse la excepción.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'salida_id' => $this->salidaId,
        ], 422);
    }
}
