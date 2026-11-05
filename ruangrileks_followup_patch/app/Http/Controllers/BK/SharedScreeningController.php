<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\ScreeningResultShare;
use App\Services\ScreeningService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SharedScreeningController extends Controller
{
    public function __construct(private readonly ScreeningService $screening)
    {
    }

    public function index(Request $request): Response
    {
        $shares = $this->activeSharesQuery($request->user()->id)
            ->with(['student:id,name,email', 'screeningResult:id,completed_at'])
            ->orderByDesc('consented_at')
            ->paginate(10);

        return response()->view('bk.shares.index', [
            'shares' => $shares,
        ])->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache');
    }

    public function show(Request $request, int $shareId): Response
    {
        $share = $this->activeSharesQuery($request->user()->id)
            ->with([
                'student:id,name,email',
                'screeningResult.answers',
            ])
            ->whereKey($shareId)
            ->firstOrFail();

        $questionsByDimension = [];
        $answerMap = $share->screeningResult->answers->keyBy('question_code');
        $meta = is_array($share->screeningResult->meta) ? $share->screeningResult->meta : [];
        $isCurrentInstrument = ($meta['instrument_key'] ?? null) === ScreeningService::INSTRUMENT_KEY;
        $descriptiveProfile = $isCurrentInstrument
            ? $this->screening->buildDescriptiveProfile($share->screeningResult->answers->pluck('answer', 'question_code')->toArray())
            : null;
        $storedImpact = $meta['supplementary_impact']['value'] ?? null;
        $followUpGuidance = $isCurrentInstrument
            ? $this->screening->buildFollowUpGuidance(
                is_string($storedImpact) ? $storedImpact : null,
                $descriptiveProfile ?? []
            )
            : null;
        $sourceQuestions = $isCurrentInstrument
            ? $this->screening->getQuestions()
            : $this->screening->getLegacyDemoQuestions();
        $answerLabels = $isCurrentInstrument
            ? $this->screening->getResponseOptions()
            : $this->screening->getLegacyDemoResponseOptions();
        $dimensions = $isCurrentInstrument
            ? $this->screening->getDimensions()
            : $this->screening->getLegacyDemoDimensions();

        foreach ($sourceQuestions as $question) {
            $answer = $answerMap->get($question['code']);
            if (! $answer) {
                abort(404);
            }

            $answerCode = (string) $answer->answer;
            $question['answer_value'] = $answerCode;
            $question['answer_label'] = $answerLabels[$answerCode] ?? 'Jawaban tidak dikenali';
            $questionsByDimension[$question['dimension']][] = $question;
        }

        // Derive per-dimension response frequencies from this shared result's
        // stored answers. These are descriptive counts only, not scores or
        // indicators of academic-stress severity.
        $dimensionSummaries = [];
        if ($isCurrentInstrument) {
            foreach ($dimensions as $dimensionKey => $dimensionLabel) {
                $dimensionQuestions = $questionsByDimension[$dimensionKey] ?? [];
                $responseCounts = array_fill_keys(array_keys($answerLabels), 0);
                $answeredCount = 0;

                foreach ($dimensionQuestions as $question) {
                    $answerCode = (string) $question['answer_value'];
                    if (array_key_exists($answerCode, $responseCounts)) {
                        $responseCounts[$answerCode]++;
                        $answeredCount++;
                    }
                }

                $dimensionSummaries[$dimensionKey] = [
                    'label' => $dimensionLabel,
                    'item_count' => count($dimensionQuestions),
                    'answered_count' => $answeredCount,
                    'response_counts' => $responseCounts,
                ];
            }
        }

        return response()->view('bk.shares.show', [
            'share' => $share,
            'questions' => $questionsByDimension,
            'dimensions' => $dimensions,
            'dimensionSummaries' => $dimensionSummaries,
            'descriptiveProfile' => $descriptiveProfile,
            'followUpGuidance' => $followUpGuidance,
            'meta' => $meta,
            'isCurrentInstrument' => $isCurrentInstrument,
            'responseOptions' => $answerLabels,
        ])->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache');
    }

    private function activeSharesQuery(int $teacherId): Builder
    {
        return ScreeningResultShare::query()
            ->where('bk_teacher_id', $teacherId)
            ->whereNull('revoked_at')
            ->whereHas('student', function (Builder $studentQuery): void {
                $studentQuery->where('role', 'siswa');
            })
            ->whereHas('screeningResult', function (Builder $resultQuery): void {
                $resultQuery
                    ->whereColumn('screening_results.user_id', 'screening_result_shares.student_id')
                    ->whereHas('consents', function (Builder $consentQuery): void {
                        $consentQuery->whereColumn('screening_consents.user_id', 'screening_result_shares.student_id')
                            ->where('consent_type', 'storage')
                            ->where('consent_given', true);
                    });
            });
    }
}
