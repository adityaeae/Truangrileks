<?php

namespace Tests\Feature;

use App\Models\ScreeningResult;
use App\Models\ScreeningResultShare;
use App\Models\User;
use App\Services\ScreeningService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ScreeningSharingTest extends TestCase
{
    use DatabaseTransactions;

    private function createStoredResult(User $student, ?array $answerOverrides = null): ScreeningResult
    {
        $service = app(ScreeningService::class);
        $answers = array_fill_keys(array_column($service->getQuestions(), 'code'), 'KK');
        if ($answerOverrides !== null) {
            $answers = array_replace($answers, $answerOverrides);
        }

        $result = ScreeningResult::create([
            'user_id' => $student->id,
            'score' => null,
            'interpretation' => null,
            'meta' => $service->calculateScreeningSummary($answers, 'moderate'),
            'completed_at' => now(),
        ]);

        foreach ($answers as $code => $answer) {
            $result->answers()->create([
                'question_code' => $code,
                'answer' => (string) $answer,
            ]);
        }

        $result->consents()->createMany([
            [
                'user_id' => $student->id,
                'consent_type' => 'participation',
                'consent_given' => true,
                'consented_at' => now(),
            ],
            [
                'user_id' => $student->id,
                'consent_type' => 'storage',
                'consent_given' => true,
                'consented_at' => now(),
            ],
        ]);

        return $result;
    }

    private function createShare(User $student, User $teacher, ScreeningResult $result): ScreeningResultShare
    {
        return ScreeningResultShare::create([
            'screening_result_id' => $result->id,
            'student_id' => $student->id,
            'bk_teacher_id' => $teacher->id,
            'consent_version' => '2026-11-02-v1',
            'consented_at' => now(),
        ]);
    }

    public function test_guest_cannot_open_student_share_management(): void
    {
        $this->get(route('student.screening-shares.create', 1))
            ->assertRedirect(route('login'));
    }

    public function test_non_student_cannot_open_student_share_management(): void
    {
        $teacher = User::factory()->create(['role' => 'guru_bk']);

        $this->actingAs($teacher)
            ->get(route('student.screening-shares.create', 1))
            ->assertForbidden();
    }

    public function test_student_can_open_share_management_for_own_stored_result(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);

        $this->actingAs($student)
            ->get(route('student.screening-shares.create', $result->id))
            ->assertOk()
            ->assertSee('Berbagi Hasil kepada Guru BK')
            ->assertSee('jawaban pertanyaan tambahan tentang dampak aktivitas')
            ->assertSee($teacher->name)
            ->assertSee($teacher->email);
    }

    public function test_student_cannot_share_another_students_result(): void
    {
        $owner = User::factory()->create(['role' => 'siswa']);
        $otherStudent = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($owner);

        $this->actingAs($otherStudent)
            ->post(route('student.screening-shares.store', $result->id), [
                'teacher_id' => $teacher->id,
                'consent_share' => '1',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('screening_result_shares', 0);
    }

    public function test_student_cannot_share_result_without_storage_consent(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = ScreeningResult::create([
            'user_id' => $student->id,
            'score' => 50,
            'interpretation' => null,
            'meta' => [],
            'completed_at' => now(),
        ]);

        $this->actingAs($student)
            ->post(route('student.screening-shares.store', $result->id), [
                'teacher_id' => $teacher->id,
                'consent_share' => '1',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('screening_result_shares', 0);
    }

    public function test_explicit_sharing_consent_is_required(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);

        $this->actingAs($student)
            ->from(route('student.screening-shares.create', $result->id))
            ->post(route('student.screening-shares.store', $result->id), [
                'teacher_id' => $teacher->id,
            ])
            ->assertRedirect(route('student.screening-shares.create', $result->id))
            ->assertSessionHasErrors('consent_share');

        $this->assertDatabaseCount('screening_result_shares', 0);
    }

    public function test_student_can_share_one_result_with_selected_bk_teacher(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);

        $this->actingAs($student)
            ->post(route('student.screening-shares.store', $result->id), [
                'teacher_id' => $teacher->id,
                'consent_share' => '1',
            ])
            ->assertRedirect(route('student.screening-shares.create', $result->id))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('screening_result_shares', [
            'screening_result_id' => $result->id,
            'student_id' => $student->id,
            'bk_teacher_id' => $teacher->id,
            'consent_version' => '2026-11-02-v1',
            'revoked_at' => null,
        ]);
    }

    public function test_only_bk_teacher_accounts_can_be_selected(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $otherStudent = User::factory()->create(['role' => 'siswa']);
        $result = $this->createStoredResult($student);

        $this->actingAs($student)
            ->post(route('student.screening-shares.store', $result->id), [
                'teacher_id' => $otherStudent->id,
                'consent_share' => '1',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('screening_result_shares', 0);
    }

    public function test_duplicate_active_share_to_same_teacher_is_not_created(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);
        $this->createShare($student, $teacher, $result);

        $this->actingAs($student)
            ->from(route('student.screening-shares.create', $result->id))
            ->post(route('student.screening-shares.store', $result->id), [
                'teacher_id' => $teacher->id,
                'consent_share' => '1',
            ])
            ->assertRedirect(route('student.screening-shares.create', $result->id))
            ->assertSessionHasErrors('teacher_id');

        $this->assertDatabaseCount('screening_result_shares', 1);
    }


    public function test_student_can_grant_access_again_after_previous_access_was_revoked(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);
        $previousShare = $this->createShare($student, $teacher, $result);
        $previousShare->forceFill(['revoked_at' => now()->subMinute()])->save();

        $this->actingAs($student)
            ->post(route('student.screening-shares.store', $result->id), [
                'teacher_id' => $teacher->id,
                'consent_share' => '1',
            ])
            ->assertRedirect(route('student.screening-shares.create', $result->id))
            ->assertSessionHas('status');

        $this->assertDatabaseCount('screening_result_shares', 2);
        $this->assertDatabaseHas('screening_result_shares', [
            'screening_result_id' => $result->id,
            'bk_teacher_id' => $teacher->id,
            'revoked_at' => null,
        ]);
    }

    public function test_bk_teacher_sees_only_results_explicitly_shared_with_them(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $otherTeacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);
        $share = $this->createShare($student, $teacher, $result);

        $this->actingAs($teacher)
            ->get(route('bk.shared-results.index'))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee(route('bk.shared-results.show', $share->id), false);

        $this->actingAs($otherTeacher)
            ->get(route('bk.shared-results.index'))
            ->assertOk()
            ->assertSee('Belum ada hasil yang dibagikan')
            ->assertDontSee($student->name);
    }

    public function test_bk_teacher_can_view_shared_result_and_sixteen_answers(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);
        $share = $this->createShare($student, $teacher, $result);

        $this->actingAs($teacher)
            ->get(route('bk.shared-results.show', $share->id))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Ringkasan Hasil Skrining')
            ->assertSee('Stres karena Beban Akademik yang Berlebihan')
            ->assertSee('Stres atas Perspektif Masa Depan')
            ->assertSee('1.')
            ->assertSee('16.')
            ->assertSee('Jawaban: Kadang-kadang');
    }

    public function test_bk_teacher_sees_descriptive_response_distribution_for_each_original_dimension(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student, [
            'BA01' => 'Ser',
            'BA02' => 'TP',
            'BA03' => 'TP',
            'BA04' => 'KK',
            'BA05' => 'TP',
        ]);
        $share = $this->createShare($student, $teacher, $result);

        $this->actingAs($teacher)
            ->get(route('bk.shared-results.show', $share->id))
            ->assertOk()
            ->assertSee('Distribusi jawaban per komponen awal')
            ->assertSee('Stres karena Beban Akademik yang Berlebihan')
            ->assertSee('Stres dalam Interaksi dengan Teman Sekelas')
            ->assertSee('Tekanan dari Anggota Keluarga')
            ->assertSee('Stres atas Perspektif Masa Depan')
            ->assertSee('5/5 butir')
            ->assertSee('4 respons')
            ->assertSee('1 respons')
            ->assertSee('Ini bukan skor atau tingkat stres.');
    }

    public function test_bk_teacher_sees_the_descriptive_source_profile_from_shared_answers(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student, [
            'KF01' => 'Ser',
            'KF02' => 'Sel',
            'KF03' => 'Ser',
            'KF04' => 'TP',
        ]);
        $share = $this->createShare($student, $teacher, $result);

        $this->actingAs($teacher)
            ->get(route('bk.shared-results.show', $share->id))
            ->assertOk()
            ->assertSee('Pola sumber tekanan akademik yang paling menonjol')
            ->assertSee('Pola jawaban paling menonjol pada aspek Tekanan dari Anggota Keluarga.')
            ->assertSee('3 dari 4 butir')
            ->assertSee('bukan diagnosis');
    }

    public function test_bk_teacher_sees_student_reported_daily_impact_and_follow_up_recommendation(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);
        $share = $this->createShare($student, $teacher, $result);

        $this->actingAs($teacher)
            ->get(route('bk.shared-results.show', $share->id))
            ->assertOk()
            ->assertSee('Dampak pada kegiatan sehari-hari menurut siswa')
            ->assertSee('Cukup mengganggu')
            ->assertSee('Rekomendasi tindak lanjut')
            ->assertSee('Buat janji untuk berdiskusi dengan Guru BK atau wali kelas.')
            ->assertSee('bukan penetapan tingkat keparahan stres');
    }

    public function test_bk_teacher_cannot_open_result_shared_with_another_teacher(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $otherTeacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);
        $share = $this->createShare($student, $teacher, $result);

        $this->actingAs($otherTeacher)
            ->get(route('bk.shared-results.show', $share->id))
            ->assertNotFound();
    }

    public function test_student_can_revoke_bk_access_and_teacher_loses_access(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);
        $share = $this->createShare($student, $teacher, $result);

        $this->actingAs($student)
            ->delete(route('student.screening-shares.revoke', [$result->id, $share->id]))
            ->assertRedirect(route('student.screening-shares.create', $result->id))
            ->assertSessionHas('status');

        $this->assertNotNull($share->fresh()->revoked_at);

        $this->actingAs($teacher)
            ->get(route('bk.shared-results.show', $share->id))
            ->assertNotFound();

        $this->actingAs($teacher)
            ->get(route('bk.shared-results.index'))
            ->assertOk()
            ->assertSee('Belum ada hasil yang dibagikan');
    }

    public function test_student_cannot_revoke_share_belonging_to_another_student(): void
    {
        $owner = User::factory()->create(['role' => 'siswa']);
        $otherStudent = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($owner);
        $share = $this->createShare($owner, $teacher, $result);

        $this->actingAs($otherStudent)
            ->delete(route('student.screening-shares.revoke', [$result->id, $share->id]))
            ->assertNotFound();

        $this->assertNull($share->fresh()->revoked_at);
    }


    public function test_guest_cannot_access_bk_shared_results(): void
    {
        $this->get(route('bk.shared-results.index'))
            ->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_bk_shared_results(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($student)
            ->get(route('bk.shared-results.index'))
            ->assertForbidden();
    }

    public function test_bk_teacher_cannot_access_student_sharing_form(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $result = $this->createStoredResult($student);

        $this->actingAs($teacher)
            ->get(route('student.screening-shares.create', $result->id))
            ->assertForbidden();
    }
}
