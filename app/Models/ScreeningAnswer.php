<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningAnswer extends Model
{
    protected $fillable = ['screening_result_id', 'question_code', 'answer'];

    public function screeningResult(): BelongsTo
    {
        return $this->belongsTo(ScreeningResult::class);
    }
}
