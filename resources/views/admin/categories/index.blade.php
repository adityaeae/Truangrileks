@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-10"><div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8"><p class="text-teal-100 text-sm font-semibold mb-2">Administrasi Edukasi</p><h1 class="text-3xl font-bold">Kategori Artikel</h1><p class="text-teal-100 mt-2">Kelola kelompok materi yang ditampilkan pada halaman Edukasi.</p></div></div>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
    @include('admin.partials.nav')
    @if(session('status'))<div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-800" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-lg font-bold text-teal-900 mb-3">Tambah kategori</h2>
        <form method="POST" action="{{ route('admin.categories.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">@csrf<input name="name" required maxlength="100" value="{{ old('name') }}" placeholder="Nama kategori" class="rounded-lg border-gray-300"><input name="description" maxlength="1000" value="{{ old('description') }}" placeholder="Deskripsi singkat (opsional)" class="rounded-lg border-gray-300"><button class="rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-4 py-2 font-semibold">Tambah Kategori</button></form>
    </section>
    <section class="space-y-3">
        @forelse($categories as $category)
            <article class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-3">
                <div class="flex items-center justify-between gap-2"><span class="text-xs text-gray-500">{{ $category->articles_count }} artikel terkait · slug: {{ $category->slug }}</span></div>
                <form method="POST" action="{{ route('admin.categories.update', $category->id) }}" class="grid grid-cols-1 md:grid-cols-[1fr_2fr_auto] gap-3">@csrf @method('PUT')<input name="name" value="{{ $category->name }}" required maxlength="100" class="rounded-lg border-gray-300"><input name="description" value="{{ $category->description }}" maxlength="1000" placeholder="Deskripsi singkat" class="rounded-lg border-gray-300"><button class="rounded-lg border border-teal-700 text-teal-800 px-4 py-2 text-sm font-semibold">Simpan</button></form>
                <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}" onsubmit="return confirm('Hapus kategori? Artikel terkait tetap ada dan akan menjadi tanpa kategori.');">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-700 hover:underline">Hapus kategori</button></form>
            </article>
        @empty
            <div class="bg-white rounded-xl border border-gray-100 p-8 text-center text-gray-600">Belum ada kategori artikel.</div>
        @endforelse
        {{ $categories->links() }}
    </section>
</div>
@endsection
