@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-5">
    @include('admin.partials.nav')
    <div><p class="text-sm font-semibold text-teal-700">Manajemen Artikel</p><h1 class="text-3xl font-bold text-teal-900 mt-1">Tambah Artikel</h1></div>
    @include('admin.articles.form', ['article' => null, 'action' => route('admin.articles.store'), 'method' => 'POST'])
</div>
@endsection
