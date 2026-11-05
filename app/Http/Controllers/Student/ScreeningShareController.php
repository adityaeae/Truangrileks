<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ScreeningResult;
use App\Models\ScreeningResultShare;
use App\Models\User;
use App\Services\ScreeningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ScreeningShareController extends Controller
{
    private const CONSENT_VERSION = '2026-11-02-v1';

    public function __construct(private readonly ScreeningService $screening)
    {
    }

    public function create(Request $request, int $resultId): Response
    {
        $result = $this->findShareableResult($request, $resultId);

        return response()->view('screening.shares.create', [
            'result' => $result,
            'teachers' => User::query()
                ->where('role', 'guru_bk')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'shares' => $result->shares()
                ->with('teacher:id,name,email')
                ->orderByDesc('consented_at')
                ->get(),
        ])->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache');
    }

    public function store(Request $request, int $resultId): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'integer'],
            'consent_share' => ['required', 'accepted'],
        ], [
            'consent_share.accepted' => 'Persetujuan berbagi hasil wajib diberikan untuk melanjutkan.',
        ]);

        $created = DB::transaction(function () use ($request, $resultId, $validated): bool {
            $result = $this->findShareableResult($request, $resultId, lock: true);

            $teacher = User::query()
                ->whereKey($validated['teacher_id'])
                ->where('role', 'guru_bk')
                ->firstOrFail();

            $alreadyShared = ScreeningResultShare::query()
                ->where('screening_result_id', $result->id)
                ->where('student_id', $request->user()->id)
                ->where('bk_teacher_id', $teacher->id)
                ->whereNull('revoked_at')
                ->exists();

            if ($alreadyShared) {
                return false;
            }

            ScreeningResultShare::create([
                'screening_result_id' => $result->id,
                'student_id' => $request->user()->id,
                'bk_teacher_id' => $teacher->id,
                'consent_version' => self::CONSENT_VERSION,
                'consented_at' => now(),
            ]);

            return true;
        });

        if (! $created) {
            return redirect()
                ->route('student.screening-shares.create', $resultId)
                ->withErrors(['teacher_id' => 'Hasil ini masih aktif dibagikan kepada guru BK tersebut.']);
        }

        return redirect()
            ->route('student.screening-shares.create', $resultId)
            ->with('status', 'Hasil skrining berhasil dibagikan kepada guru BK yang dipilih.');
    }

    public function revoke(Request $request, int $resultId, int $shareId): RedirectResponse
    {
        DB::transaction(function () use ($request, $resultId, $shareId): void {
            $result = $this->findShareableResult($request, $resultId, lock: true);

            $share = ScreeningResultShare::query()
                ->whereKey($shareId)
                ->where('screening_result_id', $result->id)
                ->where('student_id', $request->user()->id)
                ->whereNull('revoked_at')
                ->lockForUpdate()
                ->firstOrFail();

            $share->forceFill(['revoked_at' => now()])->save();
        });

        return redirect()
            ->route('student.screening-shares.create', $resultId)
            ->with('status', 'Akses guru BK terhadap hasil ini telah dicabut.');
    }

    private function findShareableResult(Request $request, int $resultId, bool $lock = false): ScreeningResult
    {
        $query = ScreeningResult::query()
            ->whereKey($resultId)
            ->where('user_id', $request->user()->id)
            ->whereHas('consents', function ($consents) use ($request): void {
                $consents->where('user_id', $request->user()->id)
                    ->where('consent_type', 'storage')
                    ->where('consent_given', true);
            });

        if ($lock) {
            $query->lockForUpdate();
        }

        $result = $query->firstOrFail();
        $questions = ($result->meta['instrument_key'] ?? null) === ScreeningService::INSTRUMENT_KEY
            ? $this->screening->getQuestions()
            : $this->screening->getLegacyDemoQuestions();
        $expectedCodes = collect($questions)->pluck('code')->sort()->values()->all();
        $storedCodes = $result->answers()
            ->pluck('question_code')->map(fn ($code): string => (string) $code)
            ->sort()->values()->all();

        if ($expectedCodes !== $storedCodes) {
            abort(404);
        }

        return $result;
    }
}
