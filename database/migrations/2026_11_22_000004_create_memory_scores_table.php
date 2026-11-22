<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memory_scores', function (Blueprint $table) {
            $table->id();
            $table->string('player_name', 60);
            $table->string('difficulty', 20);
            $table->unsignedTinyInteger('pairs');
            $table->unsignedSmallInteger('moves');
            $table->unsignedSmallInteger('seconds');
            $table->unsignedInteger('points');
            $table->timestamps();

            // El ranking se ordena por dificultad y puntaje
            $table->index(['difficulty', 'points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_scores');
    }
};
