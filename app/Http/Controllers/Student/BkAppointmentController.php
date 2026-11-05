<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BkAppointment;
use App\Models\BkServiceSchedule;
use App\Models\User;
use App\Notifications\BkAppointmentNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BkAppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $studentId = $request->user()->id;

        $schedules = BkServiceSchedule::query()
            ->with('teacher:id,name,email')
            ->withCount(['activeAppointments as active_appointments_count'])
            ->where('is_published', true)
            ->where('starts_at', '>', now())
            ->whereHas('teacher', fn (Builder $query) => $query
                ->where('role', 'guru_bk')
                ->where('is_active', true))
            ->orderBy('starts_at')
            ->limit(40)
            ->get();

        $appointments = BkAppointment::query()
            ->with(['schedule.teacher:id,name,email'])
            ->where('student_id', $studentId)
            ->orderByDesc('requested_at')
            ->paginate(10);

        return view('student.bk-appointments.index', [
            'schedules' => $schedules,
            'appointments' => $appointments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_schedule_id' => ['required', 'integer', 'exists:bk_service_schedules,id'],
            'student_note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $validated): BkAppointment {
            // Serialize simultaneous bookings by the same student to prevent overlapping appointments.
            User::query()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();

            $schedule = BkServiceSchedule::query()
                ->whereKey($validated['service_schedule_id'])
                ->where('is_published', true)
                ->lockForUpdate()
                ->firstOrFail();

            if ($schedule->starts_at->isPast() || ! $schedule->teacher()->where('role', 'guru_bk')->where('is_active', true)->exists()) {
                throw ValidationException::withMessages([
                    'service_schedule_id' => 'Jadwal ini sudah tidak tersedia. Silakan pilih jadwal lain.',
                ]);
            }

            $activeCount = $schedule->activeAppointments()->count();
            if ($activeCount >= $schedule->capacity) {
                throw ValidationException::withMessages([
                    'service_schedule_id' => 'Kuota pada jadwal ini sudah penuh. Silakan pilih jadwal lain.',
                ]);
            }

            $hasOverlappingAppointment = BkAppointment::query()
                ->where('student_id', $request->user()->id)
                ->whereIn('status', BkServiceSchedule::ACTIVE_APPOINTMENT_STATUSES)
                ->whereHas('schedule', function (Builder $query) use ($schedule): void {
                    $query->where('starts_at', '<', $schedule->ends_at)
                        ->where('ends_at', '>', $schedule->starts_at);
                })
                ->exists();

            if ($hasOverlappingAppointment) {
                throw ValidationException::withMessages([
                    'service_schedule_id' => 'Kamu sudah memiliki janji temu aktif pada waktu yang bertabrakan. Batalkan atau pilih waktu lain.',
                ]);
            }

            $appointment = BkAppointment::create([
                'service_schedule_id' => $schedule->id,
                'student_id' => $request->user()->id,
                'student_note' => $validated['student_note'] ?? null,
                'status' => BkAppointment::STATUS_PENDING,
                'requested_at' => now(),
            ]);

            $schedule->loadMissing('teacher');
            $teacher = $schedule->teacher;

            if ($teacher && $teacher->role === 'guru_bk' && $teacher->is_active) {
                $teacher->notify(new BkAppointmentNotification(
                    'appointment_requested',
                    'Permintaan janji temu baru',
                    'Ada siswa yang mengajukan janji temu pada jadwal '.$schedule->title.'.',
                    route('bk.service-schedules.index')
                ));
            }

            return $appointment;
        }, 3);

        return redirect()->route('student.bk-appointments.index')
            ->with('status', 'Permintaan janji temu berhasil dikirim. Tunggu konfirmasi dari Guru BK.');
    }

    public function cancel(Request $request, int $appointmentId): RedirectResponse
    {
        $appointment = BkAppointment::query()
            ->with('schedule')
            ->where('student_id', $request->user()->id)
            ->whereKey($appointmentId)
            ->firstOrFail();

        if (! in_array($appointment->status, BkServiceSchedule::ACTIVE_APPOINTMENT_STATUSES, true)
            || $appointment->schedule->starts_at->isPast()) {
            return back()->withErrors([
                'appointment' => 'Janji temu ini tidak dapat dibatalkan melalui aplikasi.',
            ]);
        }

        $appointment->loadMissing('schedule.teacher');
        $appointment->update([
            'status' => BkAppointment::STATUS_CANCELLED,
            'responded_at' => now(),
        ]);

        $teacher = $appointment->schedule->teacher;
        if ($teacher && $teacher->role === 'guru_bk' && $teacher->is_active) {
            $teacher->notify(new BkAppointmentNotification(
                'appointment_cancelled',
                'Janji temu dibatalkan siswa',
                'Seorang siswa membatalkan janji temu pada jadwal '.$appointment->schedule->title.'.',
                route('bk.service-schedules.index')
            ));
        }

        return redirect()->route('student.bk-appointments.index')
            ->with('status', 'Janji temu berhasil dibatalkan.');
    }
}
