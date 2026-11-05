<?php

namespace Tests\Feature;

use App\Models\ScreeningResult;
use App\Models\ScreeningResultShare;
use App\Models\User;
use App\Services\ScreeningService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ScreeningSupportPathwayTest extends TestCase
{
    use DatabaseTransactions;

    private function beginScreening(bool $storageConsent): void
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

    /** @return array<string, string> */
    private function answers(): array
    {
        return [
            'BA01' => 'TP', 'BA02' => 'KK', 'BA03' => 'KK', 'BA04' => 'Ser', 'BA05' => 'SJ',
            'TS01' => 'TP', 'TS02' => 'SJ', 'TS03' => 'KK',
            'KF01' => 'KK', 'KF02' => 'Ser', 'KF03' => 'SJ', 'KF04' => 'TP',
            'MD01' => 'KK', 'MD02' => 'Ser', 'MD03' => 'SJ', 'MD04' => 'TP',
        ];
    }

    public function test_daily_impact_maps_to_three_support_pathways_without_stress_severity_labels(): void
    {
        $service = app(ScreeningService::class);
        $profile = $service->buildDescriptiveProfile(array_fill_keys(
            array_column($service->getQuestions(), 'code'),
            'TP'
        ));

        $selfSupport = $service->buildFollowUpGuidance('none', $profile);
        $lightSupport = $service->buildFollowUpGuidance('slight', $profile);
        $bkSupport = $service->buildFollowUpGuidance('moderate', $profile);
        $professionalSupport = $service->buildFollowUpGuidance('significant', $profile);

        $this->assertSame('self_support', $selfSupport['support_level']);
        $this->assertSame('self_support', $lightSupport['support_level']);
        $this->assertSame('school_counseling', $bkSupport['support_level']);
        $this->assertTrue($bkSupport['consultation_recommended']);
        $this->assertSame('professional_assessment', $professionalSupport['support_level']);
        $this->assertTrue($professionalSupport['consultation_recommended']);
        $this->assertTrue($professionalSupport['professional_assessment_recommended']);
        $this->assertStringContainsString('Puskesmas', implode(' ', $professionalSupport['steps']));
        $this->assertStringContainsString('psikiater', implode(' ', $professionalSupport['steps']));
        $this->assertStringNotContainsString('stres berat', strtolower($professionalSupport['message']));
    }

    public function test_daily_impact_is_persisted_and_recommendation_is_rebuilt_on_saved_result(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($student);
        $this->beginScreening(true);

        $this->post('/skrining/kirim', [
            'answers' => $this->answers(),
            'daily_impact' => 'significant',
        ])->assertRedirect();

        $result = ScreeningResult::query()
            ->where('user_id', $student->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('significant', $result->meta['daily_impact']);
        $this->assertSame('significant', $result->meta['supplementary_impact']['value']);
        $this->assertSame('professional_assessment', $result->meta['follow_up_guidance']['support_level']);

        $this->get('/skrining/hasil/'.$result->id)
            ->assertOk()
            ->assertSee('Dampak terhadap kegiatan sehari-hari')
            ->assertSee('Sangat mengganggu')
            ->assertSee('Pemeriksaan profesional')
            ->assertSee('Puskesmas atau fasilitas pelayanan kesehatan')
            ->assertSee('Kebutuhan rujukan lanjutan')
            ->assertDontSee('Stres berat')
            ->assertDontSee('Stres sedang');

        $teacher = User::factory()->create(['role' => 'guru_bk']);
        $share = ScreeningResultShare::create([
            'screening_result_id' => $result->id,
            'student_id' => $student->id,
            'bk_teacher_id' => $teacher->id,
            'consent_version' => '2026-11-02-v1',
            'consented_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('bk.shared-results.show', $share->id))
            ->assertOk()
            ->assertSee('Pemeriksaan profesional')
            ->assertSee('Sangat mengganggu')
            ->assertSee('Puskesmas atau fasilitas pelayanan kesehatan');
    }

    public function test_moderate_daily_impact_recommends_school_counseling(): void
    {
        $service = app(ScreeningService::class);
        $profile = $service->buildDescriptiveProfile(array_fill_keys(
            array_column($service->getQuestions(), 'code'),
            'TP'
        ));

        $guidance = $service->buildFollowUpGuidance('moderate', $profile);

        $this->assertSame('school_counseling', $guidance['support_level']);
        $this->assertSame('Konsultasi Guru BK', $guidance['support_label']);
        $this->assertTrue($guidance['consultation_recommended']);
        $this->assertFalse($guidance['professional_assessment_recommended']);
    }
}
