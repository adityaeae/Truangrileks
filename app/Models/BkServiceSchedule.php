<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BkServiceSchedule extends Model
{
    public const ACTIVE_APPOINTMENT_STATUSES = ['pending', 'approved'];

    protected $fillable = [
        'bk_teacher_id',
        'created_by',
        'title',
        'description',
        'service_mode',
        'location',
        'starts_at',
        'ends_at',
        'capacity',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_published' => 'boolean',
            'capacity' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bk_teacher_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(BkAppointment::class, 'service_schedule_id');
    }

    public function activeAppointments(): HasMany
    {
        return $this->appointments()->whereIn('status', self::ACTIVE_APPOINTMENT_STATUSES);
    }
}
