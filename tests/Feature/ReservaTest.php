<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Salida;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaTest extends TestCase
{
    use RefreshDatabase;

    private Cliente $cliente;

    private Salida $salidaVolcan;

    private Salida $salidaCanopy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cliente = Cliente::create(['nombre' => 'Ana Pérez', 'correo' => 'ana@example.com']);

        $volcan = Tour::create(['nombre' => 'Volcán', 'precio_por_persona' => 45.00, 'duracion_horas' => 4]);
        $canopy = Tour::create(['nombre' => 'Canopy', 'precio_por_persona' => 60.00, 'duracion_horas' => 2]);

        $this->salidaVolcan = $volcan->salidas()->create(['fecha' => '2026-12-10', 'cupos_disponibles' => 10]);
        $this->salidaCanopy = $canopy->salidas()->create(['fecha' => '2026-12-11', 'cupos_disponibles' => 5]);
    }

    public function test_una_reserva_valida_con_dos_salidas_se_registra_y_descuenta_los_cupos(): void
    {
        $respuesta = $this->postJson('/reservas', [
            'cliente_id' => $this->cliente->id,
            'items' => [
                ['salida_id' => $this->salidaVolcan->id, 'cantidad_personas' => 3],
                ['salida_id' => $this->salidaCanopy->id, 'cantidad_personas' => 2],
            ],
        ]);

        // 3 × 45 + 2 × 60 = 255
        $respuesta->assertCreated()
            ->assertJsonPath('cliente_id', $this->cliente->id)
            ->assertJsonPath('total', '255.00')
            ->assertJsonCount(2, 'items');

        $this->assertDatabaseCount('reservas', 1);
        $this->assertDatabaseCount('item_reservas', 2);
        $this->assertDatabaseHas('item_reservas', [
            'salida_id' => $this->salidaVolcan->id,
            'cantidad_personas' => 3,
            'precio_unitario' => 45.00,
        ]);
        $this->assertDatabaseHas('item_reservas', [
            'salida_id' => $this->salidaCanopy->id,
            'cantidad_personas' => 2,
            'precio_unitario' => 60.00,
        ]);

        $this->assertSame(7, $this->salidaVolcan->fresh()->cupos_disponibles);
        $this->assertSame(3, $this->salidaCanopy->fresh()->cupos_disponibles);
    }

    public function test_si_la_segunda_salida_no_tiene_cupos_no_se_registra_nada(): void
    {
        $respuesta = $this->postJson('/reservas', [
            'cliente_id' => $this->cliente->id,
            'items' => [
                ['salida_id' => $this->salidaVolcan->id, 'cantidad_personas' => 3],
                ['salida_id' => $this->salidaCanopy->id, 'cantidad_personas' => 6], // solo hay 5
            ],
        ]);

        $respuesta->assertUnprocessable()
            ->assertJsonPath('salida_id', $this->salidaCanopy->id);

        $this->assertDatabaseCount('reservas', 0);
        $this->assertDatabaseCount('item_reservas', 0);

        $this->assertSame(10, $this->salidaVolcan->fresh()->cupos_disponibles);
        $this->assertSame(5, $this->salidaCanopy->fresh()->cupos_disponibles);
    }
}
