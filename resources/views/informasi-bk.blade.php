@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-4">Informasi BK</h1>
        <p class="text-teal-100 text-lg max-w-3xl">Semua yang perlu kamu ketahui tentang layanan Bimbingan Konseling di sekolah.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-8">
    <div class="bg-white rounded-xl shadow-md p-8">
        <h2 class="text-2xl font-bold text-teal-800 mb-4">Layanan yang Tersedia</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-cream-50 rounded-lg p-6">
                <h3 class="font-semibold text-teal-700 mb-2">Konseling Individu</h3>
                <p class="text-gray-600 text-sm">Sesi tatap muka atau online dengan Guru BK untuk masalah pribadi, belajar, atau karir.</p>
            </div>
            <div class="bg-cream-50 rounded-lg p-6">
                <h3 class="font-semibold text-teal-700 mb-2">Konseling Kelompok</h3>
                <p class="text-gray-600 text-sm">Diskusi kelompok dengan tema tertentu, misalnya manajemen stres atau persiapan ujian.</p>
            </div>
            <div class="bg-cream-50 rounded-lg p-6">
                <h3 class="font-semibold text-teal-700 mb-2">Layanan Darurat</h3>
                <p class="text-gray-600 text-sm">Dukungan cepat untuk situasi krisis. Hubungi BK segera jika kamu membutuhkan bantuan sekarang.</p>
            </div>
            <div class="bg-cream-50 rounded-lg p-6">
                <h3 class="font-semibold text-teal-700 mb-2">Referrasi</h3>
                <p class="text-gray-600 text-sm">Rujukan ke profesional psikolog atau psychiatrist jika diperlukan.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-8">
        <h2 class="text-2xl font-bold text-teal-800 mb-4">Jadwal Layanan</h2>
        <p class="text-gray-700 mb-4">Guru BK tersedia pada hari kerja, pukul 08.00 - 15.00 WIB. Janji temu bisa diatur melalui aplikasi ini atau datang langsung ke ruang BK.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-teal-50 text-teal-700">
                    <tr>
                        <th class="px-4 py-3 rounded-tl-lg">Hari</th>
                        <th class="px-4 py-3">Jam</th>
                        <th class="px-4 py-3 rounded-tr-lg">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">Senin - Kamis</td>
                        <td class="px-4 py-3">08.00 - 15.00</td>
                        <td class="px-4 py-3">Konseling reguler</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">Jumat</td>
                        <td class="px-4 py-3">08.00 - 11.00</td>
                        <td class="px-4 py-3">Konseling & evaluasi</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-8">
        <h2 class="text-2xl font-bold text-teal-800 mb-4">Prosedur Konseling</h2>
        <ol class="list-decimal list-inside text-gray-700 space-y-3">
            <li>Daftar atau masuk ke aplikasi RuangRileks.</li>
            <li>Pilih layanan konseling sesuai kebutuhan.</li>
            <li>Isi form pendaftaran dan pilih jadwal yang tersedia.</li>
            <li>Terima konfirmasi dari Guru BK.</li>
            <li>Lakukan konseling sesuai jadwal.</li>
            <li>Akses materi edukasi tambahan setelah sesi jika diperlukan.</li>
        </ol>
    </div>
</div>
@endsection
