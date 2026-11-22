<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    /**
     * Sin esto 'spent_on' llega como string y no se le puede dar formato
     * de fecha en la vista.
     */
    protected $casts = [
        'spent_on' => 'date',
        'amount'   => 'decimal:2',
    ];
}
