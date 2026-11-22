<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('customer_name', 80);
            $table->string('customer_email', 120);
            $table->dateTime('reserved_at');
            $table->enum('status', ['pendiente', 'confirmada', 'cancelada'])->default('pendiente');
            $table->timestamps();

            // Un mismo servicio no puede tener dos reservas a la misma hora.
            // La regla tambien esta en el controlador, pero aqui la respalda la BD.
            $table->unique(['service_id', 'reserved_at'], 'reservations_slot_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
