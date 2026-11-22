<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('description', 255)->nullable();
            $table->dateTime('starts_at');
            // Minutos de antelacion del recordatorio. Null = sin recordatorio.
            $table->unsignedSmallInteger('reminder_minutes')->nullable();
            $table->timestamps();

            // El calendario pide siempre un rango de fechas
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
