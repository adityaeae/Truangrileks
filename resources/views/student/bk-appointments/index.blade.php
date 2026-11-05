@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">Dashboard Siswa · Layanan BK</p>
        <h1 class="text-3xl sm:text-4xl font-bold">Jadwal Temu Guru BK</h1>
        <p class="text-teal-100 mt-3 max-w-3xl">Pilih waktu pelayanan yang tersedia untuk mengajukan janji temu. Permintaan akan berstatus menunggu sampai Guru BK mengonfirmasinya.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-9 space-y-9">
    @if(session('status'))
        <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-800" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800" role="alert">
            <p class="font-semibold">Permintaan belum dapat diproses:</p>
            <ul class="list-disc pl-5 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="space-y-4">
        <div>
            <h2 class="text-xl font-bold text-teal-900">Jadwal yang tersedia</h2>
            <p class="text-sm text-gray-600 mt-1">Jadwal ditampilkan berdasarkan ketersediaan yang dipublikasikan oleh Guru BK atau administrator.</p>
        </div>
        @if($schedules->isNotEmpty())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($schedules as $schedule)
                    @php
    $remaining = max(0, $schedule->capacity - $schedule->active_appointments_count);
@endphp
                    <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-bold text-teal-900">{{ $schedule->title }}</h3>
                                <p class="text-sm text-gray-600 mt-1">Guru BK: {{ $schedule->teacher->name }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $remaining > 0 ? 'bg-teal-50 text-teal-800' : 'bg-gray-100 text-gray-600' }}">{{ $remaining > 0 ? $remaining.' slot tersedia' : 'Kuota penuh' }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 text-sm">
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-gray-500">Waktu pelayanan</p>
                                <p class="font-semibold text-gray-900 mt-1">{{ $schedule->starts_at->format('d M Y') }}</p>
                                <p class="text-gray-700">{{ $schedule->starts_at->format('H:i') }}–{{ $schedule->ends_at->format('H:i') }} WIB</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-gray-500">Moda dan lokasi</p>
                                <p class="font-semibold text-gray-900 mt-1">{{ $schedule->service_mode === 'daring' ? 'Daring' : 'Tatap muka' }}</p>
                                <p class="text-gray-700 break-words">{{ $schedule->location }}</p>
                            </div>
                        </div>
                        @if($schedule->description)<p class="text-sm text-gray-600 mt-4">{{ $schedule->description }}</p>@endif
                        @if($remaining > 0)
                            <form method="POST" action="{{ route('student.bk-appointments.store') }}" class="mt-5 border-t border-gray-100 pt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="service_schedule_id" value="{{ $schedule->id }}">
                                <div>
                                    <label for="student_note_{{ $schedule->id }}" class="block text-sm font-semibold text-gray-700">Catatan untuk Guru BK (opsional)</label>
                                    <textarea id="student_note_{{ $schedule->id }}" name="student_note" rows="2" maxlength="500" class="mt-1 w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600" placeholder="Tuliskan topik umum yang ingin dibicarakan. Hindari mencantumkan informasi sangat pribadi."></textarea>
                                </div>
                                <button type="submit" class="w-full sm:w-auto rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-5 py-3 text-sm font-semibold">Ajukan Janji Temu</button>
                            </form>
                        @else
                            <p class="mt-5 text-sm text-gray-500">Slot pada jadwal ini sudah penuh.</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center">
                <h3 class="font-semibold text-gray-800">Belum ada jadwal yang tersedia</h3>
                <p class="text-sm text-gray-600 mt-1">Silakan periksa kembali nanti atau hubungi pihak Bimbingan dan Konseling sekolah.</p>
            </div>
        @endif
    </section>

    <section class="space-y-4">
        <div>
            <h2 class="text-xl font-bold text-teal-900">Janji temu saya</h2>
            <p class="text-sm text-gray-600 mt-1">Bagian ini hanya menampilkan permintaan janji temu milik akun kamu.</p>
        </div>
        @if($appointments->count())
            <div class="space-y-3">
                @foreach($appointments as $appointment)
                    @php
                        $statusLabels = [
                            'pending' => ['Menunggu konfirmasi', 'bg-amber-50 text-amber-800'],
                            'approved' => ['Dikonfirmasi', 'bg-teal-50 text-teal-800'],
                            'rejected' => ['Ditolak / dibatalkan BK', 'bg-red-50 text-red-800'],
                            'cancelled' => ['Dibatalkan oleh siswa', 'bg-gray-100 text-gray-700'],
                            'completed' => ['Selesai', 'bg-blue-50 text-blue-800'],
                        ];
                        [$statusLabel, $statusClass] = $statusLabels[$appointment->status] ?? ['Status tidak diketahui', 'bg-gray-100 text-gray-700'];
                    @endphp
                    <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-bold text-teal-900">{{ $appointment->schedule->title }}</h3>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Guru BK: {{ $appointment->schedule->teacher->name }}</p>
                            <p class="text-sm text-gray-700 mt-2">{{ $appointment->schedule->starts_at->format('d/m/Y H:i') }}–{{ $appointment->schedule->ends_at->format('H:i') }} WIB · {{ $appointment->schedule->location }}</p>
                            <p class="text-xs text-gray-500 mt-1">Diajukan: {{ $appointment->requested_at->copy()->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>
                            @if(filled($appointment->student_note))
                        <div class="mt-3 rounded-lg bg-cream-50 border border-gray-100 p-3">
                            <p class="text-xs font-semibold text-gray-600 mb-1">
                                Catatan untuk Guru BK
                            </p>
                            <p class="text-sm text-gray-700">
                                {{ $appointment->student_note }}
                            </p>
                        </div>
                    @endif
                        </div>
                        @if(in_array($appointment->status, ['pending', 'approved'], true) && $appointment->schedule->starts_at->isFuture())
                            <form method="POST" action="{{ route('student.bk-appointments.cancel', $appointment->id) }}" onsubmit="return confirm('Batalkan janji temu ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg border border-red-200 text-red-700 hover:bg-red-50 px-4 py-2 text-sm font-semibold">Batalkan Janji Temu</button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
            <div>{{ $appointments->links() }}</div>
        @else
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center">
                <p class="font-semibold text-gray-800">Kamu belum memiliki janji temu.</p>
                <p class="text-sm text-gray-600 mt-1">Pilih salah satu jadwal yang tersedia di atas untuk mengajukan pertemuan.</p>
            </div>
        @endif
    </section>

    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <strong>Informasi:</strong> Pengajuan janji temu belum otomatis menjadi konfirmasi. Tunggu sampai Guru BK mengonfirmasi permintaanmu. Jadwal temu BK merupakan layanan terpisah dari persetujuan penyimpanan atau berbagi hasil skrining.
    </div>
</div>
@endsection
