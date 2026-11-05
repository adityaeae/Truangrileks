<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function screeningResults()
    {
        return $this->hasMany(ScreeningResult::class);
    }

    public function screeningConsents(): HasMany
    {
        return $this->hasMany(ScreeningConsent::class);
    }

    public function screeningShares(): HasMany
    {
        return $this->hasMany(ScreeningResultShare::class, 'student_id');
    }

    public function receivedScreeningShares(): HasMany
    {
        return $this->hasMany(ScreeningResultShare::class, 'bk_teacher_id');
    }

    public function bkServiceSchedules(): HasMany
    {
        return $this->hasMany(BkServiceSchedule::class, 'bk_teacher_id');
    }

    public function bkAppointments(): HasMany
    {
        return $this->hasMany(BkAppointment::class, 'student_id');
    }
}
