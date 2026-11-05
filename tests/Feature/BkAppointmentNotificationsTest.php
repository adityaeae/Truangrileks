<?php

namespace Tests\Feature;

use App\Models\BkAppointment;
use App\Models\BkServiceSchedule;
use App\Models\User;
use App\Notifications\BkAppointmentNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BkAppointmentNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    private function schedule(User $teacher, array $attributes = []): BkServiceSchedule
    {
        $start = now()->addDays(4)->setTime(9, 0);

        return BkServiceSchedule::create(array_merge([
            'bk_teacher_id' => $teacher->id,
            'created_by' => $teacher->id,
            'title' => 'Konsultasi BK Pagi',
            'description' => 'Layanan konsultasi umum.',
            'service_mode' => 'tatap_muka',
            'location' => 'Ruang BK',
            'starts_at' => $start,
            'ends_at' => $start->copy()->addMinutes(30),
            'capacity' => 2,
            'is_published' => true,
        ], $attributes));
    }

    public function test_student_booking_notifies_only_the_assigned_active_teacher_without_private_note(): void
    {
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $otherTeacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $schedule = $this->schedule($teacher);
        $privateNote = 'CATATAN-PRIBADI-JANGAN-MASUK-NOTIFIKASI';

        $this->actingAs($student)
            ->post(route('student.bk-appointments.store'), [
                'service_schedule_id' => $schedule->id,
                'student_note' => $privateNote,
            ])
            ->assertRedirect(route('student.bk-appointments.index'));

        $notification = $teacher->notifications()->firstOrFail();

        $this->assertSame('appointment_requested', $notification->data['event']);
        $this->assertSame('Permintaan janji temu baru', $notification->data['title']);
        $this->assertStringContainsString($schedule->title, $notification->data['message']);
        $this->assertStringNotContainsString($privateNote, json_encode($notification->data));
        $this->assertSame(0, $otherTeacher->notifications()->count());
    }

    public function test_teacher_status_change_notifies_only_the_owning_student(): void
    {
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $otherStudent = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $schedule = $this->schedule($teacher);
        $appointment = BkAppointment::create([
            'service_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'status' => BkAppointment::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->patch(route('bk.service-schedules.appointments.status', $appointment->id), [
                'status' => BkAppointment::STATUS_APPROVED,
            ])
            ->assertRedirect(route('bk.service-schedules.index'));

        $notification = $student->notifications()->firstOrFail();

        $this->assertSame('appointment_approved', $notification->data['event']);
        $this->assertSame('Janji temu dikonfirmasi', $notification->data['title']);
        $this->assertSame(0, $otherStudent->notifications()->count());
    }

    public function test_student_cancellation_notifies_the_assigned_active_teacher(): void
    {
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $schedule = $this->schedule($teacher);
        $appointment = BkAppointment::create([
            'service_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'status' => BkAppointment::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $this->actingAs($student)
            ->delete(route('student.bk-appointments.cancel', $appointment->id))
            ->assertRedirect(route('student.bk-appointments.index'));

        $notification = $teacher->notifications()->firstOrFail();

        $this->assertSame('appointment_cancelled', $notification->data['event']);
        $this->assertSame('Janji temu dibatalkan siswa', $notification->data['title']);
    }

    public function test_notification_can_only_be_opened_by_its_owner_and_can_be_marked_read(): void
    {
        $owner = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $otherUser = User::factory()->create(['role' => 'siswa', 'is_active' => true]);

        $owner->notify(new BkAppointmentNotification(
            'appointment_approved',
            'Janji temu dikonfirmasi',
            'Permintaan janji temu berhasil dikonfirmasi.',
            route('student.bk-appointments.index')
        ));
        $otherUser->notify(new BkAppointmentNotification(
            'appointment_approved',
            'NOTIFIKASI-RAHASIA-AKUN-LAIN',
            'Pesan untuk akun lain.',
            route('dashboard')
        ));
        $notification = $owner->notifications()->firstOrFail();

        $this->actingAs($otherUser)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Buka notifikasi')
            ->assertSee('Janji temu dikonfirmasi')
            ->assertDontSee('NOTIFIKASI-RAHASIA-AKUN-LAIN');

        $this->actingAs($owner)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect(route('student.bk-appointments.index'));

        $this->assertNotNull($owner->notifications()->whereKey($notification->id)->firstOrFail()->read_at);
    }

    public function test_user_can_mark_all_own_notifications_as_read(): void
    {
        $owner = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $owner->notify(new BkAppointmentNotification(
            'appointment_approved',
            'Notifikasi pertama',
            'Contoh pesan pertama.',
            route('dashboard')
        ));
        $owner->notify(new BkAppointmentNotification(
            'appointment_rejected',
            'Notifikasi kedua',
            'Contoh pesan kedua.',
            route('dashboard')
        ));

        $this->assertSame(2, $owner->unreadNotifications()->count());

        $this->actingAs($owner)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $owner->unreadNotifications()->count());
    }
}
