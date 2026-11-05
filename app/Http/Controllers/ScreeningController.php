<?php

namespace App\Http\Controllers;

use App\Models\ScreeningResult;
use App\Services\ScreeningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ScreeningController extends Controller
{
    public function __construct(protected ScreeningService $screening = new ScreeningService())
    {
    }

    public function info()
    {
        return view('screening.info');
    }

    public function consent(Request $request)
    {
        $request->validate([
            'confirm_participation' => ['required', 'accepted'],
        ]);

        $request->session()->forget(['screening_consent', 'screening_demo_result']);
        $request->session()->put('screening_participation_pending', true);

        return view('screening.consent');
    }

    public function storeConsent(Request $request)
    {
        if (! (bool) $request->session()->get('screening_participation_pending', false)) {
            return redirect()->route('screening.info')
                ->with('error', 'Silakan mulai dari informasi skrining terlebih dahulu.');
        }

        $validated = $request->validate([
            'consent_participation' => ['required', 'accepted'],
            'consent_storage' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $canStore = $user !== null && $user->role === 'siswa';
        $storageConsent = $canStore && (bool) ($validated['consent_storage'] ?? false);

        $request->session()->put('screening_consent', [
            'participation' => true,
            'storage' => $storageConsent,
        ]);
        $request->session()->forget('screening_participation_pending');

        return redirect()->route('screening.questionnaire');
    }

    public function questionnaire(Request $request)
    {
        if (! (bool) $request->session()->get('screening_consent.participation', false)) {
            return redirect()->route('screening.info')
                ->with('error', 'Silakan menyetujui partisipasi terlebih dahulu.');
        }

        $questions = $this->screening->getQuestions();
        $grouped = [];
        foreach ($questions as $question) {
            $grouped[$question['dimension']][] = $question;
        }

        return view('screening.questionnaire', [
            'questions' => $grouped,
            'dimensions' => $this->screening->getDimensions(),
            'responseOptions' => $this->screening->getResponseOptions(),
            'dailyImpactOptions' => $this->screening->getDailyImpactOptions(),
            'dailyImpactQuestion' => $this->screening->getDailyImpactQuestion(),
        ]);
    }

    public function submit(Request $request)
    {
        if (! (bool) $request->session()->get('screening_consent.participation', false)) {
            return redirect()->route('screening.info')
                ->with('error', 'Persetujuan partisipasi tidak ditemukan. Silakan mulai kembali.');
        }

        // Do not flash submitted answers into the session if validation fails.
        $validator = Validator::make($request->all(), [
            'answers' => ['required', 'array', 'size:16'],
            'answers.*' => ['required', 'string', Rule::in(array_keys($this->screening->getResponseOptions()))],
            'daily_impact' => ['required', 'string', Rule::in(array_keys($this->screening->getDailyImpactOptions()))],
        ]);

        if ($validator->fails()) {
            return redirect()->route('screening.questionnaire')->withErrors($validator);
        }

        /** @var array<string, string> $answers */
        $validated = $validator->validated();
        $answers = $validated['answers'];
        $dailyImpact = $validated['daily_impact'];
        $expectedCodes = array_column($this->screening->getQuestions(), 'code');
        $submittedCodes = array_map('strval', array_keys($answers));
        sort($expectedCodes);
        sort($submittedCodes);

        if ($expectedCodes !== $submittedCodes) {
            return redirect()->route('screening.questionnaire')
                ->withErrors(['answers' => 'Daftar jawaban tidak sesuai dengan instrumen. Silakan isi kembali.']);
        }

        $consent = $request->session()->get('screening_consent', []);
        $user = Auth::user();
        $canStore = $user !== null
            && $user->role === 'siswa'
            && (bool) ($consent['storage'] ?? false);

        $screeningResult = $this->screening->storeScreeningResult(
            $request,
            $user,
            $answers,
            $canStore,
            $dailyImpact
        );

        $request->session()->forget(['screening_consent', 'screening_participation_pending']);

        if ($screeningResult instanceof ScreeningResult) {
            $request->session()->forget('screening_demo_result');

            return redirect()->route('screening.result', $screeningResult->id);
        }

        // Only aggregate frequencies are held in session; raw guest answers are not stored.
        $request->session()->put('screening_demo_result', $screeningResult);

        return redirect()->route('screening.result', 'demo');
    }

    public function result(Request $request, string $id)
    {
        $screeningResult = null;
        $consentStorage = false;
        $answerDetails = [];
        $questions = [];
        $dimensions = [];
        $responseOptions = [];
        $isCurrentInstrument = false;

        if ($id === 'demo') {
            $meta = $request->session()->get('screening_demo_result');
            if (! is_array($meta) || ($meta['instrument_key'] ?? null) !== ScreeningService::INSTRUMENT_KEY) {
                abort(404);
            }

            $isCurrentInstrument = true;
            $questions = $this->screening->getQuestions();
            $dimensions = $this->screening->getDimensions();
            $responseOptions = $this->screening->getResponseOptions();
        } else {
            $user = $request->user();
            if (! $user || $user->role !== 'siswa') {
                abort(404);
            }

            // Always constrain the result by its owner and explicit storage consent.
            $screeningResult = ScreeningResult::query()
                ->with(['answers', 'consents'])
                ->whereKey($id)
                ->where('user_id', $user->id)
                ->whereHas('consents', function ($query): void {
                    $query->where('consent_type', 'storage')
                        ->where('consent_given', true);
                })
                ->firstOrFail();

            // Eloquent normally casts meta to an array. Decode defensively as well
            // so older JSON-string rows remain readable across environments.
            $rawMeta = $screeningResult->meta;
            if (is_array($rawMeta)) {
                $meta = $rawMeta;
            } elseif (is_string($rawMeta)) {
                $decodedMeta = json_decode($rawMeta, true);
                $meta = is_array($decodedMeta) ? $decodedMeta : [];
            } else {
                $meta = [];
            }

            $answerValues = $screeningResult->answers->keyBy('question_code');
            $answers = $screeningResult->answers->pluck('answer', 'question_code')->toArray();
            $expectedCurrentCodes = array_column($this->screening->getQuestions(), 'code');
            $storedAnswerCodes = array_map('strval', array_keys($answers));
            $sortedExpectedCurrentCodes = $expectedCurrentCodes;
            sort($sortedExpectedCurrentCodes);
            sort($storedAnswerCodes);

            // Answer codes provide a safe fallback for rows whose metadata was
            // written by an earlier build but whose 16 current-instrument answers
            // are intact.
            $isCurrentInstrument = ($meta['instrument_key'] ?? null) === ScreeningService::INSTRUMENT_KEY
                || $sortedExpectedCurrentCodes === $storedAnswerCodes;
            if ($isCurrentInstrument) {
                $questions = $this->screening->getQuestions();
                $dimensions = $this->screening->getDimensions();
                $responseOptions = $this->screening->getResponseOptions();
                // The profile is rebuilt from stored answers. The optional impact
                // response remains separate from the 16 scale items in meta.
            } else {
                // Keep results made by the old DEMO questionnaire readable after this update.
                $questions = $this->screening->getLegacyDemoQuestions();
                $dimensions = $this->screening->getLegacyDemoDimensions();
                $responseOptions = $this->screening->getLegacyDemoResponseOptions();
            }

            foreach ($questions as $question) {
                $storedAnswer = $answerValues->get($question['code']);
                if (! $storedAnswer) {
                    continue;
                }

                $answerDetails[$question['dimension']][] = [
                    'code' => $question['code'],
                    'question' => $question['text'],
                    'answer' => $responseOptions[(string) $storedAnswer->answer] ?? 'Jawaban tidak dikenali',
                ];
            }

            $expectedCodes = array_column($questions, 'code');
            $storedCodes = array_map('strval', array_keys($answers));
            sort($expectedCodes);
            sort($storedCodes);

            if ($isCurrentInstrument && $expectedCodes === $storedCodes) {
                // Rebuild all descriptive information from the saved 16 answers.
                // The separate impact response is never inferred from those items.
                $storedImpact = $meta['supplementary_impact']['value']
                    ?? $meta['daily_impact']
                    ?? null;
                $meta = $this->screening->calculateScreeningSummary(
                    $answers,
                    is_string($storedImpact) ? $storedImpact : null
                );
            } elseif (! $isCurrentInstrument && $expectedCodes === $storedCodes && ! isset($meta['overall_percentage'])) {
                $meta = $this->screening->calculateDemoResult($answers);
            } elseif ($expectedCodes !== $storedCodes) {
                $meta = [
                    'legacy_unavailable' => true,
                    'disclaimer' => 'Hasil ini berasal dari versi kuesioner terdahulu dan tidak dapat ditampilkan lengkap dengan struktur saat ini.',
                ];
            }

            $consentStorage = true;
        }

        return view('screening.result', [
            'result' => $screeningResult,
            'meta' => $meta,
            'consentStorage' => $consentStorage,
            'dimensions' => $dimensions,
            'answerDetails' => $answerDetails,
            'isCurrentInstrument' => $isCurrentInstrument,
            'instrumentTitle' => $isCurrentInstrument
                ? ScreeningService::INSTRUMENT_TITLE
                : 'Hasil Skrining DEMO (versi sebelumnya)',
            'responseOptions' => $responseOptions,
            'canBookBkAppointment' => $request->user()?->role === 'siswa',
        ]);
    }
}
