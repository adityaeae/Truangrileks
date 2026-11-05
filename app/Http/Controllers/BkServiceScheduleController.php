<?php

namespace App\Http\Controllers;

use App\Models\BkAppointment;
use App\Models\BkServiceSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BkServiceScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        $schedules = BkServiceSchedule::query()
            ->with('teacher:id,name,email')
            ->withCount(['activeAppointments as active_appointments_count'])
            ->when(! $isAdmin, fn (Builder $query) => $query->where('bk_teacher_id', $user->id))
            ->orderByDesc('starts_at')
            ->paginate(12);

        $appointments = collect();
        if (! $isAdmin) {
            $appointments = BkAppointment::query()
                ->with([
                    'student:id,name,email',
                    'schedule:id,bk_teacher_id,title,service_mode,location,starts_at,ends_at',
                ])
                ->whereIn('status', BkServiceSchedule::ACTIVE_APPOINTMENT_STATUSES)
                ->whereHas('schedule', fn (Builder $query) => $query->where('bk_teacher_id', $user->id))
                ->orderBy('requested_at')
                ->limit(50)
                ->get();
        }

        return view('service-schedules.index', [
            'schedules' => $schedules,
            'appointments' => $appointments,
            'isAdmin' => $isAdmin,
            'routePrefix' => $isAdmin ? 'admin' : 'bk',
        ]);
    }

    public function create(Request $request): View
    {
        $isAdmin = $request->user()->role === 'admin';

        return view('service-schedules.create', [
            'isAdmin' => $isAdmin,
            'routePrefix' => $isAdmin ? 'admin' : 'bk',
            'teachers' => $isAdmin ? $this->activeTeachers() : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';
        $validated = $this->validatedSchedule($request, $isAdmin);
        $teacherId = $isAdmin ? (int) $validated['bk_teacher_id'] : $user->id;

        $this->ensureNoPublishedOverlap(
            $teacherId,
            Carbon::parse($validated['starts_at']),
            Carbon::parse($validated['ends_at']),
            (bool) $validated['is_published']
        );

        BkServiceSchedule::create([
            ...$validated,
            'bk_teacher_id' => $teacherId,
            'created_by' => $user->id,
        ]);

        return redirect()->route($isAdmin ? 'admin.service-schedules.index' : 'bk.service-schedules.index')
            ->with('status', 'Jadwal layanan BK berhasil ditambahkan.');
    }

    public function edit(Request $request, int $scheduleId): View
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';
        $schedule = $this->managedSchedule($user, $scheduleId);

        return view('service-schedules.edit', [
            'schedule' => $schedule,
            'isAdmin' => $isAdmin,
            'routePrefix' => $isAdmin ? 'admin' : 'bk',
            'teachers' => $isAdmin ? $this->activeTeachers() : collect(),
            'activeAppointmentCount' => $schedule->activeAppointments()->count(),
        ]);
    }

    public function update(Request $request, int $scheduleId): RedirectResponse
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';
        $schedule = $this->managedSchedule($user, $scheduleId);
        $validated = $this->validatedSchedule($request, $isAdmin);
        $teacherId = $isAdmin ? (int) $validated['bk_teacher_id'] : $user->id;
        $activeCount = $schedule->activeAppointments()->count();

        $newStart = Carbon::parse($validated['starts_at']);
        $newEnd = Carbon::parse($validated['ends_at']);
        $changesAppointmentDetails = $schedule->bk_teacher_id !== $teacherId
            || ! $schedule->starts_at->equalTo($newStart)
            || ! $schedule->ends_at->equalTo($newEnd)
            || $schedule->service_mode !== $validated['service_mode']
            || $schedule->location !== $validated['location'];

        if ($activeCount > 0 && $changesAppointmentDetails) {
            return back()->withInput()->withErrors([
                'starts_at' => 'Jadwal ini masih memiliki janji temu aktif. Selesaikan atau tolak janji temu terlebih dahulu sebelum mengubah waktu, Guru BK, moda, atau lokasi layanan.',
            ]);
        }

        if ((int) $validated['capacity'] < $activeCount) {
            return back()->withInput()->withErrors([
                'capacity' => 'Kapasitas tidak boleh lebih kecil daripada jumlah janji temu yang masih aktif.',
            ]);
        }

        $this->ensureNoPublishedOverlap(
            $teacherId,
            $newStart,
            $newEnd,
            (bool) $validated['is_published'],
            $schedule->id
        );

        $schedule->update([
            ...$validated,
            'bk_teacher_id' => $teacherId,
        ]);

        return redirect()->route($isAdmin ? 'admin.service-schedules.index' : 'bk.service-schedules.index')
            ->with('status', 'Jadwal layanan BK berhasil diperbarui.');
    }

    public function destroy(Request $request, int $scheduleId): RedirectResponse
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';
        $schedule = $this->managedSchedule($user, $scheduleId);

        if ($schedule->appointments()->exists()) {
            return back()->withErrors([
                'schedule' => 'Jadwal tidak dapat dihapus karena sudah memiliki riwayat janji temu. Ubah status publikasi menjadi tidak aktif jika jadwal tidak lagi digunakan.',
            ]);
        }

        $schedule->delete();

        return redirect()->route($isAdmin ? 'admin.service-schedules.index' : 'bk.service-schedules.index')
            ->with('status', 'Jadwal layanan BK berhasil dihapus.');
    }

    private function validatedSchedule(Request $request, bool $isAdmin): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'service_mode' => ['required', Rule::in(['tatap_muka', 'daring'])],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'is_published' => ['required', 'boolean'],
        ];

        if ($isAdmin) {
            $rules['bk_teacher_id'] = [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'guru_bk')
                    ->where('is_active', true)),
            ];
        }

        return $request->validate($rules);
    }

    private function activeTeachers()
    {
        return User::query()
            ->where('role', 'guru_bk')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function managedSchedule(User $user, int $scheduleId): BkServiceSchedule
    {
        return BkServiceSchedule::query()
            ->when($user->role !== 'admin', fn (Builder $query) => $query->where('bk_teacher_id', $user->id))
            ->whereKey($scheduleId)
            ->firstOrFail();
    }

    private function ensureNoPublishedOverlap(
        int $teacherId,
        Carbon $startsAt,
        Carbon $endsAt,
        bool $isPublished,
        ?int $exceptId = null
    ): void {
        if (! $isPublished) {
            return;
        }

        $overlap = BkServiceSchedule::query()
            ->where('bk_teacher_id', $teacherId)
            ->where('is_published', true)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->when($exceptId, fn (Builder $query) => $query->where('id', '!=', $exceptId))
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'starts_at' => 'Guru BK sudah memiliki jadwal layanan lain pada rentang waktu tersebut.',
            ]);
        }
    }
}
