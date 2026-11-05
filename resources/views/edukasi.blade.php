@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">Edukasi</h1>
        <p class="text-teal-100 text-lg max-w-3xl">Artikel dan materi tentang tekanan akademik, ekspektasi orang tua, lingkungan pertemanan, dukungan guru, serta strategi belajar yang sehat.</p>
    </div>
</div>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-7">
    <form method="GET" action="{{ route('edukasi') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 grid grid-cols-1 md:grid-cols-[2fr_1fr_auto] gap-3">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari artikel edukasi" class="rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
        <select name="category" class="rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>@endforeach</select>
        <button class="rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-5 py-2.5 font-semibold">Cari</button>
    </form>

    @if($articles->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-10 text-center">
            <h2 class="text-xl font-bold text-teal-900">Belum ada artikel yang tersedia</h2>
            <p class="text-gray-600 mt-2">Artikel edukasi akan ditampilkan di sini setelah diterbitkan oleh administrator.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($articles as $article)
                <article class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="h-2 bg-teal-600"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <span class="text-xs font-semibold text-teal-700 uppercase">{{ $article->category?->name ?? 'Edukasi' }}</span>
                        <h2 class="text-xl font-bold text-teal-900 mt-2 mb-3">{{ $article->title }}</h2>
                        <p class="text-gray-600 text-sm leading-6 flex-1">{{ $article->excerpt ?: \Illuminate\Support\Str::limit($article->body, 150) }}</p>
                        <div class="flex items-center justify-between gap-2 mt-5"><span class="text-xs text-gray-500">{{ $article->published_at?->format('d/m/Y') }}</span><a href="{{ route('edukasi.show', $article->slug) }}" class="text-teal-700 font-semibold text-sm hover:underline">Baca selengkapnya</a></div>
                    </div>
                </article>
            @endforeach
        </div>
        {{ $articles->links() }}
    @endif
</div>
@endsection
