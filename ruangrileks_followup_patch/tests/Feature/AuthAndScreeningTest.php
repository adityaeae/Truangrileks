<?php

namespace Tests\Feature;

use App\Models\ScreeningResult;
use App\Models\User;
use App\Services\ScreeningService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthAndScreeningTest extends TestCase
{
    use DatabaseTransactions;

    private function screeningAnswers(): array
    {
        return [
            'daily_impact' => 'moderate',
            'answers' => [
                'BA01' => 'KK', 'BA02' => 'Ser', 'BA03' => 'SJ', 'BA04' => 'KK', 'BA05' => 'TP',
                'TS01' => 'SJ', 'TS02' => 'KK', 'TS03' => 'TP',
                'KF01' => 'Ser', 'KF02' => 'SJ', 'KF03' => 'KK', 'KF04' => 'TP',
                'MD01' => 'KK', 'MD02' => 'SJ', 'MD03' => 'Ser', 'MD04' => 'KK',
            ],
        ];
    }

    private function startScreening(bool $storageConsent): void
    {
        $this->get('/skrining/info')->assertOk();

        $this->post('/skrining/setuju', [
            'confirm_participation' => '1',
        ])->assertOk();

        $this->post('/skrining/persetujuan', [
            'consent_participation' => '1',
            'consent_storage' => $storageConsent ? '1' : '0',
        ])->assertRedirect('/skrining/kuesioner');
    }

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'role' => 'siswa',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_credentials_shows_error(): void
    {
        $this->post('/login', [
            'email' => 'nouser@test.com',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_public_registration_always_creates_student_even_if_role_is_forged(): void
    {
        $email = 'student-'.uniqid().'@example.test';

        $this->post('/register', [
            'name' => 'Siswa Demo',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role' => 'siswa',
        ]);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dasbor Admin');
    }

    public function test_student_is_forbidden_from_admin_dashboard(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($student)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_guest_can_complete_screening_but_cannot_store_even_if_storage_is_posted(): void
    {
        $resultsBefore = DB::table('screening_results')->count();
        $answersBefore = DB::table('screening_answers')->count();

        $this->startScreening(true);
        $this->get('/skrining/kuesioner')->assertOk();
        $this->post('/skrining/kirim', $this->screeningAnswers())
            ->assertRedirect('/skrining/hasil/demo');
        $this->get('/skrining/hasil/demo')->assertOk()->assertSee('Ringkasan Hasil Skrining');

        $this->assertSame($resultsBefore, DB::table('screening_results')->count());
        $this->assertSame($answersBefore, DB::table('screening_answers')->count());
    }

    public function test_questionnaire_displays_five_frequency_options_from_the_paper(): void
    {
        $this->startScreening(false);

        $this->get('/skrining/kuesioner')
            ->assertOk()
            ->assertSee('Tidak pernah')
            ->assertSee('Sangat jarang')
            ->assertSee('Kadang-kadang')
            ->assertSee('Sering')
            ->assertSee('Selalu')
            ->assertSee('Saya merasa terlalu banyak tugas yang diberikan guru.');
    }

    public function test_questionnaire_displays_separate_daily_impact_question_outside_the_sixteen_items(): void
    {
        $this->startScreening(false);

        $this->get('/skrining/kuesioner')
            ->assertOk()
            ->assertSee('Pertanyaan tambahan')
            ->assertSee('di luar 16 butir instrumen')
            ->assertSee('Seberapa besar tekanan akademik yang kamu alami mengganggu kegiatan belajar dan aktivitas sehari-hari?')
            ->assertSee('Tidak mengganggu')
            ->assertSee('Sedikit mengganggu')
            ->assertSee('Cukup mengganggu')
            ->assertSee('Sangat mengganggu');
    }

    public function test_daily_impact_is_required_and_rejects_values_outside_the_defined_options(): void
    {
        $this->startScreening(false);
        $payload = $this->screeningAnswers();
        unset($payload['daily_impact']);

        $this->post('/skrining/kirim', $payload)
            ->assertRedirect('/skrining/kuesioner')
            ->assertSessionHasErrors('daily_impact');

        $payload['daily_impact'] = 'severe';
        $this->post('/skrining/kirim', $payload)
            ->assertRedirect('/skrining/kuesioner')
            ->assertSessionHasErrors('daily_impact');

        $service = app(ScreeningService::class);
        $noImpactSummary = $service->calculateScreeningSummary(
            array_fill_keys(array_column($service->getQuestions(), 'code'), 'TP'),
            'none'
        );
        $this->assertFalse($noImpactSummary['follow_up_guidance']['consultation_recommended']);
        $this->assertSame(16, $noImpactSummary['answered_items']);
        $this->assertFalse($noImpactSummary['supplementary_impact']['is_part_of_instrument']);
    }

    public function test_questionnaire_rejects_responses_outside_the_published_frequency_options(): void
    {
        $this->startScreening(false);
        $answers = $this->screeningAnswers();
        $answers['answers']['BA01'] = '4';

        $this->post('/skrining/kirim', $answers)
            ->assertRedirect('/skrining/kuesioner')
            ->assertSessionHasErrors('answers.BA01');
    }

    public function test_student_result_is_stored_only_after_explicit_consent(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($student);

        $this->startScreening(true);
        $this->post('/skrining/kirim', $this->screeningAnswers())->assertSessionHasNoErrors();

        $result = ScreeningResult::query()->where('user_id', $student->id)->latest('id')->first();
        $this->assertNotNull($result);
        $this->assertSame(16, $result->answers()->count());
        $this->assertNull($result->score);
        $this->assertSame('ACADEMIC_STRESS_SCALE', $result->meta['result_type']);
        $this->assertArrayNotHasKey('overall_percentage', $result->meta);
        $this->assertSame('moderate', $result->meta['supplementary_impact']['value']);
        $this->assertTrue($result->meta['follow_up_guidance']['consultation_recommended']);
        $this->assertDatabaseHas('screening_consents', [
            'screening_result_id' => $result->id,
            'consent_type' => 'participation',
            'consent_given' => 1,
        ]);
        $this->assertDatabaseHas('screening_consents', [
            'screening_result_id' => $result->id,
            'consent_type' => 'storage',
            'consent_given' => 1,
        ]);
    }

    public function test_saved_result_shows_action_recommendation_based_on_the_separate_impact_question(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($student);

        $this->startScreening(true);
        $this->post('/skrining/kirim', $this->screeningAnswers())
            ->assertRedirect();

        $result = ScreeningResult::query()->where('user_id', $student->id)->latest('id')->firstOrFail();
        $this->get('/skrining/hasil/'.$result->id)
            ->assertOk()
            ->assertSee('Dampak terhadap kegiatan sehari-hari')
            ->assertSee('Cukup mengganggu')
            ->assertSee('Rekomendasi tindak lanjut')
            ->assertSee('Buat janji untuk berdiskusi dengan Guru BK atau wali kelas.')
            ->assertSee('Lihat Jadwal dan Buat Janji BK')
            ->assertDontSee('Stres berat');
    }

    public function test_student_who_declines_storage_does_not_create_permanent_records(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($student);
        $resultsBefore = DB::table('screening_results')->count();
        $answersBefore = DB::table('screening_answers')->count();

        $this->startScreening(false);
        $this->post('/skrining/kirim', $this->screeningAnswers())
            ->assertRedirect('/skrining/hasil/demo');

        $this->assertSame($resultsBefore, DB::table('screening_results')->count());
        $this->assertSame($answersBefore, DB::table('screening_answers')->count());
    }

    public function test_non_student_account_cannot_store_a_screening_result(): void
    {
        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $this->actingAs($teacher);
        $resultsBefore = DB::table('screening_results')->count();

        $this->startScreening(true);
        $this->post('/skrining/kirim', $this->screeningAnswers())
            ->assertRedirect('/skrining/hasil/demo');

        $this->assertSame($resultsBefore, DB::table('screening_results')->count());
    }

    public function test_student_can_open_own_saved_result(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $answers = array_fill_keys(
            array_column(app(ScreeningService::class)->getQuestions(), 'code'),
            'KK'
        );
        $result = ScreeningResult::create([
            'user_id' => $student->id,
            'score' => null,
            'interpretation' => null,
            'meta' => app(ScreeningService::class)->calculateScreeningSummary($answers),
            'completed_at' => now(),
        ]);
        foreach ($answers as $code => $answer) {
            $result->answers()->create([
                'question_code' => $code,
                'answer' => (string) $answer,
            ]);
        }
        $result->consents()->create([
            'user_id' => $student->id,
            'consent_type' => 'storage',
            'consent_given' => true,
            'consented_at' => now(),
        ]);

        $this->actingAs($student)
            ->get('/skrining/hasil/'.$result->id)
            ->assertOk()
            ->assertSee('Ringkasan Hasil Skrining');
    }


    public function test_old_demo_result_remains_readable_after_the_instrument_change(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $service = app(ScreeningService::class);
        $answers = array_fill_keys(
            array_column($service->getLegacyDemoQuestions(), 'code'),
            '2'
        );

        $result = ScreeningResult::create([
            'user_id' => $student->id,
            'score' => 50,
            'interpretation' => null,
            'meta' => $service->calculateDemoResult($answers),
            'completed_at' => now(),
        ]);

        foreach ($answers as $code => $answer) {
            $result->answers()->create([
                'question_code' => $code,
                'answer' => $answer,
            ]);
        }

        $result->consents()->create([
            'user_id' => $student->id,
            'consent_type' => 'storage',
            'consent_given' => true,
            'consented_at' => now(),
        ]);

        $this->actingAs($student)
            ->get('/skrining/hasil/'.$result->id)
            ->assertOk()
            ->assertSee('Hasil Skrining DEMO (versi sebelumnya)')
            ->assertSee('Kurang Sesuai')
            ->assertSee('Contoh DEMO: Saya merasa ada harapan akademik dari orang tua yang perlu saya penuhi.');
    }

    public function test_temporary_result_is_unavailable_without_a_matching_session(): void
    {
        $this->get('/skrining/hasil/demo')->assertNotFound();
    }

    public function test_student_cannot_open_another_students_saved_result(): void
    {
        $owner = User::factory()->create(['role' => 'siswa']);
        $otherStudent = User::factory()->create(['role' => 'siswa']);

        $result = ScreeningResult::create([
            'user_id' => $owner->id,
            'score' => 50,
            'interpretation' => null,
            'meta' => app(ScreeningService::class)->calculateScreeningSummary(array_fill_keys(
                array_column(app(ScreeningService::class)->getQuestions(), 'code'),
                'KK'
            )),
            'completed_at' => now(),
        ]);

        $this->actingAs($otherStudent)
            ->get('/skrining/hasil/'.$result->id)
            ->assertNotFound();
    }

    public function test_saved_result_requires_authenticated_student(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $result = ScreeningResult::create([
            'user_id' => $student->id,
            'score' => 50,
            'interpretation' => null,
            'meta' => [],
            'completed_at' => now(),
        ]);

        $this->get('/skrining/hasil/'.$result->id)->assertNotFound();
    }

    public function test_questionnaire_requires_participation_consent(): void
    {
        $this->get('/skrining/kuesioner')->assertRedirect('/skrining/info');
    }

    public function test_published_indonesian_scale_contains_sixteen_items_in_four_initial_components(): void
    {
        $service = app(ScreeningService::class);
        $questions = $service->getQuestions();
        $dimensions = array_unique(array_column($questions, 'dimension'));

        $this->assertCount(16, $questions);
        $this->assertEqualsCanonicalizing([
            'academic_load',
            'classmate_interaction',
            'family_pressure',
            'future_perspective',
        ], $dimensions);
        $this->assertSame([
            'TP', 'SJ', 'KK', 'Ser', 'Sel',
        ], array_keys($service->getResponseOptions()));

        $countsByDimension = [];
        foreach ($questions as $question) {
            $countsByDimension[$question['dimension']] = ($countsByDimension[$question['dimension']] ?? 0) + 1;
        }
        $this->assertSame([
            'academic_load' => 5,
            'classmate_interaction' => 3,
            'family_pressure' => 4,
            'future_perspective' => 4,
        ], $countsByDimension);

        $item12 = collect($questions)->firstWhere('number', 12);
        $this->assertSame(
            'Saya merasa tidak nyaman ketika orang tua saya bertengkar hasil belajar saya yang buruk.',
            $item12['text']
        );
    }

    public function test_descriptive_profile_identifies_the_most_frequent_component_without_severity_labels(): void
    {
        $service = app(ScreeningService::class);
        $answers = array_fill_keys(array_column($service->getQuestions(), 'code'), 'TP');

        // Three of five academic-load items are frequent; two of three classmate
        // items are frequent. The latter has the higher proportion despite a lower
        // raw count, so components with different item counts are compared fairly.
        $answers = array_replace($answers, [
            'BA01' => 'Ser', 'BA02' => 'Sel', 'BA03' => 'Ser',
            'TS01' => 'Ser', 'TS02' => 'Sel',
            'KF01' => 'Ser', 'KF02' => 'TP', 'KF03' => 'TP', 'KF04' => 'TP',
        ]);

        $profile = $service->buildDescriptiveProfile($answers);

        $this->assertTrue($profile['has_dominant']);
        $this->assertCount(1, $profile['dominant_dimensions']);
        $this->assertSame('classmate_interaction', $profile['dominant_dimensions'][0]['key']);
        $this->assertSame(2, $profile['dominant_dimensions'][0]['frequent_response_count']);
        $this->assertSame(67, $profile['dominant_dimensions'][0]['frequent_response_percentage']);
        $this->assertStringContainsString('Stres dalam Interaksi dengan Teman Sekelas', $profile['message']);
        $this->assertStringContainsString('bukan diagnosis', $profile['message']);
        $this->assertStringContainsString('bukan pedoman skoring resmi', $profile['disclaimer']);
    }

    public function test_descriptive_profile_does_not_select_a_component_when_no_frequent_answers_exist(): void
    {
        $service = app(ScreeningService::class);
        $answers = array_fill_keys(array_column($service->getQuestions(), 'code'), 'KK');

        $profile = $service->buildDescriptiveProfile($answers);

        $this->assertFalse($profile['has_dominant']);
        $this->assertSame([], $profile['dominant_dimensions']);
        $this->assertStringContainsString('belum tampak satu sumber tekanan', $profile['message']);
    }

    public function test_guest_result_displays_a_descriptive_source_profile_not_a_stress_level(): void
    {
        $answers = array_fill_keys(array_column(app(ScreeningService::class)->getQuestions(), 'code'), 'TP');
        $answers = array_replace($answers, [
            'KF01' => 'Ser', 'KF02' => 'Sel', 'KF03' => 'Ser',
        ]);

        $this->startScreening(false);
        $this->post('/skrining/kirim', ['answers' => $answers, 'daily_impact' => 'moderate'])
            ->assertRedirect('/skrining/hasil/demo');

        $this->get('/skrining/hasil/demo')
            ->assertOk()
            ->assertSee('Pola sumber tekanan akademik yang paling menonjol')
            ->assertSee('Pola jawaban paling menonjol pada aspek Tekanan dari Anggota Keluarga.')
            ->assertSee('3 dari 4 butir')
            ->assertSee('bukan pedoman skoring resmi artikel')
            ->assertDontSee('Stres berat')
            ->assertDontSee('Stres sedang');
    }

    public function test_instrument_summary_is_descriptive_and_does_not_assign_a_score_or_category(): void
    {
        $service = app(ScreeningService::class);
        $result = $service->calculateScreeningSummary(array_fill_keys(
            array_column($service->getQuestions(), 'code'),
            'KK'
        ));

        $this->assertSame('ACADEMIC_STRESS_SCALE', $result['result_type']);
        $this->assertArrayNotHasKey('total_score', $result);
        $this->assertArrayNotHasKey('overall_percentage', $result);
        $this->assertArrayNotHasKey('level', $result);
        $this->assertNull($result['interpretation']);
        $this->assertSame(16, $result['answered_items']);
        $this->assertSame(16, $result['response_distribution']['KK']);
    }
}
