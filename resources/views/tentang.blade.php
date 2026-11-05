@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-4">Tentang RuangRileks</h1>
        <p class="text-teal-100 text-lg max-w-3xl">Platform digital yang dirancang untuk membantu siswa memahami kondisi psikologis dan mendapatkan dukungan yang tepat.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-12">
    <div class="bg-white rounded-xl shadow-md p-8">
        <h2 class="text-2xl font-bold text-teal-800 mb-4">Visi</h2>
        <p class="text-gray-700 leading-relaxed">Menjadi ruang aman bagi setiap siswa untuk memahami diri, mendapatkan informasi yang valid, dan mengakses layanan bimbingan konseling dengan mudah.</p>
    </div>
    <div class="bg-white rounded-xl shadow-md p-8">
        <h2 class="text-2xl font-bold text-teal-800 mb-4">Misi</h2>
        <ul class="list-disc list-inside text-gray-700 space-y-2">
            <li>Menyediakan edukasi kesehatan mental yang mudah dipahami.</li>
            <li>Mendukung deteksi dini melalui skrining yang terstruktur.</li>
            <li>Menghubungkan siswa dengan layanan BK secara efektif.</li>
            <li>Menjaga privasi dan kenyamanan pengguna di setiap langkah.</li>
        </ul>
    </div>
    <div class="bg-white rounded-xl shadow-md p-8">
        <h2 class="text-2xl font-bold text-teal-800 mb-4">Nilai</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-cream-50 rounded-lg p-6">
                <h3 class="font-semibold text-teal-700 mb-2">Empati</h3>
                <p class="text-gray-600 text-sm">Setiap interaksi didasari pengertian dan penghargaan terhadap perasaan.</p>
            </div>
            <div class="bg-cream-50 rounded-lg p-6">
                <h3 class="font-semibold text-teal-700 mb-2">Kerahasiaan</h3>
                <p class="text-gray-600 text-sm">Privasi pengguna dijamin dan dilindungi.</p>
            </div>
            <div class="bg-cream-50 rounded-lg p-6">
                <h3 class="font-semibold text-teal-700 mb-2">Aksesibilitas</h3>
                <p class="text-gray-600 text-sm">Layanan mudah dijangkau oleh semua siswa.</p>
            </div>
            <div class="bg-cream-50 rounded-lg p-6">
                <h3 class="font-semibold text-teal-700 mb-2">Kelayakan</h3>
                <p class="text-gray-600 text-sm">Informasi dan rekomendasi berbasis bukti dan prosedur BK.</p>
            </div>
        </div>
    </div>
</div>
@endsection
