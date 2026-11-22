<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    protected $casts = [
        'is_open' => 'boolean',
    ];

    public function options(): HasMany {

        return $this->hasMany(SurveyOption::class);
    }

    public function votes(): HasMany {

        return $this->hasMany(SurveyVote::class);
    }
}
