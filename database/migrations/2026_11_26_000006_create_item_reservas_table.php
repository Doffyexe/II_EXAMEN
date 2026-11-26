<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clase asociación entre Reserva (0..*) y Salida (1..*).
     */
    public function up(): void
    {
        Schema::create('item_reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('salida_id')->constrained('salidas')->restrictOnDelete();
            $table->unsignedInteger('cantidad_personas');
            $table->decimal('precio_unitario', 10, 2);
            $table->timestamps();

            // Una clase asociación tiene a lo sumo un enlace por par (reserva, salida).
            $table->unique(['reserva_id', 'salida_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_reservas');
    }
};
