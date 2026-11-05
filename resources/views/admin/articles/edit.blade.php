@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-5">
    @include('admin.partials.nav')
    <div><p class="text-sm font-semibold text-teal-700">Manajemen Artikel</p><h1 class="text-3xl font-bold text-teal-900 mt-1">Edit Artikel</h1></div>
    @include('admin.articles.form', ['article' => $article, 'action' => route('admin.articles.update', $article->id), 'method' => 'PUT'])
</div>
@endsection
