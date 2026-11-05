@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">Dashboard Siswa</p>
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">Riwayat Skrining</h1>
        <p class="text-teal-100 text-base sm:text-lg max-w-3xl">Lihat kembali hasil skrining stres akademik yang pernah Anda simpan dengan persetujuan.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 space-y-6">
    @if(session('status'))
        <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-800" role="status">{{ session('status') }}</div>
    @endif

    @if($results->count())
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-teal-800">Hasil tersimpan</h2>
                <p class="text-sm text-gray-600 mt-1">Hanya hasil yang Anda simpan dengan persetujuan yang ditampilkan di sini.</p>
            </div>
            <span class="text-sm text-gray-500">{{ $results->total() }} hasil tersimpan</span>
        </div>

        <div class="space-y-4">
            @foreach($results as $result)
                @php
                    $meta = is_array($result->meta) ? $result->meta : [];
                    $isCurrentInstrument = ($meta['instrument_key'] ?? null) === \App\Services\ScreeningService::INSTRUMENT_KEY;
                @endphp
                <article class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sm:p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="inline-flex items-center rounded-full bg-teal-50 text-teal-800 px-3 py-1 text-xs font-semibold">{{ $isCurrentInstrument ? 'Sinadia & Gandey (2024)' : 'DEMO lama' }}</span>
                                <span class="text-xs text-gray-500">{{ $result->answers_count }} jawaban tersimpan</span>
                            </div>
                            <h3 class="text-lg font-semibold text-teal-900">Skrining tekanan akademik</h3>
                            <p class="text-sm text-gray-600 mt-1">Tanggal: {{ ($result->completed_at ?? $result->created_at)?->copy()->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }}</p>
                            @if($isCurrentInstrument)
                                <p class="text-sm text-gray-600 mt-2">Ringkasan distribusi jawaban tersedia pada halaman detail. Tidak ada kategori tingkat tekanan yang dihitung.</p>
                            @elseif(isset($meta['overall_percentage']))
                                <p class="text-sm text-gray-700 mt-2">Ringkasan angka DEMO lama: <strong>{{ $meta['overall_percentage'] }}%</strong> <span class="text-gray-500">(bukan tingkat atau kategori tekanan)</span></p>
                            @else
                                <p class="text-sm text-gray-500 mt-2">Ringkasan angka dari versi DEMO sebelumnya tidak tersedia.</p>
                            @endif
                            @php
                                $activeShares = $result->shares->whereNull('revoked_at');
                            @endphp
                            @if($activeShares->isNotEmpty())
                                <div class="mt-3 text-sm text-gray-600"><span class="font-semibold text-teal-800">Dibagikan kepada:</span> {{ $activeShares->pluck('teacher.name')->filter()->implode(', ') }}</div>
                            @else
                                <p class="mt-3 text-sm text-gray-500">Belum dibagikan kepada guru BK.</p>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2 shrink-0">
                            <a href="{{ route('screening.result', $result->id) }}" class="inline-flex justify-center items-center rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-4 py-2.5 text-sm font-semibold transition">Lihat Detail</a>
                            @if($result->answers_count === 16)
                                <a href="{{ route('student.screening-shares.create', $result->id) }}" class="inline-flex justify-center items-center rounded-lg border border-teal-700 text-teal-800 hover:bg-teal-50 px-4 py-2.5 text-sm font-semibold transition">Bagikan ke BK</a>
                            @else
                                <span class="inline-flex justify-center items-center rounded-lg bg-gray-100 text-gray-500 px-4 py-2.5 text-sm">Tidak dapat dibagikan</span>
                            @endif
                            <form method="POST" action="{{ route('screening-history.destroy', $result->id) }}" onsubmit="return confirm('Hapus hasil skrining ini dari riwayat Anda? Jawaban, persetujuan, dan akses berbagi guru BK yang terkait juga akan dihapus atau dihentikan. Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex justify-center items-center rounded-lg border border-red-200 text-red-700 hover:bg-red-50 px-4 py-2.5 text-sm font-semibold transition">Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div>{{ $results->links() }}</div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-12 text-center">
            <h2 class="text-xl font-bold text-teal-900">Belum ada riwayat skrining</h2>
            <p class="text-gray-600 max-w-xl mx-auto mt-2">Hasil akan muncul di sini jika Anda login sebagai siswa dan menyetujui penyimpanan hasil ketika mengikuti skrining.</p>
            <a href="{{ route('screening.info') }}" class="inline-flex mt-6 items-center justify-center rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-5 py-3 font-semibold transition">Mulai Skrining</a>
        </div>
    @endif

    <div class="text-center"><a href="{{ route('dashboard') }}" class="text-sm font-semibold text-teal-800 hover:text-teal-600">Kembali ke Dashboard</a></div>
    <p class="text-xs text-gray-500 text-center max-w-3xl mx-auto">Hasil skrining bersifat deskriptif dan bukan diagnosis atau penetapan kategori tingkat tekanan akademik.</p>
</div>
@endsection
