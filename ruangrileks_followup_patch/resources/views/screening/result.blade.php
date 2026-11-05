@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">RuangRileks · Skrining Stres Akademik</p>
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">Ringkasan Hasil Skrining</h1>
        <p class="text-teal-100 text-lg max-w-3xl">Berikut ringkasan respons dari pengisian kuesioner. Hasil ini tidak digunakan untuk menetapkan diagnosis atau kategori tingkat tekanan akademik.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 space-y-6">
    @if(!empty($meta['legacy_unavailable']))
        <section class="bg-white rounded-xl border border-amber-200 p-6">
            <h2 class="text-lg font-bold text-amber-900 mb-2">Hasil versi sebelumnya</h2>
            <p class="text-gray-700">{{ $meta['disclaimer'] }}</p>
        </section>
    @elseif($isCurrentInstrument)
        <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-7 space-y-5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-teal-50 text-teal-800 px-3 py-1 text-xs font-semibold">Instrumen penelitian</span>
                <span class="text-xs text-gray-500">{{ $meta['answered_items'] ?? 16 }} dari {{ $meta['total_items'] ?? 16 }} butir terjawab</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-teal-900">{{ $instrumentTitle }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $meta['instrument_citation'] ?? '' }}</p>
            </div>
            <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-950 space-y-2">
                <p class="font-semibold">Cara membaca ringkasan</p>
                <p>{{ $meta['scoring_note'] ?? 'Ringkasan ini hanya menampilkan frekuensi pilihan jawaban secara deskriptif.' }}</p>
                <p>{{ $meta['disclaimer'] ?? 'Hasil ini bukan diagnosis.' }}</p>
            </div>

            @if(!empty($meta['descriptive_profile']))
                <section class="rounded-xl border border-teal-200 bg-teal-50 p-5 sm:p-6 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center rounded-full bg-white text-teal-800 w-9 h-9" aria-hidden="true">&#10003;</span>
                        <div>
                            <h3 class="font-bold text-teal-900">{{ $meta['descriptive_profile']['title'] }}</h3>
                            <p class="text-xs text-teal-800 mt-0.5">Gambaran respons, bukan label diagnosis</p>
                        </div>
                    </div>
                    <p class="text-gray-800 leading-relaxed">{{ $meta['descriptive_profile']['message'] }}</p>
                    <p class="text-xs text-gray-600">{{ $meta['descriptive_profile']['method'] }}</p>
                    <p class="text-xs text-gray-600">{{ $meta['descriptive_profile']['disclaimer'] }}</p>
                </section>
            @endif

            @if(!empty($meta['supplementary_impact']))
                <section class="rounded-xl border border-gray-200 bg-white p-5 space-y-2">
                    <h3 class="font-bold text-teal-900">Dampak terhadap kegiatan sehari-hari</h3>
                    <p class="text-sm text-gray-600">{{ $meta['supplementary_impact']['question'] }}</p>
                    <p class="inline-flex rounded-full bg-teal-50 text-teal-900 px-3 py-1.5 text-sm font-semibold">{{ $meta['supplementary_impact']['label'] }}</p>
                    <p class="text-xs text-gray-500">Pertanyaan tambahan ini berada di luar 16 butir instrumen dan tidak dihitung dalam distribusi jawaban skala.</p>
                </section>
            @endif

            @if(!empty($meta['follow_up_guidance']))
                <section class="rounded-xl border border-teal-200 bg-teal-50 p-5 sm:p-6 space-y-3">
                    <div>
                        <h3 class="text-lg font-bold text-teal-900">{{ $meta['follow_up_guidance']['title'] }}</h3>
                        <p class="text-gray-800 leading-relaxed mt-2">{{ $meta['follow_up_guidance']['message'] }}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-teal-900 mb-2">Langkah yang dapat kamu lakukan</h4>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-2">
                            @foreach($meta['follow_up_guidance']['steps'] as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @if(!empty($meta['follow_up_guidance']['consultation_recommended']))
                        <div class="rounded-lg border border-teal-200 bg-white p-4 space-y-2">
                            <p class="font-semibold text-teal-900">Pertimbangkan untuk berkonsultasi</p>
                            <p class="text-sm text-gray-700">{{ $meta['follow_up_guidance']['consultation_label'] }}</p>
                            @if($canBookBkAppointment)
                                <a href="{{ route('student.bk-appointments.index') }}" class="inline-flex bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-teal-600 transition">Lihat Jadwal dan Buat Janji BK</a>
                            @else
                                <a href="{{ route('informasi-bk') }}" class="inline-flex bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-teal-600 transition">Lihat Informasi Layanan BK</a>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-teal-900">{{ $meta['follow_up_guidance']['consultation_label'] }}</p>
                    @endif
                    <p class="text-xs text-gray-600">{{ $meta['follow_up_guidance']['disclaimer'] }}</p>
                </section>
            @elseif($isCurrentInstrument)
                <section class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                    Pertanyaan tambahan tentang dampak aktivitas belum tersedia pada hasil pengisian ini. Rekomendasi tindak lanjut belum dapat ditampilkan.
                </section>
            @endif

            <div>
                <h3 class="font-bold text-teal-900 mb-3">Distribusi pilihan jawaban keseluruhan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    @foreach($responseOptions as $code => $label)
                        <div class="rounded-lg bg-cream-50 border border-gray-100 p-4">
                            <p class="text-sm text-gray-600">{{ $label }}</p>
                            <p class="text-2xl font-bold text-teal-800 mt-1">{{ $meta['response_distribution'][$code] ?? 0 }}</p>
                            <p class="text-xs text-gray-500">respons</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <h3 class="font-bold text-teal-900 mb-3">Distribusi respons per komponen awal</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($dimensions as $key => $label)
                        @php
                            $dimensionSummary = $meta['dimensions'][$key] ?? [];
                        @endphp
                        <article class="rounded-lg border border-gray-100 bg-white p-4">
                            <h4 class="font-semibold text-teal-800 mb-3">{{ $label }}</h4>
                            <p class="text-xs text-gray-500 mb-2">{{ $dimensionSummary['item_count'] ?? 0 }} butir</p>
                            <div class="space-y-2">
                                @foreach($responseOptions as $code => $responseLabel)
                                    <div class="flex items-center justify-between gap-4 text-sm">
                                        <span class="text-gray-600">{{ $responseLabel }}</span>
                                        <span class="font-semibold text-gray-900 tabular-nums">{{ $dimensionSummary['response_counts'][$code] ?? 0 }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-7">
            <h2 class="text-xl font-bold text-teal-900 mb-2">{{ $instrumentTitle }}</h2>
            <p class="text-sm text-gray-700">Ini adalah hasil dari kuesioner DEMO versi terdahulu. Ringkasan persentase di bawah hanya perhitungan aritmetika lama untuk demonstrasi aplikasi dan tidak boleh ditafsirkan sebagai tingkat tekanan akademik.</p>
            @if(isset($meta['overall_percentage']))
                <p class="text-3xl font-bold text-teal-800 mt-4">{{ $meta['overall_percentage'] }}%</p>
                <p class="text-sm text-gray-600">Angka DEMO lama: {{ $meta['total_score'] ?? 0 }} / {{ $meta['total_max'] ?? 0 }}</p>
            @endif
            <p class="text-xs text-gray-500 mt-4">Hasil lama dipertahankan agar riwayat sebelumnya tetap dapat dibaca. Kuesioner aktif sekarang menggunakan skala Sinadia dan Gandey (2024).</p>
        </section>
    @endif

    @if($result && !empty($answerDetails))
        <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-7">
            <h2 class="text-xl font-bold text-teal-900 mb-2">Jawaban yang tersimpan</h2>
            <p class="text-sm text-gray-600 mb-5">Bagian ini hanya dapat dilihat oleh siswa pemilik hasil yang memberikan persetujuan penyimpanan.</p>
            <div class="space-y-6">
                @foreach($dimensions as $dimensionKey => $dimensionLabel)
                    @if(!empty($answerDetails[$dimensionKey]))
                        <section>
                            <h3 class="font-semibold text-teal-800 mb-3">{{ $dimensionLabel }}</h3>
                            <div class="space-y-3">
                                @foreach($answerDetails[$dimensionKey] as $item)
                                    <article class="rounded-lg border border-gray-100 bg-cream-50 p-4">
                                        <p class="text-sm text-gray-800">@if(!empty($item['code']))<span class="font-semibold text-teal-800">{{ $item['code'] }}.</span> @endif{{ $item['question'] }}</p>
                                        <p class="mt-2 text-sm font-semibold text-teal-800">Jawaban: {{ $item['answer'] }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-7">
        <h2 class="text-xl font-bold text-teal-900 mb-3">Informasi lanjutan</h2>
        <p class="text-gray-700 mb-4">Untuk memahami pengalaman akademik dan mencari dukungan, Anda dapat:</p>
        <ul class="list-disc list-inside text-gray-700 space-y-2">
            <li>Membaca materi edukasi di halaman Edukasi.</li>
            <li>Membaca informasi layanan Bimbingan dan Konseling.</li>
            <li>Menghubungi Guru BK atau orang tepercaya jika membutuhkan dukungan.</li>
        </ul>
        @if($consentStorage)
            <p class="text-sm text-teal-800 mt-4">Jawaban dan ringkasan ini tersimpan pada akun siswa berdasarkan persetujuan penyimpanan.</p>
        @else
            <p class="text-sm text-gray-600 mt-4">Jawaban mentah tidak disimpan permanen. Ringkasan distribusi ini hanya tersedia sementara dalam sesi browser.</p>
        @endif
    </section>

    <div class="text-center flex flex-wrap justify-center gap-3">
        @if($result)
            <a href="{{ route('screening-history.index') }}" class="bg-teal-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-teal-600 transition">Kembali ke Riwayat</a>
        @endif
        <a href="{{ route('edukasi') }}" class="bg-teal-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-teal-500 transition">Edukasi</a>
        <a href="{{ route('informasi-bk') }}" class="border border-teal-600 text-teal-700 px-6 py-3 rounded-lg font-semibold hover:bg-teal-50 transition">Informasi BK</a>
        <button type="button" onclick="window.print()" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition">Cetak hasil</button>
    </div>
</div>
@endsection
