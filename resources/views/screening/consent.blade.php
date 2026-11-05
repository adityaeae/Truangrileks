@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">Persetujuan Skrining</h1>
        <p class="text-teal-100 text-lg max-w-3xl">Sebelum melanjutkan, baca pilihan persetujuan berikut. Setiap pilihan memiliki tujuan yang berbeda.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-7">
        <form method="POST" action="{{ route('screening.consent.store') }}" class="space-y-6">
            @csrf

            <div class="bg-cream-50 rounded-lg p-5">
                <h2 class="text-lg font-bold text-teal-800 mb-2">1. Persetujuan partisipasi</h2>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="consent_participation" value="1" required class="mt-1 w-5 h-5 text-teal-600 border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Saya telah membaca informasi skrining dan bersedia mengikuti pengisian Skala Psikologi Stres Akademik pada Siswa SMA secara sukarela. Saya memahami bahwa hasil aplikasi berupa ringkasan deskriptif, bukan diagnosis atau penetapan tingkat tekanan akademik.</span>
                </label>
                @error('consent_participation')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-cream-50 rounded-lg p-5">
                <h2 class="text-lg font-bold text-teal-800 mb-2">2. Persetujuan penyimpanan hasil</h2>
                @auth
                    @if(auth()->user()->role === 'siswa')
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="consent_storage" value="1" class="mt-1 w-5 h-5 text-teal-600 border-gray-300 rounded">
                            <span class="text-sm text-gray-700">Saya secara sukarela menyetujui jawaban dan ringkasan distribusi respons ini disimpan pada akun siswa saya agar dapat dilihat kembali.</span>
                        </label>
                        <p class="text-xs text-gray-600 mt-3">Jika tidak dicentang, jawaban mentah tidak disimpan permanen. Ringkasan distribusi hanya ditampilkan sementara dalam sesi ini.</p>
                    @else
                        <p class="text-sm text-gray-700">Akun Anda bukan akun siswa. Jawaban mentah tidak akan disimpan permanen dan ringkasan hanya ditampilkan sementara.</p>
                    @endif
                @else
                    <p class="text-sm text-gray-700">Penyimpanan permanen hanya tersedia bagi siswa yang sudah login dan memberikan persetujuan. Anda dapat <a href="{{ route('login') }}" class="text-teal-700 underline font-semibold">login</a> atau <a href="{{ route('register') }}" class="text-teal-700 underline font-semibold">mendaftar sebagai siswa</a> terlebih dahulu. Tanpa login, jawaban mentah tidak disimpan permanen.</p>
                @endauth
                @error('consent_storage')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-cream-50 rounded-lg p-5">
                <h2 class="text-lg font-bold text-teal-800 mb-2">3. Persetujuan berbagi hasil</h2>
                <p class="text-sm text-gray-700">Persetujuan berbagi hasil kepada Guru BK tidak diminta pada tahap ini. Jika fitur berbagi digunakan, persetujuan akan diminta secara terpisah untuk hasil tertentu dan Guru BK yang dipilih.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="flex-1 bg-teal-700 text-white py-3 rounded-lg font-semibold hover:bg-teal-600 transition">Lanjut ke Kuesioner</button>
                <a href="{{ route('beranda') }}" class="flex-1 text-center border border-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-50 transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
