<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScreeningResult extends Model
{
    protected $fillable = ['user_id', 'score', 'interpretation', 'meta', 'completed_at'];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ScreeningAnswer::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(ScreeningConsent::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ScreeningResultShare::class);
    }
}
