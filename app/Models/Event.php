<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /**
     * Sin el cast, 'starts_at' llega como string y no se le puede
     * dar formato ni comparar con fechas en la vista.
     */
    protected $casts = [
        'starts_at' => 'datetime',
    ];
}
