@extends('layouts.app')

@section('content')
<section class="home-hero bg-teal-700 text-white py-14 sm:py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mb-4">
            Selamat Datang di RuangRileks
        </h1>
        <p class="text-teal-100 text-lg sm:text-xl max-w-4xl mx-auto leading-relaxed">
            Platform edukasi dan skrining awal untuk membantu siswa mengenali tekanan akademik,
            memahami faktor yang memengaruhinya, dan menemukan informasi dukungan Bimbingan dan Konseling.
        </p>

        <div class="home-hero-actions mt-8 flex flex-col sm:flex-row flex-wrap justify-center items-stretch sm:items-center gap-3 sm:gap-4">
            <a href="{{ route('screening.info') }}"
               class="inline-flex min-h-12 items-center justify-center rounded-lg bg-white px-6 py-3 font-semibold text-teal-700 shadow-sm transition hover:bg-cream-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                Mulai Skrining
            </a>
            <a href="{{ route('edukasi') }}"
               class="inline-flex min-h-12 items-center justify-center rounded-lg border border-white px-6 py-3 font-semibold text-white transition hover:bg-teal-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                Jelajahi Edukasi
            </a>
            <a href="{{ route('informasi-bk') }}"
               class="inline-flex min-h-12 items-center justify-center rounded-lg border border-teal-200/70 px-6 py-3 font-semibold text-white transition hover:bg-teal-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                Informasi BK
            </a>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14" aria-label="Layanan RuangRileks">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-start">
        <article class="home-feature-card rounded-xl border-t-4 border-teal-500 bg-white p-6 sm:p-7 shadow-md">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-teal-100 text-teal-700" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h2 class="mb-2 text-xl font-bold text-teal-800">Edukasi</h2>
            <p class="leading-relaxed text-gray-600">
                Pelajari tekanan akademik, ekspektasi orang tua, pengaruh lingkungan pertemanan,
                serta dukungan guru dalam proses belajar.
            </p>
        </article>

        <article class="home-feature-card rounded-xl border-t-4 border-cream-400 bg-white p-6 sm:p-7 shadow-md">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-cream-100 text-teal-800" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h2 class="mb-2 text-xl font-bold text-teal-800">Informasi BK</h2>
            <p class="leading-relaxed text-gray-600">
                Temukan informasi mengenai layanan, prosedur, dan peran Guru Bimbingan dan Konseling
                dalam mendampingi siswa di sekolah.
            </p>
        </article>

        <article class="home-feature-card rounded-xl border-t-4 border-teal-500 bg-white p-6 sm:p-7 shadow-md">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-teal-100 text-teal-700" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <h2 class="mb-2 text-xl font-bold text-teal-800">Skrining Stres Akademik</h2>
            <p class="mb-5 leading-relaxed text-gray-600">
                Isi 16 pernyataan Skala Psikologi Stres Akademik dari Sinadia dan Gandey (2024). Ringkasan jawaban ditampilkan secara deskriptif, bukan sebagai diagnosis atau kategori tingkat tekanan.
            </p>
            <a href="{{ route('screening.info') }}"
               class="mt-auto inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-teal-600 px-4 py-2 font-semibold text-white transition hover:bg-teal-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700 sm:w-fit">
                Mulai Skrining
            </a>
        </article>
    </div>
</section>
@endsection
