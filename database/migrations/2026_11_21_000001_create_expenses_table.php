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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description', 120);
            $table->decimal('amount', 12, 2);
            $table->string('category', 40);
            $table->date('spent_on');          // la fecha del gasto, no la de creacion
            $table->timestamps();

            // El resumen mensual siempre filtra por fecha: que no recorra toda la tabla
            $table->index('spent_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
