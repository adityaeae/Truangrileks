<?php

namespace Tests\Feature;

use App\Models\ScreeningResult;
use App\Models\User;
use App\Services\ScreeningService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ScreeningHistoryTest extends TestCase
{
    use DatabaseTransactions;

    private function createStoredResult(User $student, string $completedAt): ScreeningResult
    {
        $service = app(ScreeningService::class);
        $answers = array_fill_keys(array_column($service->getQuestions(), 'code'), 'KK');

        $result = ScreeningResult::create([
            'user_id' => $student->id,
            'score' => null,
            'interpretation' => null,
            'meta' => $service->calculateScreeningSummary($answers),
            'completed_at' => $completedAt,
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
                'consented_at' => $completedAt,
            ],
            [
                'user_id' => $student->id,
                'consent_type' => 'storage',
                'consent_given' => true,
                'consented_at' => $completedAt,
            ],
        ]);

        return $result;
    }

    public function test_guest_cannot_access_screening_history(): void
    {
        $this->get(route('screening-history.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_student_role_cannot_access_screening_history(): void
    {
        $teacher = User::factory()->create(['role' => 'guru_bk']);

        $this->actingAs($teacher)
            ->get(route('screening-history.index'))
            ->assertForbidden();
    }

    public function test_student_sees_only_own_results_with_storage_consent(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $otherStudent = User::factory()->create(['role' => 'siswa']);

       $ownResult = $this->createStoredResult($student, '2026-10-01 03:00:00');
        $newerOwnResult = $this->createStoredResult($student, '2026-10-04 03:00:00');
        $otherResult = $this->createStoredResult($otherStudent, '2026-10-02 03:00:00');

        $unconsentedResult = ScreeningResult::create([
            'user_id' => $student->id,
            'score' => null,
            'interpretation' => null,
            'meta' => [],
            'completed_at' => '2026-10-03 03:00:00',
        ]);

        $response = $this->actingAs($student)
            ->get(route('screening-history.index'))
            ->assertOk()
            ->assertSee('Riwayat Skrining')
            ->assertSeeInOrder(['04/10/2026 10:00', '01/10/2026 10:00'])
            ->assertDontSee('02/10/2026 10:00')
            ->assertDontSee('03/10/2026 10:00');

        $response->assertSee(route('screening.result', $ownResult->id), false);
        $response->assertSee(route('screening.result', $newerOwnResult->id), false);
        $response->assertDontSee(route('screening.result', $otherResult->id), false);
        $this->assertDatabaseHas('screening_results', ['id' => $unconsentedResult->id]);
    }

    public function test_history_detail_shows_saved_answers_for_owner(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $result = $this->createStoredResult($student, '2026-10-01 10:00:00');

        $this->actingAs($student)
            ->get(route('screening.result', $result->id))
            ->assertOk()
            ->assertSee('Jawaban yang tersimpan')
            ->assertSee('Stres karena Beban Akademik yang Berlebihan')
            ->assertSee('Saya merasa terlalu banyak tugas yang diberikan guru.')
            ->assertSee('Jawaban: Kadang-kadang')
            ->assertSee(route('screening-history.index'), false);
    }

    public function test_student_cannot_open_another_students_history_detail(): void
    {
        $owner = User::factory()->create(['role' => 'siswa']);
        $otherStudent = User::factory()->create(['role' => 'siswa']);
        $result = $this->createStoredResult($owner, '2026-10-01 10:00:00');

        $this->actingAs($otherStudent)
            ->get(route('screening.result', $result->id))
            ->assertNotFound();
    }

    public function test_student_cannot_open_result_without_storage_consent(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $result = ScreeningResult::create([
            'user_id' => $student->id,
            'score' => 50,
            'interpretation' => null,
            'meta' => [],
            'completed_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('screening.result', $result->id))
            ->assertNotFound();
    }

    public function test_student_can_delete_own_result_and_related_records(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $result = $this->createStoredResult($student, '2026-10-01 10:00:00');
        $answerIds = $result->answers()->pluck('id');
        $consentIds = $result->consents()->pluck('id');

        $this->actingAs($student)
            ->delete(route('screening-history.destroy', $result->id))
            ->assertRedirect(route('screening-history.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('screening_results', ['id' => $result->id]);
        foreach ($answerIds as $answerId) {
            $this->assertDatabaseMissing('screening_answers', ['id' => $answerId]);
        }
        foreach ($consentIds as $consentId) {
            $this->assertDatabaseMissing('screening_consents', ['id' => $consentId]);
        }
    }

    public function test_student_cannot_delete_another_students_result(): void
    {
        $owner = User::factory()->create(['role' => 'siswa']);
        $otherStudent = User::factory()->create(['role' => 'siswa']);
        $result = $this->createStoredResult($owner, '2026-10-01 10:00:00');

        $this->actingAs($otherStudent)
            ->delete(route('screening-history.destroy', $result->id))
            ->assertNotFound();

        $this->assertDatabaseHas('screening_results', ['id' => $result->id, 'user_id' => $owner->id]);
        $this->assertSame(16, $result->answers()->count());
    }
}
