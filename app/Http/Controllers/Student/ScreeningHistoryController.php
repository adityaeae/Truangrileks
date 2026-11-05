<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ScreeningResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScreeningHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $studentId = $request->user()->id;

        $results = ScreeningResult::query()
            ->where('user_id', $studentId)
            ->whereHas('consents', function ($query): void {
                $query->where('consent_type', 'storage')
                    ->where('consent_given', true);
            })
            ->with(['shares.teacher:id,name,email'])
            ->withCount('answers')
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('screening.history.index', [
            'results' => $results,
        ]);
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $studentId = $request->user()->id;

        DB::transaction(function () use ($id, $studentId): void {
            $result = ScreeningResult::query()
                ->whereKey($id)
                ->where('user_id', $studentId)
                ->whereHas('consents', function ($query): void {
                    $query->where('consent_type', 'storage')
                        ->where('consent_given', true);
                })
                ->lockForUpdate()
                ->firstOrFail();

            // Delete dependent rows explicitly. The consent FK is nullableOnDelete,
            // so deleting it first prevents orphaned consent records with a null result.
            $result->answers()->delete();
            $result->consents()->delete();
            $result->delete();
        });

        return redirect()
            ->route('screening-history.index')
            ->with('status', 'Hasil skrining berhasil dihapus dari riwayat Anda.');
    }
}
