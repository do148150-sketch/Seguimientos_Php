<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    /**
     * Los ingredientes se guardan uno por linea. Esto los devuelve
     * ya separados y sin lineas vacias, listos para recorrer en la vista.
     */
    public function ingredientList(): array {

        return array_values(array_filter(
            array_map('trim', explode("\n", $this->ingredients)),
            fn ($line) => $line !== ''
        ));
    }
}
