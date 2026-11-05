@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <a href="{{ route($routePrefix.'.service-schedules.index') }}" class="text-sm font-semibold text-teal-800 hover:underline">← Kembali ke jadwal layanan</a>
    <div class="mt-5 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-teal-900">Tambah Jadwal Layanan BK</h1>
        <p class="text-sm text-gray-600 mt-2">Isi waktu dan lokasi layanan yang benar-benar tersedia. Jadwal yang dipublikasikan dapat diminta oleh siswa.</p>
        @include('service-schedules.form', ['schedule' => null, 'formAction' => route($routePrefix.'.service-schedules.store'), 'formMethod' => 'POST'])
    </div>
</div>
@endsection
