@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-10"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><p class="text-teal-100 text-sm font-semibold mb-2">Administrasi Edukasi</p><h1 class="text-3xl font-bold">Manajemen Artikel</h1><p class="text-teal-100 mt-2">Artikel berstatus terbit dapat dibaca pengunjung pada halaman Edukasi.</p></div></div>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
    @include('admin.partials.nav')
    @if(session('status'))<div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-800" role="status">{{ session('status') }}</div>@endif
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div><h2 class="text-lg font-bold text-teal-900">Daftar artikel</h2><p class="text-sm text-gray-600">Draft hanya terlihat oleh admin.</p></div>
        <a href="{{ route('admin.articles.create') }}" class="inline-flex justify-center rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-4 py-2.5 text-sm font-semibold">Tambah Artikel</a>
    </div>
    <form method="GET" action="{{ route('admin.articles.index') }}" class="flex flex-wrap gap-2"><select name="status" class="rounded-lg border-gray-300"><option value="">Semua status</option><option value="draft" @selected($status === 'draft')>Draft</option><option value="published" @selected($status === 'published')>Terbit</option></select><button class="rounded-lg border border-teal-700 text-teal-800 px-4 py-2 text-sm font-semibold">Filter</button><a href="{{ route('admin.articles.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold">Reset</a></form>
    <section class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        @if($articles->isEmpty())<div class="p-8 text-center text-gray-600">Belum ada artikel. Tambahkan artikel pertama untuk ditampilkan pada halaman Edukasi.</div>
        @else
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-100 text-sm"><thead class="bg-cream-50 text-gray-600"><tr><th class="px-5 py-3 text-left">Artikel</th><th class="px-5 py-3 text-left">Kategori</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Diperbarui</th><th class="px-5 py-3 text-right">Tindakan</th></tr></thead><tbody class="divide-y divide-gray-100">
                @foreach($articles as $article)<tr><td class="px-5 py-4"><p class="font-semibold text-teal-900">{{ $article->title }}</p><p class="text-xs text-gray-500">/edukasi/{{ $article->slug }}</p></td><td class="px-5 py-4 text-gray-600">{{ $article->category?->name ?? 'Tanpa kategori' }}</td><td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $article->is_published ? 'bg-green-50 text-green-800' : 'bg-amber-50 text-amber-800' }}">{{ $article->is_published ? 'Terbit' : 'Draft' }}</span></td><td class="px-5 py-4 text-gray-600">{{ $article->updated_at->copy()->timezone('Asia/Jakarta')->format('d/m/Y') }}</td><td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('admin.articles.edit', $article->id) }}" class="rounded-lg border border-teal-700 px-3 py-2 text-xs font-semibold text-teal-800">Edit</a><form method="POST" action="{{ route('admin.articles.destroy', $article->id) }}" onsubmit="return confirm('Hapus artikel ini?');">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700">Hapus</button></form></div></td></tr>@endforeach
            </tbody></table></div><div class="p-4">{{ $articles->links() }}</div>
        @endif
    </section>
    <div class="text-sm"><a href="{{ route('edukasi') }}" class="font-semibold text-teal-800 hover:underline">Lihat halaman Edukasi publik</a></div>
</div>
@endsection
