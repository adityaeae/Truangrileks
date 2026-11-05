@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-10 sm:py-14"><div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8"><p class="text-teal-100 text-sm font-semibold mb-2">{{ $article->category?->name ?? 'Edukasi' }}</p><h1 class="text-3xl sm:text-4xl font-bold">{{ $article->title }}</h1><p class="text-teal-100 text-sm mt-4">Diterbitkan {{ $article->published_at?->format('d/m/Y') }}</p></div></div>
<article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
    @if($article->excerpt)<p class="text-lg text-gray-700 font-medium mb-8">{{ $article->excerpt }}</p>@endif
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 sm:p-9 text-gray-700 leading-8 whitespace-pre-line">{{ $article->body }}</div>
    <div class="mt-8"><a href="{{ route('edukasi') }}" class="font-semibold text-teal-800 hover:underline">← Kembali ke Edukasi</a></div>
</article>
@endsection
