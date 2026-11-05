@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <a href="{{ route($routePrefix.'.service-schedules.index') }}" class="text-sm font-semibold text-teal-800 hover:underline">← Kembali ke jadwal layanan</a>
    <div class="mt-5 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-teal-900">Edit Jadwal Layanan BK</h1>
        <p class="text-sm text-gray-600 mt-2">Perbarui informasi jadwal. Waktu, Guru BK, moda, dan lokasi tidak dapat diubah selama masih ada janji temu yang menunggu atau telah dikonfirmasi.</p>
        @if($activeAppointmentCount > 0)
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">Ada {{ $activeAppointmentCount }} janji temu aktif pada jadwal ini.</div>
        @endif
        @include('service-schedules.form', ['formAction' => route($routePrefix.'.service-schedules.update', $schedule->id), 'formMethod' => 'PUT'])
    </div>
</div>
@endsection
