<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningConsent extends Model
{
    protected $fillable = ['user_id', 'screening_result_id', 'consent_type', 'consent_given', 'consented_at', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return [
            'consent_given' => 'boolean',
            'consented_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function screeningResult(): BelongsTo
    {
        return $this->belongsTo(ScreeningResult::class);
    }
}
