<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Catalogo de servicios sobre el que se hacen las reservas.
     */
    public function run(): void
    {
        // Idempotente: correr 'db:seed' dos veces no duplica el catalogo
        if (Service::count() > 0) {
            return;
        }

        $services = [
            [
                'name'             => 'Corte de cabello',
                'description'      => 'Corte clásico con lavado y secado.',
                'duration_minutes' => 60,
                'price'            => 25000,
            ],
            [
                'name'             => 'Asesoría académica',
                'description'      => 'Acompañamiento personalizado de una hora.',
                'duration_minutes' => 60,
                'price'            => 40000,
            ],
            [
                'name'             => 'Consulta odontológica',
                'description'      => 'Valoración general y limpieza dental.',
                'duration_minutes' => 60,
                'price'            => 70000,
            ],
            [
                'name'             => 'Masaje relajante',
                'description'      => 'Masaje de descarga muscular.',
                'duration_minutes' => 60,
                'price'            => 90000,
            ],
        ];

        foreach ($services as $service) {

            Service::create($service);
        }
    }
}
