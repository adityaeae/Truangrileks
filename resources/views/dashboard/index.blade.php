@extends('layouts.app')

@php
    $roleLabel = match ($role) {
        'siswa' => 'Siswa',
        'guru_bk' => 'Guru BK',
        default => ucfirst(str_replace('_', ' ', $role)),
    };

    $dashboardTitle = match ($role) {
        'siswa' => 'Dashboard Siswa',
        'guru_bk' => 'Dashboard Guru BK',
        default => 'Dashboard',
    };
@endphp

@section('content')
<div class="bg-teal-700 text-white py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">RuangRileks · Area {{ $roleLabel }}</p>
        <h1 class="text-3xl sm:text-4xl font-bold">{{ $dashboardTitle }}</h1>
        <p class="text-teal-100 mt-3 max-w-3xl">
            Selamat datang, <strong>{{ $user->name }}</strong>. Pilih layanan yang ingin kamu gunakan.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-9 space-y-8">
    @if(session('status'))
        <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500">Akun yang sedang digunakan</p>
                <h2 class="text-xl font-bold text-teal-900 mt-1">{{ $user->name }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $user->email }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-teal-50 border border-teal-100 px-3 py-1.5 text-sm font-semibold text-teal-800">
                    {{ $roleLabel }}
                </span>
                <a href="{{ route('profile') }}" class="inline-flex items-center justify-center rounded-lg border border-teal-700 px-4 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-50 transition">
                    Lihat Profil
                </a>
            </div>
        </div>
    </section>

    @if($role === 'siswa')
        <section>
            <div class="mb-4">
                <h2 class="text-xl font-bold text-teal-900">Layanan Siswa</h2>
                <p class="text-sm text-gray-600 mt-1">Akses skrining, riwayat pribadi, dan materi pendampingan.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
                <a href="{{ route('screening.info') }}" class="group bg-white rounded-xl border border-teal-100 shadow-sm p-5 hover:shadow-md hover:border-teal-400 transition focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-teal-50 text-teal-800 text-xl font-bold" aria-hidden="true">＋</span>
                    <h3 class="text-lg font-bold text-teal-900 mt-4">Mulai Skrining</h3>
                    <p class="text-sm text-gray-600 mt-2">Baca informasi dan persetujuan sebelum mengisi kuesioner.</p>
                    <span class="inline-block text-sm font-semibold text-teal-700 mt-4 group-hover:underline">Mulai sekarang →</span>
                </a>

                <a href="{{ route('screening-history.index') }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-teal-400 transition focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-teal-50 text-teal-800 text-xl font-bold" aria-hidden="true">◷</span>
                    <h3 class="text-lg font-bold text-teal-900 mt-4">Riwayat Skrining</h3>
                    <p class="text-sm text-gray-600 mt-2">Buka kembali hasil yang kamu setujui untuk disimpan.</p>
                    <span class="inline-block text-sm font-semibold text-teal-700 mt-4 group-hover:underline">Lihat riwayat →</span>
                </a>

                <a href="{{ route('edukasi') }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-teal-400 transition focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-amber-50 text-amber-800 text-xl font-bold" aria-hidden="true">▤</span>
                    <h3 class="text-lg font-bold text-teal-900 mt-4">Materi Edukasi</h3>
                    <p class="text-sm text-gray-600 mt-2">Pelajari informasi seputar tekanan akademik dan pengelolaannya.</p>
                    <span class="inline-block text-sm font-semibold text-teal-700 mt-4 group-hover:underline">Jelajahi edukasi →</span>
                </a>

                <a href="{{ route('informasi-bk') }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-teal-400 transition focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-amber-50 text-amber-800 text-xl font-bold" aria-hidden="true">?</span>
                    <h3 class="text-lg font-bold text-teal-900 mt-4">Informasi BK</h3>
                    <p class="text-sm text-gray-600 mt-2">Temukan informasi untuk mendapatkan dukungan Bimbingan dan Konseling.</p>
                    <span class="inline-block text-sm font-semibold text-teal-700 mt-4 group-hover:underline">Lihat informasi →</span>
                </a>

                <a href="{{ route('student.bk-appointments.index') }}" class="group bg-white rounded-xl border border-teal-100 shadow-sm p-5 hover:shadow-md hover:border-teal-400 transition focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-teal-50 text-teal-800 text-xl font-bold" aria-hidden="true">◷</span>
                    <h3 class="text-lg font-bold text-teal-900 mt-4">Jadwal Temu BK</h3>
                    <p class="text-sm text-gray-600 mt-2">Lihat jadwal pelayanan dan ajukan janji temu dengan Guru BK.</p>
                    <span class="inline-block text-sm font-semibold text-teal-700 mt-4 group-hover:underline">Lihat jadwal →</span>
                </a>
            </div>
        </section>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            <strong>Privasi dan penggunaan hasil:</strong> Skrining RuangRileks merupakan sarana informasi awal, bukan alat diagnosis. Hasil hanya disimpan apabila kamu menyetujui penyimpanan, dan hanya dapat dibagikan kepada Guru BK melalui persetujuan berbagi yang terpisah.
        </div>

    @elseif($role === 'guru_bk')
        <section>
            <div class="mb-4">
                <h2 class="text-xl font-bold text-teal-900">Layanan Guru BK</h2>
                <p class="text-sm text-gray-600 mt-1">Akses hasil yang secara khusus dibagikan oleh siswa dan informasi pendukung.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <a href="{{ route('bk.service-schedules.index') }}" class="group bg-white rounded-xl border border-teal-100 shadow-sm p-6 hover:shadow-md hover:border-teal-400 transition focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-50 text-teal-800 text-xl font-bold" aria-hidden="true">◷</span>
                    <h3 class="text-lg font-bold text-teal-900 mt-4">Jadwal Pelayanan & Janji Temu</h3>
                    <p class="text-sm text-gray-600 mt-2">Atur waktu layanan, lihat permintaan siswa, dan konfirmasi janji temu.</p>
                    <span class="inline-block text-sm font-semibold text-teal-700 mt-4 group-hover:underline">Kelola jadwal →</span>
                </a>
                <a href="{{ route('bk.shared-results.index') }}" class="group bg-white rounded-xl border border-teal-100 shadow-sm p-6 hover:shadow-md hover:border-teal-400 transition focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-50 text-teal-800 text-xl font-bold" aria-hidden="true">▤</span>
                    <h3 class="text-lg font-bold text-teal-900 mt-4">Hasil yang Dibagikan</h3>
                    <p class="text-sm text-gray-600 mt-2">Periksa daftar hasil skrining yang dibagikan siswa kepada akun Guru BK ini.</p>
                    <span class="inline-block text-sm font-semibold text-teal-700 mt-4 group-hover:underline">Buka daftar hasil →</span>
                </a>

                <a href="{{ route('informasi-bk') }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:border-teal-400 transition focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-50 text-amber-800 text-xl font-bold" aria-hidden="true">i</span>
                    <h3 class="text-lg font-bold text-teal-900 mt-4">Informasi BK</h3>
                    <p class="text-sm text-gray-600 mt-2">Buka halaman informasi layanan Bimbingan dan Konseling RuangRileks.</p>
                    <span class="inline-block text-sm font-semibold text-teal-700 mt-4 group-hover:underline">Lihat informasi →</span>
                </a>

                <a href="{{ route('profile') }}" class="group bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:border-teal-400 transition focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 text-gray-700 text-xl font-bold" aria-hidden="true">◎</span>
                    <h3 class="text-lg font-bold text-teal-900 mt-4">Profil Saya</h3>
                    <p class="text-sm text-gray-600 mt-2">Periksa identitas akun Guru BK yang sedang digunakan.</p>
                    <span class="inline-block text-sm font-semibold text-teal-700 mt-4 group-hover:underline">Buka profil →</span>
                </a>
            </div>
        </section>

        <div class="rounded-xl border border-teal-200 bg-teal-50 p-5 text-sm text-teal-900">
            <strong>Batas akses data siswa:</strong> Guru BK hanya dapat membuka hasil skrining yang secara aktif dibagikan oleh siswa kepada akun ini. Akses yang dicabut oleh siswa tidak lagi tersedia.
        </div>
    @else
        <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-teal-900">Layanan akun</h2>
            <p class="text-sm text-gray-600 mt-2">Silakan buka profil untuk memeriksa informasi akun atau kembali ke halaman utama RuangRileks.</p>
            <div class="flex flex-wrap gap-3 mt-5">
                <a href="{{ route('profile') }}" class="inline-flex rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-4 py-2.5 text-sm font-semibold">Lihat Profil</a>
                <a href="{{ route('beranda') }}" class="inline-flex rounded-lg border border-teal-700 text-teal-800 hover:bg-teal-50 px-4 py-2.5 text-sm font-semibold">Kembali ke Beranda</a>
            </div>
        </section>
    @endif
</div>
@endsection
