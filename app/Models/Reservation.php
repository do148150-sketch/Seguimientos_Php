<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    /**
     * Sin el cast, 'reserved_at' llega como string y no se le puede
     * dar formato de fecha/hora en la vista.
     */
    protected $casts = [
        'reserved_at' => 'datetime',
    ];

    public function service(): BelongsTo {

        return $this->belongsTo(Service::class);
    }
}
