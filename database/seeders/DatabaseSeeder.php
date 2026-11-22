<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // El ejercicio 5 necesita un catalogo de servicios para poder reservar
        $this->call([
            ServiceSeeder::class,
        ]);
    }
}
