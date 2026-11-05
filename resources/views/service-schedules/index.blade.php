@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">RuangRileks · Pengelolaan layanan</p>
        <h1 class="text-3xl sm:text-4xl font-bold">Jadwal Layanan BK</h1>
        <p class="text-teal-100 mt-3 max-w-3xl">
            {{ $isAdmin
                ? 'Atur jadwal pelayanan untuk akun Guru BK yang aktif. Data janji temu dan catatan pribadi siswa tidak ditampilkan pada panel admin.'
                : 'Atur ketersediaan layanan dan kelola permintaan janji temu yang ditujukan kepada akun Guru BK ini.' }}
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-9 space-y-8">
    @if(session('status'))
        <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-800" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
            <p class="font-semibold">Perlu diperiksa:</p>
            <ul class="list-disc pl-5 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if($isAdmin)
        @include('admin.partials.nav')
    @endif

    <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-teal-900">Daftar jadwal pelayanan</h2>
            <p class="text-sm text-gray-600 mt-1">Jadwal yang dipublikasikan akan terlihat oleh siswa dan dapat diminta sebagai janji temu.</p>
        </div>
        <a href="{{ route($routePrefix.'.service-schedules.create') }}" class="inline-flex justify-center rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-5 py-3 text-sm font-semibold">＋ Tambah Jadwal</a>
    </section>

    @if($schedules->count())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($schedules as $schedule)
                <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-teal-900">{{ $schedule->title }}</h3>
                            @if($isAdmin)
                                <p class="text-sm text-gray-600 mt-1">Guru BK: {{ $schedule->teacher?->name ?? 'Akun tidak tersedia' }}</p>
                            @endif
                        </div>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $schedule->is_published ? 'bg-teal-50 text-teal-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $schedule->is_published ? 'Dipublikasikan' : 'Draft / Tidak aktif' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 text-sm">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-gray-500">Tanggal dan waktu</p>
                            <p class="font-semibold text-gray-900 mt-1">{{ $schedule->starts_at->format('d M Y') }}</p>
                            <p class="text-gray-700">{{ $schedule->starts_at->format('H:i') }}–{{ $schedule->ends_at->format('H:i') }} WIB</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-gray-500">Lokasi / media</p>
                            <p class="font-semibold text-gray-900 mt-1">{{ $schedule->service_mode === 'daring' ? 'Daring' : 'Tatap muka' }}</p>
                            <p class="text-gray-700 break-words">{{ $schedule->location }}</p>
                        </div>
                    </div>
                    @if($schedule->description)
                        <p class="text-sm text-gray-600 mt-4">{{ $schedule->description }}</p>
                    @endif
                    <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-600">Kapasitas: <strong>{{ $schedule->active_appointments_count }}/{{ $schedule->capacity }}</strong> janji aktif</p>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route($routePrefix.'.service-schedules.edit', $schedule->id) }}" class="inline-flex rounded-lg border border-teal-700 text-teal-800 hover:bg-teal-50 px-3 py-2 text-sm font-semibold">Edit</a>
                            @if($schedule->appointments()->doesntExist())
                                <form method="POST" action="{{ route($routePrefix.'.service-schedules.destroy', $schedule->id) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex rounded-lg border border-red-200 text-red-700 hover:bg-red-50 px-3 py-2 text-sm font-semibold">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div>{{ $schedules->links() }}</div>
    @else
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center">
            <h3 class="font-semibold text-gray-800">Belum ada jadwal pelayanan</h3>
            <p class="text-sm text-gray-600 mt-1">Tambahkan jadwal agar siswa dapat melihat ketersediaan layanan BK.</p>
        </div>
    @endif

    @unless($isAdmin)
        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-bold text-teal-900">Permintaan janji temu siswa</h2>
                <p class="text-sm text-gray-600 mt-1">Catatan siswa di bagian ini hanya dapat dilihat oleh Guru BK yang dituju.</p>
            </div>
            @if($appointments->isNotEmpty())
                <div class="space-y-3">
                    @foreach($appointments as $appointment)
                        <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-bold text-teal-900">{{ $appointment->student->name }}</h3>
                                        <span class="rounded-full bg-amber-50 text-amber-800 px-3 py-1 text-xs font-semibold">{{ $appointment->status === 'pending' ? 'Menunggu konfirmasi' : 'Dikonfirmasi' }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $appointment->student->email }}</p>
                                    <p class="text-sm text-gray-800 mt-3"><strong>{{ $appointment->schedule->title }}</strong></p>
                                    <p class="text-sm text-gray-600">{{ $appointment->schedule->starts_at->format('d/m/Y H:i') }}–{{ $appointment->schedule->ends_at->format('H:i') }} WIB · {{ $appointment->schedule->location }}</p>
                                    @if($appointment->student_note)
                                        <div class="mt-3 rounded-lg bg-gray-50 p-3 text-sm text-gray-700">
                                            <p class="font-semibold text-gray-800 mb-1">Catatan siswa</p>
                                            <p class="whitespace-pre-line">{{ $appointment->student_note }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-2 shrink-0">
                                    @if($appointment->status === 'pending')
                                        <form method="POST" action="{{ route('bk.service-schedules.appointments.status', $appointment->id) }}">
                                            @csrf @method('PATCH') <input type="hidden" name="status" value="approved">
                                            <button class="rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-3 py-2 text-sm font-semibold">Konfirmasi</button>
                                        </form>
                                        <form method="POST" action="{{ route('bk.service-schedules.appointments.status', $appointment->id) }}">
                                            @csrf @method('PATCH') <input type="hidden" name="status" value="rejected">
                                            <button class="rounded-lg border border-red-200 text-red-700 hover:bg-red-50 px-3 py-2 text-sm font-semibold">Tolak</button>
                                        </form>
                                    @elseif($appointment->status === 'approved')
                                        <form method="POST" action="{{ route('bk.service-schedules.appointments.status', $appointment->id) }}">
                                            @csrf @method('PATCH') <input type="hidden" name="status" value="completed">
                                            <button class="rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-3 py-2 text-sm font-semibold">Tandai selesai</button>
                                        </form>
                                        <form method="POST" action="{{ route('bk.service-schedules.appointments.status', $appointment->id) }}">
                                            @csrf @method('PATCH') <input type="hidden" name="status" value="rejected">
                                            <button class="rounded-lg border border-red-200 text-red-700 hover:bg-red-50 px-3 py-2 text-sm font-semibold">Batalkan</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center">
                    <p class="font-semibold text-gray-800">Belum ada permintaan janji temu aktif.</p>
                    <p class="text-sm text-gray-600 mt-1">Permintaan siswa akan muncul di sini setelah mereka memilih jadwal layanan Anda.</p>
                </div>
            @endif
        </section>
    @endunless
</div>
@endsection
