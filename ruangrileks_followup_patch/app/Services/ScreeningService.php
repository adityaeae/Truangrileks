<?php

namespace App\Services;

use App\Models\ScreeningAnswer;
use App\Models\ScreeningConsent;
use App\Models\ScreeningResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScreeningService
{
    public const INSTRUMENT_KEY = 'sinadia_gandey_2024';

    public const INSTRUMENT_TITLE = 'Skala Psikologi Stres Akademik pada Siswa SMA';

    public const INSTRUMENT_CITATION = 'Sinadia, A. R., & Gandey, G. G. G. (2024). Pembuktian Validitas dan Estimasi Reliabilitas Skala Psikologi Stres Akademik pada Siswa SMA. SUMIKOLAH: Jurnal Ilmu Pendidikan, 2(2), 127–138.';

    /**
     * Indonesian 16-item scale transcribed verbatim from Table 1, page 129,
     * of Sinadia & Gandey (2024). The item codes are internal application IDs;
     * they are not codes assigned by the article authors.
     *
     * @return array<int, array{code:string, number:int, text:string, dimension:string}>
     */
    public function getQuestions(): array
    {
        return [
            ['code' => 'BA01', 'number' => 1, 'text' => 'Saya merasa terlalu banyak tugas yang diberikan guru.', 'dimension' => 'academic_load'],
            ['code' => 'BA02', 'number' => 2, 'text' => 'Saya mengalami kesulitan untuk menyelesaikan tugas-tugas yang diberikan karena waktu yang diberikan guru tidak cukup.', 'dimension' => 'academic_load'],
            ['code' => 'BA03', 'number' => 3, 'text' => 'Saya merasa kesulitan untuk memenuhi kewajiban saya sebagai seorang siswa karena terlalu banyak tanggung jawab yang diberikan guru.', 'dimension' => 'academic_load'],
            ['code' => 'BA04', 'number' => 4, 'text' => 'Saya merasa tugas yang diberikan oleh guru tidak sesuai dengan kemampuan saya.', 'dimension' => 'academic_load'],
            ['code' => 'BA05', 'number' => 5, 'text' => 'Saya merasa tertekan ketika guru memberi tahu saya tentang tugas saya.', 'dimension' => 'academic_load'],

            ['code' => 'TS01', 'number' => 6, 'text' => 'Saya mengalami kesulitan ketika membuat tugas bersama dengan teman kelas.', 'dimension' => 'classmate_interaction'],
            ['code' => 'TS02', 'number' => 7, 'text' => 'Saya merasa teman sekelas saya berpikir bahwa saya bukan siswa yang baik.', 'dimension' => 'classmate_interaction'],
            ['code' => 'TS03', 'number' => 8, 'text' => 'Saya merasa tidak nyaman ketika memiliki masalah dengan teman sekelas saya.', 'dimension' => 'classmate_interaction'],

            ['code' => 'KF01', 'number' => 9, 'text' => 'Saya merasa tertekan karena keluarga saya menuntut saya untuk mendapatkan nilai yang bagus.', 'dimension' => 'family_pressure'],
            ['code' => 'KF02', 'number' => 10, 'text' => 'Saya merasa khawatir ketika orang tua saya menanyakan apakah saya sudah mengerjakan PR.', 'dimension' => 'family_pressure'],
            ['code' => 'KF03', 'number' => 11, 'text' => 'Saya merasa takut ketika orang tua saya bertanya tentang nilai saya.', 'dimension' => 'family_pressure'],
            ['code' => 'KF04', 'number' => 12, 'text' => 'Saya merasa tidak nyaman ketika orang tua saya bertengkar hasil belajar saya yang buruk.', 'dimension' => 'family_pressure'],

            ['code' => 'MD01', 'number' => 13, 'text' => 'Saya merasa khawatir akan masa depan saya.', 'dimension' => 'future_perspective'],
            ['code' => 'MD02', 'number' => 14, 'text' => 'Saya merasa takut jika saya tidak naik kelas.', 'dimension' => 'future_perspective'],
            ['code' => 'MD03', 'number' => 15, 'text' => 'Saya merasa kecewa terhadap diri saya ketika saya mendapatkan nilai yang jelek.', 'dimension' => 'future_perspective'],
            ['code' => 'MD04', 'number' => 16, 'text' => 'Saya tidak merasa puas dengan hasil belajar saya.', 'dimension' => 'future_perspective'],
        ];
    }

    /**
     * The response options reproduce the frequency labels in Table 1 of the paper.
     * Codes are storage values only. This application does not assign numeric
     * weights, totals, cut-offs, or psychological severity categories.
     *
     * @return array<string, string>
     */
    public function getResponseOptions(): array
    {
        return [
            'TP' => 'Tidak pernah',
            'SJ' => 'Sangat jarang',
            'KK' => 'Kadang-kadang',
            'Ser' => 'Sering',
            'Sel' => 'Selalu',
        ];
    }

    /** @return array<string, string> */
    public function getDimensions(): array
    {
        return [
            'academic_load' => 'Stres karena Beban Akademik yang Berlebihan',
            'classmate_interaction' => 'Stres dalam Interaksi dengan Teman Sekelas',
            'family_pressure' => 'Tekanan dari Anggota Keluarga',
            'future_perspective' => 'Stres atas Perspektif Masa Depan',
        ];
    }

    /**
     * A separate self-reported impact question used only to tailor support
     * recommendations. It is not one of the 16 items in the published scale.
     *
     * @return array<string, string>
     */
    public function getDailyImpactOptions(): array
    {
        return [
            'none' => 'Tidak mengganggu',
            'slight' => 'Sedikit mengganggu',
            'moderate' => 'Cukup mengganggu',
            'significant' => 'Sangat mengganggu',
        ];
    }

    public function getDailyImpactQuestion(): string
    {
        return 'Seberapa besar tekanan akademik yang kamu alami mengganggu kegiatan belajar dan aktivitas sehari-hari?';
    }

    /**
     * Builds practical, non-diagnostic recommendations from a student's own
     * report about day-to-day impact and the descriptive profile. The response
     * does not classify stress severity and is not part of the validated scale.
     *
     * @param array<string, mixed> $descriptiveProfile
     * @return array<string, mixed>|null
     */
    public function buildFollowUpGuidance(?string $dailyImpact, array $descriptiveProfile): ?array
    {
        $options = $this->getDailyImpactOptions();
        if ($dailyImpact === null || ! array_key_exists($dailyImpact, $options)) {
            return null;
        }

        $dominantDimensions = $descriptiveProfile['dominant_dimensions'] ?? [];
        $dimensionAdvice = [
            'academic_load' => 'Bicarakan beban tugas, tenggat, dan prioritas belajar dengan guru atau wali kelas bila terasa sulit dikelola.',
            'classmate_interaction' => 'Jika ada kesulitan dalam kerja kelompok atau hubungan dengan teman, pertimbangkan berdiskusi dengan wali kelas atau Guru BK.',
            'family_pressure' => 'Bila memungkinkan, sampaikan kebutuhan dan kesulitan belajarmu kepada keluarga dengan didampingi orang dewasa yang kamu percaya.',
            'future_perspective' => 'Coba bicarakan kekhawatiran tentang nilai, kenaikan kelas, atau rencana masa depan dengan Guru BK dan susun target kecil yang realistis.',
        ];

        $steps = [];
        $consultationRecommended = false;

        if ($dailyImpact === 'none') {
            $message = 'Kamu menyatakan bahwa tekanan akademik belum mengganggu kegiatan belajar dan aktivitas sehari-hari. Tetap perhatikan keseimbangan antara belajar, istirahat, dan kegiatan lain.';
            $steps = [
                'Pertahankan pola belajar dan waktu istirahat yang seimbang.',
                'Kenali lebih awal situasi yang biasanya membuatmu merasa tertekan.',
                'Bicarakan dengan orang tepercaya apabila suatu saat kamu membutuhkan dukungan.',
            ];
        } elseif ($dailyImpact === 'slight') {
            $message = 'Kamu menyatakan bahwa tekanan akademik sedikit mengganggu kegiatan belajar atau aktivitas sehari-hari. Beberapa penyesuaian sederhana dapat dicoba dan dievaluasi kembali.';
            $steps = [
                'Bagi tugas besar menjadi beberapa langkah kecil dan susun prioritas.',
                'Sisihkan waktu istirahat di antara kegiatan belajar.',
                'Ceritakan situasi yang kamu alami kepada orang tepercaya bila diperlukan.',
            ];
        } elseif ($dailyImpact === 'moderate') {
            $message = 'Kamu menyatakan bahwa tekanan akademik cukup mengganggu kegiatan belajar atau aktivitas sehari-hari. Sebaiknya kamu berdiskusi dengan Guru BK atau wali kelas untuk memahami situasi dan mencari dukungan yang sesuai.';
            $steps = [
                'Buat janji untuk berdiskusi dengan Guru BK atau wali kelas.',
                'Catat situasi yang paling mengganggu agar lebih mudah diceritakan saat konsultasi.',
                'Susun kembali beban dan target belajar bersama orang dewasa tepercaya.',
            ];
            $consultationRecommended = true;
        } else {
            $message = 'Kamu menyatakan bahwa tekanan akademik sangat mengganggu kegiatan belajar atau aktivitas sehari-hari. Sebaiknya segera sampaikan hal ini kepada Guru BK, wali kelas, atau orang dewasa yang kamu percaya agar kamu mendapat pendampingan.';
            $steps = [
                'Hubungi Guru BK atau orang dewasa tepercaya untuk meminta waktu berdiskusi.',
                'Jika sulit menghubungi Guru BK, mintalah bantuan wali kelas atau keluarga untuk mendampingi.',
                'Tidak perlu menghadapi situasi ini sendirian; ceritakan kebutuhan dukunganmu dengan cara yang terasa aman.',
            ];
            $consultationRecommended = true;
        }

        foreach ($dominantDimensions as $dimension) {
            $key = $dimension['key'] ?? null;
            if ($key && isset($dimensionAdvice[$key])) {
                $steps[] = $dimensionAdvice[$key];
            }
        }

        return [
            'title' => 'Rekomendasi tindak lanjut',
            'impact_question' => $this->getDailyImpactQuestion(),
            'impact_value' => $dailyImpact,
            'impact_label' => $options[$dailyImpact],
            'message' => $message,
            'steps' => array_values(array_unique($steps)),
            'consultation_recommended' => $consultationRecommended,
            'consultation_label' => $consultationRecommended
                ? 'Pertimbangkan untuk menghubungi Guru BK dan membuat janji konsultasi.'
                : 'Kamu dapat menghubungi Guru BK kapan pun ketika membutuhkan teman berdiskusi.',
            'disclaimer' => 'Rekomendasi ini didasarkan pada jawaban tambahan tentang dampak aktivitas dan pola respons kuesioner. Ini bukan penetapan tingkat keparahan stres atau diagnosis psikologis.',
        ];
    }

    /**
     * Builds a descriptive response-frequency summary only. The source article
     * does not provide a scoring key or cut-off values, so no total score,
     * percentage, severity level, or diagnostic interpretation is calculated.
     *
     * @param array<string, string> $answers
     * @return array<string, mixed>
     */
    public function calculateScreeningSummary(array $answers, ?string $dailyImpact = null): array
    {
        $responseOptions = $this->getResponseOptions();
        $dimensions = [];
        foreach ($this->getDimensions() as $key => $label) {
            $dimensions[$key] = [
                'label' => $label,
                'item_count' => 0,
                'response_counts' => array_fill_keys(array_keys($responseOptions), 0),
            ];
        }

        $overallCounts = array_fill_keys(array_keys($responseOptions), 0);

        foreach ($this->getQuestions() as $question) {
            $response = (string) ($answers[$question['code']] ?? '');
            if (! isset($responseOptions[$response])) {
                continue;
            }

            $dimension = $question['dimension'];
            $dimensions[$dimension]['item_count']++;
            $dimensions[$dimension]['response_counts'][$response]++;
            $overallCounts[$response]++;
        }

        $descriptiveProfile = $this->buildDescriptiveProfileFromCounts($dimensions);
        $impactOptions = $this->getDailyImpactOptions();
        $supplementaryImpact = $dailyImpact !== null && isset($impactOptions[$dailyImpact])
            ? [
                'question' => $this->getDailyImpactQuestion(),
                'value' => $dailyImpact,
                'label' => $impactOptions[$dailyImpact],
                'is_part_of_instrument' => false,
            ]
            : null;

        return [
            'result_type' => 'ACADEMIC_STRESS_SCALE',
            'instrument_key' => self::INSTRUMENT_KEY,
            'instrument_title' => self::INSTRUMENT_TITLE,
            'instrument_citation' => self::INSTRUMENT_CITATION,
            'total_items' => count($this->getQuestions()),
            'answered_items' => count($answers),
            'response_distribution' => $overallCounts,
            'dimensions' => $dimensions,
            'descriptive_profile' => $descriptiveProfile,
            'supplementary_impact' => $supplementaryImpact,
            'follow_up_guidance' => $this->buildFollowUpGuidance($dailyImpact, $descriptiveProfile),
            'interpretation' => null,
            'scoring_note' => 'Artikel sumber tidak menetapkan pembobotan skor total maupun nilai ambang kategori. Ringkasan profil di aplikasi hanya membandingkan proporsi butir yang dijawab “Sering” atau “Selalu” pada empat komponen awal. Perbandingan ini bersifat deskriptif, bukan pedoman skoring resmi instrumen.',
            'disclaimer' => 'Hasil ini merupakan ringkasan jawaban skrining, bukan diagnosis dan bukan penetapan tingkat tekanan akademik. Gunakan bersama konteks siswa dan pendampingan yang sesuai.',
        ];
    }

    /**
     * Creates a non-diagnostic descriptive profile by comparing, for each
     * initial component, the proportion of items answered "Sering" or "Selalu".
     * This is an application-level summary of response patterns, not a scoring
     * key, severity cut-off, or interpretation published by the scale authors.
     *
     * @param array<string, string> $answers
     * @return array<string, mixed>
     */
    public function buildDescriptiveProfile(array $answers): array
    {
        $dimensions = [];
        foreach ($this->getDimensions() as $key => $label) {
            $dimensions[$key] = [
                'label' => $label,
                'item_count' => 0,
                'response_counts' => array_fill_keys(array_keys($this->getResponseOptions()), 0),
            ];
        }

        foreach ($this->getQuestions() as $question) {
            $answer = (string) ($answers[$question['code']] ?? '');
            if (! array_key_exists($answer, $this->getResponseOptions())) {
                continue;
            }

            $dimensionKey = $question['dimension'];
            $dimensions[$dimensionKey]['item_count']++;
            $dimensions[$dimensionKey]['response_counts'][$answer]++;
        }

        return $this->buildDescriptiveProfileFromCounts($dimensions);
    }

    /**
     * @param array<string, array{label:string,item_count:int,response_counts:array<string,int>}> $dimensions
     * @return array<string, mixed>
     */
    private function buildDescriptiveProfileFromCounts(array $dimensions): array
    {
        $descriptions = [
            'academic_load' => 'beban tugas, keterbatasan waktu pengerjaan, dan tanggung jawab sekolah',
            'classmate_interaction' => 'interaksi dan hubungan dengan teman sekelas',
            'family_pressure' => 'tuntutan keluarga terkait nilai dan pekerjaan rumah',
            'future_perspective' => 'kekhawatiran mengenai masa depan dan hasil belajar',
        ];

        $profiles = [];
        $highestProportion = 0.0;

        foreach ($dimensions as $key => $dimension) {
            $itemCount = (int) ($dimension['item_count'] ?? 0);
            $responseCounts = $dimension['response_counts'] ?? [];
            $frequentCount = (int) ($responseCounts['Ser'] ?? 0) + (int) ($responseCounts['Sel'] ?? 0);
            $proportion = $itemCount > 0 ? $frequentCount / $itemCount : 0.0;

            $profiles[$key] = [
                'key' => $key,
                'label' => $dimension['label'] ?? ($this->getDimensions()[$key] ?? $key),
                'description' => $descriptions[$key] ?? 'pengalaman akademik yang tercakup dalam komponen ini',
                'item_count' => $itemCount,
                'frequent_response_count' => $frequentCount,
                'frequent_response_percentage' => (int) round($proportion * 100),
                'proportion' => $proportion,
            ];

            if ($proportion > $highestProportion) {
                $highestProportion = $proportion;
            }
        }

        $dominant = [];
        if ($highestProportion > 0) {
            foreach ($profiles as $profile) {
                if (abs($profile['proportion'] - $highestProportion) < 0.000001) {
                    $dominant[] = [
                        'key' => $profile['key'],
                        'label' => $profile['label'],
                        'description' => $profile['description'],
                        'item_count' => $profile['item_count'],
                        'frequent_response_count' => $profile['frequent_response_count'],
                        'frequent_response_percentage' => $profile['frequent_response_percentage'],
                    ];
                }
            }
        }

        if ($dominant === []) {
            $message = 'Pada hasil pengisian ini, belum tampak satu sumber tekanan akademik yang menonjol berdasarkan pilihan “Sering” atau “Selalu”. Respons tetap perlu dipahami bersama konteks dan pengalaman siswa.';
        } elseif (count($dominant) === 1) {
            $item = $dominant[0];
            $message = 'Pola jawaban paling menonjol pada aspek '.$item['label'].'. Pilihan “Sering” atau “Selalu” muncul pada '.$item['frequent_response_count'].' dari '.$item['item_count'].' butir dalam aspek ini. Jawaban tersebut berkaitan dengan '.$item['description'].'. Ini merupakan ringkasan pola jawaban, bukan diagnosis atau ukuran tingkat keparahan stres.';
        } else {
            $labels = array_column($dominant, 'label');
            $joinedLabels = count($labels) === 2
                ? $labels[0].' dan '.$labels[1]
                : implode(', ', array_slice($labels, 0, -1)).', serta '.$labels[count($labels) - 1];
            $message = 'Pola jawaban menunjukkan beberapa aspek yang sama-sama menonjol, yaitu '.$joinedLabels.'. Pada aspek-aspek tersebut, pilihan “Sering” atau “Selalu” memiliki proporsi yang sama. Ini merupakan ringkasan pola jawaban, bukan diagnosis atau ukuran tingkat keparahan stres.';
        }

        return [
            'title' => 'Pola sumber tekanan akademik yang paling menonjol',
            'method' => 'Perbandingan proporsi butir dengan jawaban “Sering” atau “Selalu” pada empat komponen awal instrumen.',
            'dominant_dimensions' => $dominant,
            'dimensions' => array_values(array_map(static function (array $profile): array {
                unset($profile['proportion']);
                return $profile;
            }, $profiles)),
            'has_dominant' => $dominant !== [],
            'message' => $message,
            'disclaimer' => 'Profil ini adalah ringkasan deskriptif yang dibuat aplikasi dari frekuensi jawaban. Ini bukan pedoman skoring resmi artikel, bukan kategori rendah/sedang/tinggi, dan bukan diagnosis.',
        ];
    }

    /**
     * Permanently stores answers only for an authenticated student with explicit
     * storage consent. Guests and other roles receive an aggregate session summary;
     * their raw answers are not persisted.
     *
     * @param array<string, string> $answers
     */
    public function storeScreeningResult(
        Request $request,
        ?User $user,
        array $answers,
        bool $consentStorage,
        ?string $dailyImpact = null
    ): array|ScreeningResult {
        $summary = $this->calculateScreeningSummary($answers, $dailyImpact);

        if (! $consentStorage || ! $user || $user->role !== 'siswa') {
            return $summary;
        }

        return DB::transaction(function () use ($user, $answers, $summary): ScreeningResult {
            $screeningResult = ScreeningResult::create([
                'user_id' => $user->id,
                'score' => null,
                'interpretation' => null,
                'meta' => $summary,
                'completed_at' => now(),
            ]);

            foreach ($answers as $code => $value) {
                ScreeningAnswer::create([
                    'screening_result_id' => $screeningResult->id,
                    'question_code' => $code,
                    'answer' => (string) $value,
                ]);
            }

            foreach (['participation', 'storage'] as $type) {
                ScreeningConsent::create([
                    'user_id' => $user->id,
                    'screening_result_id' => $screeningResult->id,
                    'consent_type' => $type,
                    'consent_given' => true,
                    'consented_at' => now(),
                ]);
            }

            return $screeningResult;
        });
    }

    /**
     * Legacy DEMO questions are retained only so older stored records remain
     * readable. They are not used for new questionnaire submissions.
     *
     * @return array<int, array{code:string, text:string, dimension:string}>
     */
    public function getLegacyDemoQuestions(): array
    {
        return [
            ['code' => 'P1', 'text' => 'Contoh DEMO: Saya merasa ada harapan akademik dari orang tua yang perlu saya penuhi.', 'dimension' => 'parent_related_pressure'],
            ['code' => 'P2', 'text' => 'Contoh DEMO: Saya merasa perlu memperoleh nilai yang sesuai dengan harapan keluarga.', 'dimension' => 'parent_related_pressure'],
            ['code' => 'P3', 'text' => 'Contoh DEMO: Pembicaraan mengenai prestasi sekolah di rumah terkadang membuat saya merasa tertekan.', 'dimension' => 'parent_related_pressure'],
            ['code' => 'P4', 'text' => 'Contoh DEMO: Saya khawatir mengecewakan orang tua apabila hasil belajar saya tidak sesuai harapan.', 'dimension' => 'parent_related_pressure'],
            ['code' => 'I1', 'text' => 'Contoh DEMO: Saya menetapkan target akademik yang membuat saya merasa tertekan.', 'dimension' => 'internal_related_pressure'],
            ['code' => 'I2', 'text' => 'Contoh DEMO: Saya merasa harus selalu memperoleh hasil akademik yang tinggi.', 'dimension' => 'internal_related_pressure'],
            ['code' => 'I3', 'text' => 'Contoh DEMO: Saya menyalahkan diri sendiri ketika hasil belajar tidak sesuai target pribadi.', 'dimension' => 'internal_related_pressure'],
            ['code' => 'I4', 'text' => 'Contoh DEMO: Saya merasa tertinggal ketika pencapaian akademik saya tidak sesuai harapan pribadi.', 'dimension' => 'internal_related_pressure'],
            ['code' => 'F1', 'text' => 'Contoh DEMO: Saya merasa tertekan ketika teman memperoleh hasil akademik yang lebih baik.', 'dimension' => 'friend_related_pressure'],
            ['code' => 'F2', 'text' => 'Contoh DEMO: Saya merasa harus mengikuti pencapaian akademik teman-teman.', 'dimension' => 'friend_related_pressure'],
            ['code' => 'F3', 'text' => 'Contoh DEMO: Perbandingan nilai di lingkungan pertemanan membuat saya merasa tertekan.', 'dimension' => 'friend_related_pressure'],
            ['code' => 'F4', 'text' => 'Contoh DEMO: Saya khawatir dianggap kurang mampu ketika hasil belajar saya berbeda dari teman.', 'dimension' => 'friend_related_pressure'],
            ['code' => 'T1', 'text' => 'Contoh DEMO: Saya merasa tertekan untuk memenuhi harapan akademik guru.', 'dimension' => 'teacher_related_pressure'],
            ['code' => 'T2', 'text' => 'Contoh DEMO: Tenggat tugas yang diberikan guru terkadang membuat saya merasa tertekan.', 'dimension' => 'teacher_related_pressure'],
            ['code' => 'T3', 'text' => 'Contoh DEMO: Saya khawatir terhadap penilaian guru atas hasil belajar saya.', 'dimension' => 'teacher_related_pressure'],
            ['code' => 'T4', 'text' => 'Contoh DEMO: Banyaknya tuntutan akademik dari guru membuat saya merasa terbebani.', 'dimension' => 'teacher_related_pressure'],
        ];
    }

    /** @return array<string, string> */
    public function getLegacyDemoDimensions(): array
    {
        return [
            'parent_related_pressure' => 'Parent-Related Pressure (Tekanan terkait orang tua)',
            'internal_related_pressure' => 'Internal-Related Pressure (Tekanan dari diri sendiri)',
            'friend_related_pressure' => 'Friend-Related Pressure (Tekanan dari teman)',
            'teacher_related_pressure' => 'Teacher-Related Pressure (Tekanan terkait guru)',
        ];
    }

    /** @return array<string, string> */
    public function getLegacyDemoResponseOptions(): array
    {
        return [
            '1' => 'Tidak Sesuai',
            '2' => 'Kurang Sesuai',
            '3' => 'Sesuai',
            '4' => 'Sangat Sesuai',
        ];
    }

    /**
     * Reproduces the old demo summary for historical results only.
     *
     * @param array<string, int|string> $answers
     * @return array<string, mixed>
     */
    public function calculateDemoResult(array $answers): array
    {
        $dimensionScores = [];
        foreach ($this->getLegacyDemoDimensions() as $key => $label) {
            $dimensionScores[$key] = [
                'label' => $label,
                'score' => 0,
                'max' => 0,
                'percentage' => 0,
            ];
        }

        foreach ($this->getLegacyDemoQuestions() as $question) {
            $dimension = $question['dimension'];
            $value = (int) ($answers[$question['code']] ?? 0);
            $dimensionScores[$dimension]['score'] += $value;
            $dimensionScores[$dimension]['max'] += 4;
        }

        $totalScore = 0;
        $totalMax = 0;
        foreach ($dimensionScores as $key => $data) {
            $dimensionScores[$key]['percentage'] = $data['max'] > 0
                ? (int) round(($data['score'] / $data['max']) * 100)
                : 0;
            $totalScore += $data['score'];
            $totalMax += $data['max'];
        }

        return [
            'dimensions' => $dimensionScores,
            'total_score' => $totalScore,
            'total_max' => $totalMax,
            'overall_percentage' => $totalMax > 0
                ? (int) round(($totalScore / $totalMax) * 100)
                : 0,
            'result_type' => 'DEMO',
            'interpretation' => null,
            'disclaimer' => 'Ringkasan angka ini hanya untuk demonstrasi fungsi aplikasi. Ini bukan skor instrumen penelitian, bukan kategori tingkat tekanan, dan bukan diagnosis.',
        ];
    }
}
