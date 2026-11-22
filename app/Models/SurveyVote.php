<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyVote extends Model
{
    public function survey(): BelongsTo {

        return $this->belongsTo(Survey::class);
    }

    public function option(): BelongsTo {

        return $this->belongsTo(SurveyOption::class, 'survey_option_id');
    }
}
