<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\BkAppointment;
use App\Models\BkServiceSchedule;
use App\Notifications\BkAppointmentNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BkAppointmentManagementController extends Controller
{
    public function updateStatus(Request $request, int $appointmentId): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                BkAppointment::STATUS_APPROVED,
                BkAppointment::STATUS_REJECTED,
                BkAppointment::STATUS_COMPLETED,
            ])],
        ]);

        $appointment = BkAppointment::query()
            ->with(['schedule', 'student'])
            ->whereKey($appointmentId)
            ->whereHas('schedule', fn (Builder $query) => $query
                ->where('bk_teacher_id', $request->user()->id))
            ->firstOrFail();

        $current = $appointment->status;
        $next = $validated['status'];
        $allowed = ($current === BkAppointment::STATUS_PENDING
                && in_array($next, [BkAppointment::STATUS_APPROVED, BkAppointment::STATUS_REJECTED], true))
            || ($current === BkAppointment::STATUS_APPROVED
                && in_array($next, [BkAppointment::STATUS_COMPLETED, BkAppointment::STATUS_REJECTED], true));

        if (! $allowed) {
            return back()->withErrors([
                'appointment' => 'Perubahan status janji temu tersebut tidak diizinkan.',
            ]);
        }

        if ($next === BkAppointment::STATUS_APPROVED && $appointment->schedule->starts_at->isPast()) {
            return back()->withErrors([
                'appointment' => 'Janji temu yang waktunya sudah lewat tidak dapat dikonfirmasi.',
            ]);
        }

        $appointment->update([
            'status' => $next,
            'responded_at' => now(),
        ]);

        [$event, $title, $notificationMessage, $message] = match ($next) {
            BkAppointment::STATUS_APPROVED => [
                'appointment_approved',
                'Janji temu dikonfirmasi',
                'Permintaan janji temu pada jadwal '.$appointment->schedule->title.' telah dikonfirmasi Guru BK.',
                'Janji temu berhasil dikonfirmasi.',
            ],
            BkAppointment::STATUS_REJECTED => [
                'appointment_rejected',
                'Permintaan janji temu ditolak',
                'Permintaan janji temu pada jadwal '.$appointment->schedule->title.' ditolak Guru BK.',
                'Permintaan janji temu ditolak.',
            ],
            BkAppointment::STATUS_COMPLETED => [
                'appointment_completed',
                'Janji temu selesai',
                'Janji temu pada jadwal '.$appointment->schedule->title.' telah ditandai selesai.',
                'Janji temu ditandai selesai.',
            ],
        };

        $student = $appointment->student;
        if ($student && $student->role === 'siswa' && $student->is_active) {
            $student->notify(new BkAppointmentNotification(
                $event,
                $title,
                $notificationMessage,
                route('student.bk-appointments.index')
            ));
        }

        return redirect()->route('bk.service-schedules.index')->with('status', $message);
    }
}
