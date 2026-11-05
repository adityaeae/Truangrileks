<?php

namespace Tests\Feature;

use App\Models\BkAppointment;
use App\Models\BkServiceSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BkAppointmentTest extends TestCase
{
    use DatabaseTransactions;

    private function schedule(User $teacher, array $attributes = []): BkServiceSchedule
    {
        $start = now()->addDays(3)->setTime(9, 0);

        return BkServiceSchedule::create(array_merge([
            'bk_teacher_id' => $teacher->id,
            'created_by' => $teacher->id,
            'title' => 'Konsultasi BK Pagi',
            'description' => 'Layanan konsultasi umum.',
            'service_mode' => 'tatap_muka',
            'location' => 'Ruang BK',
            'starts_at' => $start,
            'ends_at' => $start->copy()->addMinutes(30),
            'capacity' => 1,
            'is_published' => true,
        ], $attributes));
    }

    public function test_teacher_can_create_and_update_own_service_schedule(): void
    {
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $startsAt = now()->addDays(4)->setTime(10, 0);

        $this->actingAs($teacher)
            ->post(route('bk.service-schedules.store'), [
                'title' => 'Konsultasi Belajar',
                'description' => 'Sesi konsultasi belajar.',
                'service_mode' => 'tatap_muka',
                'location' => 'Ruang BK 1',
                'starts_at' => $startsAt->format('Y-m-d\TH:i'),
                'ends_at' => $startsAt->copy()->addMinutes(30)->format('Y-m-d\TH:i'),
                'capacity' => 2,
                'is_published' => 1,
            ])
            ->assertRedirect(route('bk.service-schedules.index'));

        $schedule = BkServiceSchedule::query()->where('bk_teacher_id', $teacher->id)->firstOrFail();
        $this->assertSame('Konsultasi Belajar', $schedule->title);
        $this->assertSame(2, $schedule->capacity);

        $newStart = now()->addDays(5)->setTime(11, 0);
        $this->actingAs($teacher)
            ->put(route('bk.service-schedules.update', $schedule->id), [
                'title' => 'Konsultasi Belajar Updated',
                'description' => 'Informasi terbaru.',
                'service_mode' => 'daring',
                'location' => 'Google Meet',
                'starts_at' => $newStart->format('Y-m-d\TH:i'),
                'ends_at' => $newStart->copy()->addMinutes(30)->format('Y-m-d\TH:i'),
                'capacity' => 3,
                'is_published' => 1,
            ])
            ->assertRedirect(route('bk.service-schedules.index'));

        $this->assertDatabaseHas('bk_service_schedules', [
            'id' => $schedule->id,
            'title' => 'Konsultasi Belajar Updated',
            'service_mode' => 'daring',
            'capacity' => 3,
        ]);
    }

    public function test_admin_can_create_service_schedule_for_active_teacher(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $startsAt = now()->addDays(6)->setTime(13, 0);

        $this->actingAs($admin)
            ->post(route('admin.service-schedules.store'), [
                'bk_teacher_id' => $teacher->id,
                'title' => 'Layanan Konseling Siang',
                'description' => 'Jadwal dari administrator.',
                'service_mode' => 'tatap_muka',
                'location' => 'Ruang BK',
                'starts_at' => $startsAt->format('Y-m-d\TH:i'),
                'ends_at' => $startsAt->copy()->addMinutes(45)->format('Y-m-d\TH:i'),
                'capacity' => 1,
                'is_published' => 1,
            ])
            ->assertRedirect(route('admin.service-schedules.index'));

        $this->assertDatabaseHas('bk_service_schedules', [
            'bk_teacher_id' => $teacher->id,
            'created_by' => $admin->id,
            'title' => 'Layanan Konseling Siang',
        ]);
    }

    public function test_student_can_view_published_schedule_and_request_an_appointment(): void
    {
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $published = $this->schedule($teacher);
        $this->schedule($teacher, [
            'title' => 'Jadwal Draft Jangan Ditampilkan',
            'starts_at' => now()->addDays(4)->setTime(10, 0),
            'ends_at' => now()->addDays(4)->setTime(10, 30),
            'is_published' => false,
        ]);

        $this->actingAs($student)
            ->get(route('student.bk-appointments.index'))
            ->assertOk()
            ->assertSee('Konsultasi BK Pagi')
            ->assertDontSee('Jadwal Draft Jangan Ditampilkan');

        $this->actingAs($student)
            ->post(route('student.bk-appointments.store'), [
                'service_schedule_id' => $published->id,
                'student_note' => 'Saya ingin berkonsultasi tentang pengaturan waktu belajar.',
            ])
            ->assertRedirect(route('student.bk-appointments.index'));

        $this->assertDatabaseHas('bk_appointments', [
            'service_schedule_id' => $published->id,
            'student_id' => $student->id,
            'status' => BkAppointment::STATUS_PENDING,
        ]);

        $this->actingAs($student)
            ->get(route('student.bk-appointments.index'))
            ->assertOk()
            ->assertSee('Menunggu konfirmasi');
    }

    public function test_student_cannot_book_full_slot_or_overlap_an_existing_appointment(): void
    {
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $otherStudent = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $schedule = $this->schedule($teacher, ['capacity' => 1]);
        BkAppointment::create([
            'service_schedule_id' => $schedule->id,
            'student_id' => $otherStudent->id,
            'status' => BkAppointment::STATUS_APPROVED,
            'requested_at' => now(),
        ]);

        $this->actingAs($student)
            ->from(route('student.bk-appointments.index'))
            ->post(route('student.bk-appointments.store'), ['service_schedule_id' => $schedule->id])
            ->assertRedirect(route('student.bk-appointments.index'))
            ->assertSessionHasErrors('service_schedule_id');

        $this->assertDatabaseMissing('bk_appointments', [
            'service_schedule_id' => $schedule->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_teacher_can_confirm_only_appointments_for_their_own_schedule(): void
    {
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $otherTeacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $ownSchedule = $this->schedule($teacher);
        $otherSchedule = $this->schedule($otherTeacher, [
            'starts_at' => now()->addDays(5)->setTime(10, 0),
            'ends_at' => now()->addDays(5)->setTime(10, 30),
        ]);
        $ownAppointment = BkAppointment::create([
            'service_schedule_id' => $ownSchedule->id,
            'student_id' => $student->id,
            'status' => BkAppointment::STATUS_PENDING,
            'requested_at' => now(),
        ]);
        $otherAppointment = BkAppointment::create([
            'service_schedule_id' => $otherSchedule->id,
            'student_id' => $student->id,
            'status' => BkAppointment::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->patch(route('bk.service-schedules.appointments.status', $ownAppointment->id), ['status' => 'approved'])
            ->assertRedirect(route('bk.service-schedules.index'));

        $this->assertDatabaseHas('bk_appointments', [
            'id' => $ownAppointment->id,
            'status' => BkAppointment::STATUS_APPROVED,
        ]);

        $this->actingAs($teacher)
            ->patch(route('bk.service-schedules.appointments.status', $otherAppointment->id), ['status' => 'approved'])
            ->assertNotFound();
    }

    public function test_student_only_sees_their_own_appointments_and_can_cancel_a_future_booking(): void
    {
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $otherStudent = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $ownSchedule = $this->schedule($teacher);
        $otherSchedule = $this->schedule($teacher, [
            'title' => 'Jadwal milik siswa lain',
            'starts_at' => now()->addDays(5)->setTime(10, 0),
            'ends_at' => now()->addDays(5)->setTime(10, 30),
        ]);
        $ownAppointment = BkAppointment::create([
            'service_schedule_id' => $ownSchedule->id,
            'student_id' => $student->id,
            'student_note' => 'Catatan saya sendiri.',
            'status' => BkAppointment::STATUS_PENDING,
            'requested_at' => now(),
        ]);
        BkAppointment::create([
            'service_schedule_id' => $otherSchedule->id,
            'student_id' => $otherStudent->id,
            'student_note' => 'CATATAN-MILIK-SISWA-LAIN',
            'status' => BkAppointment::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('student.bk-appointments.index'))
            ->assertOk()
            ->assertSee('Catatan saya sendiri.')
            ->assertDontSee('CATATAN-MILIK-SISWA-LAIN');

        $this->actingAs($student)
            ->delete(route('student.bk-appointments.cancel', $ownAppointment->id))
            ->assertRedirect(route('student.bk-appointments.index'));

        $this->assertDatabaseHas('bk_appointments', [
            'id' => $ownAppointment->id,
            'status' => BkAppointment::STATUS_CANCELLED,
        ]);
    }

    public function test_admin_can_manage_schedules_but_does_not_see_student_notes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $schedule = $this->schedule($teacher);
        BkAppointment::create([
            'service_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'student_note' => 'CATATAN-PRIBADI-SISWA-123',
            'status' => BkAppointment::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.service-schedules.index'))
            ->assertOk()
            ->assertSee('Konsultasi BK Pagi')
            ->assertDontSee('CATATAN-PRIBADI-SISWA-123')
            ->assertDontSee($student->email);
    }

    public function test_students_and_other_teachers_cannot_manage_service_schedules(): void
    {
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $otherTeacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $schedule = $this->schedule($teacher);

        $this->actingAs($student)
            ->get(route('bk.service-schedules.index'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('admin.service-schedules.index'))
            ->assertForbidden();

        $this->actingAs($otherTeacher)
            ->get(route('bk.service-schedules.edit', $schedule->id))
            ->assertNotFound();
    }

    public function test_teacher_cannot_publish_overlapping_service_schedules(): void
    {
        $teacher = User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $this->schedule($teacher);
        $startsAt = now()->addDays(3)->setTime(9, 15);

        $this->actingAs($teacher)
            ->from(route('bk.service-schedules.create'))
            ->post(route('bk.service-schedules.store'), [
                'title' => 'Jadwal Bertabrakan',
                'service_mode' => 'tatap_muka',
                'location' => 'Ruang BK',
                'starts_at' => $startsAt->format('Y-m-d\TH:i'),
                'ends_at' => $startsAt->copy()->addMinutes(30)->format('Y-m-d\TH:i'),
                'capacity' => 1,
                'is_published' => 1,
            ])
            ->assertRedirect(route('bk.service-schedules.create'))
            ->assertSessionHasErrors('starts_at');

        $this->assertDatabaseMissing('bk_service_schedules', ['title' => 'Jadwal Bertabrakan']);
    }
}
