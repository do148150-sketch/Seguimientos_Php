<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('meal_type', 40);
            // Un ingrediente por linea
            $table->text('ingredients');
            $table->text('instructions');
            $table->unsignedSmallInteger('minutes');
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();

            $table->index('meal_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
