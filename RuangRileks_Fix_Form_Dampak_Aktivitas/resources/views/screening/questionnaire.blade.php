@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">Skala Psikologi Stres Akademik - Sinadia &amp; Gandey (2024)</p>
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">Kuesioner Stres Akademik</h1>
        <p class="text-teal-100 text-lg max-w-3xl">Jawab 16 pernyataan berikut berdasarkan frekuensi pengalaman yang kamu rasakan.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
    <form method="POST" action="{{ route('screening.submit') }}" class="max-w-4xl mx-auto space-y-6">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-sm text-amber-950">
            <strong>Informasi penting:</strong> aplikasi menampilkan distribusi jawaban secara deskriptif. Tidak ada penjumlahan skor atau kategori tingkat tekanan karena artikel sumber tidak menetapkan pedoman skor dan nilai ambang. Kuesioner ini bukan alat diagnosis.
        </div>
        @if($errors->has('answers'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">{{ $errors->first('answers') }}</div>
        @endif
        @csrf

        @foreach($dimensions as $key => $dimensionLabel)
            <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sm:p-6">
                <h2 class="text-xl font-bold text-teal-800 mb-5">{{ $dimensionLabel }}</h2>
                <div class="space-y-6">
                    @foreach(($questions[$key] ?? []) as $question)
                        <fieldset class="border-b border-gray-100 last:border-0 pb-5 last:pb-0">
                            <legend class="font-medium text-gray-800 mb-3">
                                <span class="text-teal-700 font-bold">{{ $question['number'] }}.</span>
                                {{ $question['text'] }}
                            </legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($responseOptions as $value => $label)
                                    <label class="flex items-center gap-3 bg-cream-50 px-3 py-3 rounded-lg border border-gray-200 cursor-pointer hover:border-teal-400 transition">
                                        <input type="radio" name="answers[{{ $question['code'] }}]" value="{{ $value }}" required class="text-teal-600 focus:ring-teal-500">
                                        <span class="text-sm text-gray-800">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('answers.' . $question['code'])
                                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </fieldset>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="bg-white rounded-xl shadow-sm border border-teal-100 p-5 sm:p-6 space-y-4">
            <div>
                <span class="inline-flex rounded-full bg-amber-50 text-amber-800 px-3 py-1 text-xs font-semibold">Pertanyaan tambahan · di luar 16 butir instrumen</span>
                <h2 class="text-xl font-bold text-teal-900 mt-3">Dampak dalam kegiatan sehari-hari</h2>
                <p class="text-sm text-gray-600 mt-2">{{ $dailyImpactQuestion }}</p>
                <p class="text-xs text-gray-500 mt-1">Jawaban ini tidak dihitung dalam distribusi 16 butir. Jawaban digunakan untuk memberikan rekomendasi tindak lanjut yang lebih sesuai dengan kebutuhanmu.</p>
            </div>
            <fieldset>
                <legend class="font-semibold text-gray-800 mb-3">Pilih satu jawaban</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($dailyImpactOptions as $value => $label)
                        <label class="flex items-center gap-3 bg-cream-50 px-3 py-3 rounded-lg border border-gray-200 cursor-pointer hover:border-teal-400 transition">
                            <input type="radio" name="daily_impact" value="{{ $value }}" required class="text-teal-600 focus:ring-teal-500">
                            <span class="text-sm text-gray-800">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('daily_impact')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </fieldset>
        </section>

        <div class="rounded-xl bg-white border border-gray-100 p-5 text-sm text-gray-600">
            Pastikan seluruh pernyataan telah dijawab. Jawaban tidak akan disimpan permanen kecuali kamu login sebagai siswa dan menyetujui penyimpanan pada halaman persetujuan.
        </div>
        <div class="text-center">
            <button type="submit" class="bg-teal-700 text-white px-8 py-3 rounded-lg font-semibold hover:bg-teal-600 transition">Kirim Jawaban dan Lihat Ringkasan</button>
        </div>
    </form>
</div>
@endsection
