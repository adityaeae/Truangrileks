@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-12 sm:py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">Dashboard Guru BK</p>
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">Hasil yang Dibagikan</h1>
        <p class="text-teal-100 max-w-3xl">Halaman ini hanya menampilkan hasil skrining yang secara khusus dibagikan siswa kepada akun guru BK Anda dan aksesnya masih aktif.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-5">
    @if($shares->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8 text-center">
            <h2 class="text-xl font-bold text-teal-900">Belum ada hasil yang dibagikan</h2>
            <p class="text-sm text-gray-600 mt-2">Hasil akan muncul di sini setelah siswa memberikan persetujuan berbagi secara khusus kepada Anda.</p>
        </div>
    @else
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-teal-900">Daftar hasil aktif</h2>
                <p class="text-sm text-gray-600 mt-1">Setiap akses hanya berlaku untuk satu hasil skrining.</p>
            </div>
            <span class="text-sm text-gray-500">{{ $shares->total() }} hasil</span>
        </div>
        <div class="space-y-3">
            @foreach($shares as $share)
                <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="rounded-full bg-teal-50 text-teal-800 px-3 py-1 text-xs font-semibold">Skrining</span>
                            <span class="text-xs text-gray-500">Akses aktif</span>
                        </div>
                        <h2 class="text-lg font-semibold text-teal-900">{{ $share->student?->name ?? 'Siswa tidak tersedia' }}</h2>
                        <p class="text-sm text-gray-600">Tanggal skrining: {{ optional($share->screeningResult?->completed_at)->format('d/m/Y H:i') ?? 'Tidak tersedia' }}</p>
                        <p class="text-xs text-gray-500 mt-1">Persetujuan diberikan: {{ $share->consented_at?->copy()->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                    <a href="{{ route('bk.shared-results.show', $share->id) }}" class="inline-flex justify-center rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-5 py-2.5 text-sm font-semibold transition">Lihat Hasil</a>
                </article>
            @endforeach
        </div>
        {{ $shares->links() }}
    @endif

    <div class="text-center">
        <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-teal-800 hover:text-teal-600">Kembali ke Dashboard</a>
    </div>
</div>
@endsection
