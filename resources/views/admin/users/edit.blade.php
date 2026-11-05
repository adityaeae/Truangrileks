@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-5">
    @include('admin.partials.nav')
    <div><p class="text-sm font-semibold text-teal-700">Manajemen Pengguna</p><h1 class="text-3xl font-bold text-teal-900 mt-1">Edit Akun</h1><p class="text-gray-600 mt-2">Role akun tidak dapat diubah melalui formulir ini.</p></div>
    @if($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('admin.users.update', $managedUser->id) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
        @csrf @method('PUT')
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Nama lengkap</label><input name="name" value="{{ old('name', $managedUser->name) }}" required maxlength="255" class="w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Email</label><input type="email" name="email" value="{{ old('email', $managedUser->email) }}" required class="w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Password baru (opsional)</label><input type="password" name="password" minlength="8" class="w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi password baru</label><input type="password" name="password_confirmation" class="w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600"></div>
        <div class="flex flex-wrap gap-2 pt-2"><button class="rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-5 py-2.5 font-semibold">Simpan Perubahan</button><a href="{{ route('admin.users.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 font-semibold text-gray-700">Kembali</a></div>
    </form>
</div>
@endsection
