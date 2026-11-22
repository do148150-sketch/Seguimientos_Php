<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function reservations(): HasMany {

        return $this->hasMany(Reservation::class);
    }
}
