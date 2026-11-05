@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-12 sm:py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">Panel Administrator</p>
        <h1 class="text-3xl sm:text-4xl font-bold">Dasbor Admin</h1>
        <p class="text-teal-100 mt-3 max-w-3xl">Kelola akun pengguna dan konten edukasi RuangRileks. Panel ini tidak menyediakan akses ke jawaban maupun hasil skrining pribadi siswa.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-9 space-y-8">
    @include('admin.partials.nav')

    @if(session('status'))
        <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-800" role="status">{{ session('status') }}</div>
    @endif

    <section>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
            <div>
                <h2 class="text-xl font-bold text-teal-900">Ringkasan administrasi</h2>
                <p class="text-sm text-gray-600 mt-1">Informasi akun dan konten, bukan data skrining siswa.</p>
            </div>
            <span class="text-xs text-gray-500">Diperbarui saat halaman dibuka</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach([
                ['label' => 'Akun siswa', 'value' => $studentCount, 'detail' => 'Seluruh akun siswa'],
                ['label' => 'Akun Guru BK', 'value' => $bkCount, 'detail' => 'Seluruh akun guru BK'],
                ['label' => 'Akun aktif', 'value' => $activeAccountCount, 'detail' => 'Siswa dan Guru BK'],
                ['label' => 'Akun nonaktif', 'value' => $inactiveAccountCount, 'detail' => 'Siswa dan Guru BK'],
                ['label' => 'Artikel terbit', 'value' => $publishedArticleCount, 'detail' => 'Tampil pada halaman edukasi'],
                ['label' => 'Artikel draft', 'value' => $draftArticleCount, 'detail' => 'Belum ditampilkan ke publik'],
            ] as $stat)
                <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-sm text-gray-600">{{ $stat['label'] }}</p>
                    <p class="text-3xl font-bold text-teal-800 mt-2">{{ number_format($stat['value']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stat['detail'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-teal-900">Manajemen Pengguna</h2>
            <p class="text-sm text-gray-600 mt-2">Lihat akun siswa dan Guru BK, perbarui identitas dasar, serta aktifkan atau nonaktifkan akun. Akun tidak dihapus agar keterkaitan data tetap terjaga.</p>
            <a href="{{ route('admin.users.index') }}" class="inline-flex mt-5 rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-4 py-2.5 text-sm font-semibold">Kelola Pengguna</a>
        </article>
        <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-teal-900">Jadwal layanan BK</h2>
            <p class="text-sm text-gray-600 mt-2">Atur ketersediaan waktu layanan untuk Guru BK aktif. Panel admin hanya mengelola jadwal dan tidak menampilkan catatan pribadi janji temu siswa.</p>
            <a href="{{ route('admin.service-schedules.index') }}" class="inline-flex mt-5 rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-4 py-2.5 text-sm font-semibold">Kelola Jadwal BK</a>
        </article>
        <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-teal-900">Manajemen edukasi</h2>
            <p class="text-sm text-gray-600 mt-2">Buat, perbarui, terbitkan, atau arsipkan artikel edukasi serta kelola kategori yang ditampilkan pada halaman publik.</p>
            <div class="flex flex-wrap gap-2 mt-5">
                <a href="{{ route('admin.articles.index') }}" class="inline-flex rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-4 py-2.5 text-sm font-semibold">Kelola Artikel</a>
                <a href="{{ route('admin.categories.index') }}" class="inline-flex rounded-lg border border-teal-700 text-teal-800 hover:bg-teal-50 px-4 py-2.5 text-sm font-semibold">Kelola Kategori</a>
            </div>
        </article>
    </section>

    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
        <strong>Batas akses privasi:</strong> Administrator hanya mengelola akun dan konten. Dashboard ini tidak menampilkan jumlah, isi, jawaban, atau ringkasan hasil skrining individual siswa.
    </div>
</div>
@endsection
