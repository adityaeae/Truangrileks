@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">Instrumen Skrining Stres Akademik</p>
        <h1 class="text-3xl sm:text-4xl font-bold mb-4">Informasi Skrining</h1>
        <p class="text-teal-100 text-lg max-w-3xl">Kuesioner ini menggunakan 16 pernyataan dari Skala Psikologi Stres Akademik pada Siswa SMA yang dipublikasikan oleh Sinadia dan Gandey (2024).</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 space-y-6">
    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <h2 class="text-xl font-bold text-teal-800 mb-3">Tujuan</h2>
        <p class="text-gray-700 leading-relaxed">Skrining ini membantu siswa mengenali pengalaman yang berkaitan dengan tekanan akademik. Instrumen memuat empat komponen awal: stres karena beban akademik yang berlebihan, stres dalam interaksi dengan teman sekelas, tekanan dari anggota keluarga, dan stres atas perspektif masa depan.</p>
    </section>

    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <h2 class="text-xl font-bold text-teal-800 mb-3">Tentang instrumen</h2>
        <p class="text-gray-700 leading-relaxed">Skala yang digunakan terdiri dari 16 butir berbahasa Indonesia dan diteliti pada 131 siswa kelas XI. Artikel melaporkan koefisien reliabilitas (alpha) sebesar 0,75. Analisis konstruk dalam artikel menemukan enam faktor, sementara scree plot menunjukkan dua faktor dominan. Karena itu, struktur faktor dan kesesuaian penggunaan pada populasi penelitian ini tetap perlu diperhatikan.</p>
        <p class="text-sm text-gray-600 mt-3">Sumber: Sinadia, A. R., &amp; Gandey, G. G. G. (2024). <em>Pembuktian Validitas dan Estimasi Reliabilitas Skala Psikologi Stres Akademik pada Siswa SMA.</em> SUMIKOLAH: Jurnal Ilmu Pendidikan, 2(2), 127–138.</p>
        <p class="mt-2"><a href="https://ejournal.unklab.ac.id/index.php/sumikolah/article/download/1154/913" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-teal-700 underline">Buka artikel sumber</a></p>
    </section>

    <section class="bg-amber-50 border border-amber-200 rounded-xl p-5 sm:p-6 text-amber-950">
        <h2 class="font-bold mb-2">Catatan penggunaan dan interpretasi</h2>
        <p class="text-sm leading-relaxed">Artikel sumber tidak menetapkan pembobotan skor total maupun nilai ambang untuk kategori rendah, sedang, atau tinggi. Karena itu, RuangRileks hanya menampilkan distribusi pilihan jawaban secara deskriptif dan tidak menghasilkan kategori tingkat tekanan, diagnosis, atau keputusan psikologis.</p>
        <p class="text-sm leading-relaxed mt-3"><strong>Perhatian bagi peneliti:</strong> butir nomor 12 pada Tabel 1 artikel tertulis “Saya merasa tidak nyaman ketika orang tua saya bertengkar hasil belajar saya yang buruk.” Redaksi ini tampak tidak lengkap. Konfirmasikan redaksinya kepada dosen pembimbing atau penulis sebelum digunakan dalam pengumpulan data penelitian sebenarnya. Versi pada aplikasi mempertahankan redaksi yang tertulis di artikel.</p>
    </section>

    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <h2 class="text-xl font-bold text-teal-800 mb-3">Cara mengisi</h2>
        <ol class="list-decimal list-inside text-gray-700 space-y-2">
            <li>Baca informasi dan persetujuan partisipasi dengan saksama.</li>
            <li>Jawab seluruh 16 pernyataan sesuai pengalamanmu.</li>
            <li>Pilih frekuensi yang paling menggambarkan pengalamanmu.</li>
            <li>Lihat ringkasan distribusi jawaban yang ditampilkan aplikasi.</li>
        </ol>
    </section>

    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <h2 class="text-xl font-bold text-teal-800 mb-4">Pilihan jawaban</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach(['Tidak pernah', 'Sangat jarang', 'Kadang-kadang', 'Sering', 'Selalu'] as $option)
                <div class="bg-cream-50 rounded-lg p-4 border border-gray-100">
                    <p class="font-semibold text-teal-800">{{ $option }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <div class="text-center">
        <form method="POST" action="{{ route('screening.consent') }}" class="inline-block">
            @csrf
            <input type="hidden" name="confirm_participation" value="1">
            <button type="submit" class="bg-teal-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-teal-500 transition">Lanjut ke Persetujuan</button>
        </form>
    </div>
</div>
@endsection
