<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('question', 160);
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });

        Schema::create('survey_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->string('label', 80);
            $table->timestamps();
        });

        Schema::create('survey_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->foreignId('survey_option_id')->constrained('survey_options')->cascadeOnDelete();
            // Identifica al visitante sin pedirle datos: se guarda en su sesion
            $table->string('voter_token', 64);
            $table->timestamps();

            // Un voto por encuesta y por visitante
            $table->unique(['survey_id', 'voter_token'], 'survey_votes_one_per_voter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_votes');
        Schema::dropIfExists('survey_options');
        Schema::dropIfExists('surveys');
    }
};
