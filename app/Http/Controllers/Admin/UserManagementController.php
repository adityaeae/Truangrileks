<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScreeningResultShare;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'role' => ['nullable', Rule::in(['siswa', 'guru_bk'])],
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $users = User::query()
            ->whereIn('role', ['siswa', 'guru_bk'])
            ->when($validated['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($validated['status'] ?? null, function ($query, $status): void {
                $query->where('is_active', $status === 'active');
            })
            ->when($validated['q'] ?? null, function ($query, $term): void {
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%');
                });
            })
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $validated,
        ]);
    }

    public function createTeacher(): View
    {
        return view('admin.users.create-teacher');
    }

    public function storeTeacher(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'guru_bk',
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', 'Akun Guru BK berhasil dibuat. Role ditetapkan oleh sistem.');
    }

    public function edit(int $userId): View
    {
        $user = $this->managedUser($userId);

        return view('admin.users.edit', ['managedUser' => $user]);
    }

    public function update(Request $request, int $userId): RedirectResponse
    {
        $user = $this->managedUser($userId);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'Data akun berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, int $userId): RedirectResponse
    {
        $user = $this->managedUser($userId);
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        $isActive = (bool) $validated['is_active'];

        DB::transaction(function () use ($user, $isActive): void {
            $user->forceFill(['is_active' => $isActive])->save();

            // When a student account is deactivated, revoke their active BK shares
            // so the school account action cannot leave student data accessible.
            if ($user->role === 'siswa' && ! $isActive) {
                ScreeningResultShare::query()
                    ->where('student_id', $user->id)
                    ->whereNull('revoked_at')
                    ->update(['revoked_at' => now(), 'updated_at' => now()]);
            }
        });

        return redirect()->route('admin.users.index')->with(
            'status',
            $isActive ? 'Akun berhasil diaktifkan.' : 'Akun berhasil dinonaktifkan. Akses berbagi hasil milik siswa juga dicabut.'
        );
    }

    private function managedUser(int $userId): User
    {
        return User::query()
            ->whereKey($userId)
            ->whereIn('role', ['siswa', 'guru_bk'])
            ->firstOrFail();
    }
}
