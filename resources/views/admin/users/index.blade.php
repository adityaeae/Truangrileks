@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">Administrasi</p>
        <h1 class="text-3xl font-bold">Manajemen Pengguna</h1>
        <p class="text-teal-100 mt-2">Kelola akun siswa dan Guru BK. Data hasil skrining tidak ditampilkan di halaman ini.</p>
    </div>
</div>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
    @include('admin.partials.nav')
    @if(session('status'))<div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-800" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">Periksa kembali filter yang dimasukkan.</div>@endif

    <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div><h2 class="text-lg font-bold text-teal-900">Daftar akun</h2><p class="text-sm text-gray-600">Akun admin tidak ditampilkan dan role pengguna tidak dapat diubah dari formulir ini.</p></div>
            <a href="{{ route('admin.users.create-teacher') }}" class="inline-flex justify-center rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-4 py-2.5 text-sm font-semibold">Tambah Akun Guru BK</a>
        </div>
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama atau email" class="rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600 sm:col-span-2">
            <select name="role" class="rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
                <option value="">Semua role</option><option value="siswa" @selected(($filters['role'] ?? '') === 'siswa')>Siswa</option><option value="guru_bk" @selected(($filters['role'] ?? '') === 'guru_bk')>Guru BK</option>
            </select>
            <select name="status" class="rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
                <option value="">Semua status</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
            </select>
            <div class="sm:col-span-4 flex gap-2"><button class="rounded-lg bg-teal-700 text-white px-4 py-2 text-sm font-semibold">Terapkan Filter</button><a href="{{ route('admin.users.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Reset</a></div>
        </form>
    </section>

    <section class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        @if($users->isEmpty())
            <div class="p-8 text-center text-gray-600">Tidak ada akun yang sesuai dengan filter.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-cream-50 text-gray-600"><tr><th class="px-5 py-3 text-left font-semibold">Pengguna</th><th class="px-5 py-3 text-left font-semibold">Role</th><th class="px-5 py-3 text-left font-semibold">Status</th><th class="px-5 py-3 text-left font-semibold">Terdaftar</th><th class="px-5 py-3 text-right font-semibold">Tindakan</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $managedUser)
                            <tr>
                                <td class="px-5 py-4"><p class="font-semibold text-teal-900">{{ $managedUser->name }}</p><p class="text-gray-500">{{ $managedUser->email }}</p></td>
                                <td class="px-5 py-4"><span class="rounded-full bg-teal-50 text-teal-800 px-3 py-1 text-xs font-semibold">{{ $managedUser->role === 'guru_bk' ? 'Guru BK' : 'Siswa' }}</span></td>
                                <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $managedUser->is_active ? 'bg-green-50 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $managedUser->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="px-5 py-4 text-gray-600">{{ $managedUser->created_at?->copy()->timezone('Asia/Jakarta')->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-5 py-4"><div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $managedUser->id) }}" class="rounded-lg border border-teal-700 px-3 py-2 text-xs font-semibold text-teal-800 hover:bg-teal-50">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.status', $managedUser->id) }}" onsubmit="return confirm('Ubah status akun ini?');">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $managedUser->is_active ? 0 : 1 }}"><button class="rounded-lg border px-3 py-2 text-xs font-semibold {{ $managedUser->is_active ? 'border-amber-300 text-amber-800 hover:bg-amber-50' : 'border-green-300 text-green-800 hover:bg-green-50' }}">{{ $managedUser->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $users->links() }}</div>
        @endif
    </section>
    <p class="text-xs text-gray-500">Akun dinonaktifkan, bukan dihapus, agar riwayat dan keterkaitan data tetap terjaga. Saat akun siswa dinonaktifkan, seluruh akses berbagi hasil aktif miliknya dicabut.</p>
</div>
@endsection
