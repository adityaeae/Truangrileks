<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningResultShare extends Model
{
    protected $fillable = [
        'screening_result_id',
        'student_id',
        'bk_teacher_id',
        'consent_version',
        'consented_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function screeningResult(): BelongsTo
    {
        return $this->belongsTo(ScreeningResult::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bk_teacher_id');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }
}
