@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-12 sm:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">Hasil yang Dibagikan kepada Anda</p>
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">Ringkasan Hasil Skrining</h1>
        <p class="text-teal-100">Siswa: {{ $share->student->name }}</p>
        <p class="text-teal-100 text-sm">Tanggal: {{ optional($share->screeningResult->completed_at ?? $share->screeningResult->created_at)->format('d/m/Y H:i') }}</p>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6">
        <div class="flex flex-wrap items-center gap-2 mb-3">
            <span class="rounded-full bg-teal-50 text-teal-800 px-3 py-1 text-xs font-semibold">{{ $isCurrentInstrument ? 'Instrumen penelitian' : 'DEMO versi lama' }}</span>
            <span class="text-xs text-gray-500">Dibagikan dengan persetujuan siswa pada {{ optional($share->consented_at)->format('d/m/Y H:i') }}</span>
        </div>
        @if($isCurrentInstrument)
            <h2 class="text-lg font-bold text-teal-900">{{ $meta['instrument_title'] ?? 'Skala Psikologi Stres Akademik pada Siswa SMA' }}</h2>
            <p class="text-sm text-gray-600 mt-2">{{ $meta['instrument_citation'] ?? '' }}</p>
            <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-950 space-y-2">
                <p>{{ $meta['scoring_note'] ?? 'Ringkasan jawaban ditampilkan secara deskriptif.' }}</p>
                <p>{{ $meta['disclaimer'] ?? 'Hasil ini bukan diagnosis.' }}</p>
            </div>
            @if(!empty($descriptiveProfile))
                <section class="mt-5 rounded-xl border border-teal-200 bg-teal-50 p-5 space-y-3">
                    <div>
                        <h3 class="font-bold text-teal-900">{{ $descriptiveProfile['title'] }}</h3>
                        <p class="text-xs text-teal-800 mt-1">Gambaran respons yang dibagikan siswa, bukan label diagnosis</p>
                    </div>
                    <p class="text-gray-800 leading-relaxed">{{ $descriptiveProfile['message'] }}</p>
                    <p class="text-xs text-gray-600">{{ $descriptiveProfile['method'] }}</p>
                    <p class="text-xs text-gray-600">{{ $descriptiveProfile['disclaimer'] }}</p>
                </section>
            @endif

            @if(!empty($meta['supplementary_impact']))
                <section class="mt-5 rounded-xl border border-gray-200 p-4 space-y-2">
                    <h3 class="font-semibold text-teal-900">Dampak pada kegiatan sehari-hari menurut siswa</h3>
                    <p class="text-sm text-gray-600">{{ $meta['supplementary_impact']['question'] }}</p>
                    <p class="inline-flex rounded-full bg-teal-50 text-teal-900 px-3 py-1.5 text-sm font-semibold">{{ $meta['supplementary_impact']['label'] }}</p>
                    <p class="text-xs text-gray-500">Ini adalah pertanyaan tambahan di luar 16 butir instrumen.</p>
                </section>
            @endif

            @if(!empty($followUpGuidance))
                <section class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-5 space-y-3">
                    <h3 class="font-bold text-teal-900">{{ $followUpGuidance['title'] }}</h3>
                    <p class="text-sm text-gray-800 leading-relaxed">{{ $followUpGuidance['message'] }}</p>
                    <ul class="list-disc list-inside text-sm text-gray-700 space-y-2">
                        @foreach($followUpGuidance['steps'] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ul>
                    @if(!empty($followUpGuidance['consultation_recommended']))
                        <p class="text-sm font-semibold text-teal-900">{{ $followUpGuidance['consultation_label'] }}</p>
                    @endif
                    <p class="text-xs text-gray-600">{{ $followUpGuidance['disclaimer'] }}</p>
                </section>
            @endif

            <div class="mt-5">
                <h3 class="font-semibold text-teal-900 mb-3">Distribusi pilihan jawaban keseluruhan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    @foreach($responseOptions as $code => $label)
                        <div class="rounded-lg bg-cream-50 border border-gray-100 p-3">
                            <p class="text-sm text-gray-600">{{ $label }}</p>
                            <p class="text-xl font-bold text-teal-800">{{ $meta['response_distribution'][$code] ?? 0 }}</p>
                            <p class="text-xs text-gray-500">respons</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-7">
                <div class="mb-3">
                    <h3 class="font-semibold text-teal-900">Distribusi jawaban per komponen awal</h3>
                    <p class="text-xs text-gray-500 mt-1">Jumlah respons pada setiap pilihan ditampilkan apa adanya. Ini bukan skor atau tingkat stres.</p>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($dimensionSummaries as $summary)
                        <article class="rounded-xl border border-gray-100 bg-white p-4">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <h4 class="font-semibold text-teal-900">{{ $summary['label'] }}</h4>
                                <span class="shrink-0 rounded-full bg-teal-50 text-teal-800 px-2.5 py-1 text-xs font-semibold">{{ $summary['answered_count'] }}/{{ $summary['item_count'] }} butir</span>
                            </div>
                            <div class="space-y-3">
                                @foreach($responseOptions as $code => $label)
                                    @php
                                        $responseCount = $summary['response_counts'][$code] ?? 0;
                                        $responseWidth = $summary['item_count'] > 0
                                            ? (int) round(($responseCount / $summary['item_count']) * 100)
                                            : 0;
                                    @endphp
                                    <div>
                                        <div class="flex items-center justify-between gap-3 text-sm">
                                            <span class="text-gray-600">{{ $label }}</span>
                                            <span class="font-semibold text-teal-900">{{ $responseCount }} respons</span>
                                        </div>
                                        <div class="mt-1.5 h-2 rounded-full bg-gray-100 overflow-hidden" role="img" aria-label="{{ $label }}: {{ $responseCount }} respons">
                                            <div class="h-full rounded-full bg-teal-600" style="width: {{ $responseWidth }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @else
            <p class="text-sm text-gray-700">Hasil ini berasal dari kuesioner DEMO versi terdahulu. Ringkasan angka bukan kategori atau tingkat tekanan.</p>
            @if(isset($meta['overall_percentage']))
                <p class="text-3xl font-bold text-teal-800 mt-3">{{ $meta['overall_percentage'] }}%</p>
            @endif
            <p class="text-xs text-gray-500 mt-3">Hasil lama dipertahankan agar tetap dapat dibaca dan tidak dicampur dengan instrumen saat ini.</p>
        @endif
    </section>

    <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6">
        <h2 class="text-xl font-bold text-teal-900 mb-2">Jawaban kuesioner</h2>
        <p class="text-sm text-gray-600 mb-5">Berikut pernyataan dan jawaban yang secara khusus dibagikan oleh siswa.</p>
        <div class="space-y-6">
            @foreach($dimensions as $dimensionKey => $dimensionLabel)
                <div>
                    <h3 class="font-semibold text-teal-800 mb-3">{{ $dimensionLabel }}</h3>
                    <div class="space-y-3">
                        @foreach($questions[$dimensionKey] ?? [] as $question)
                            <article class="rounded-lg border border-gray-100 bg-cream-50 p-4">
                                <p class="text-sm text-gray-800"><span class="font-semibold">{{ $question['number'] ?? $question['code'] }}.</span> {{ $question['text'] }}</p>
                                <p class="text-sm font-semibold text-teal-800 mt-2">Jawaban: {{ $question['answer_label'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="text-center">
        <a href="{{ route('bk.shared-results.index') }}" class="text-sm font-semibold text-teal-800 hover:text-teal-600">Kembali ke daftar hasil yang dibagikan</a>
    </div>
</div>
@endsection
