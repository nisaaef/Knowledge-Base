@extends('layouts.dashboard')

@section('title', 'Beranda')
@section('heading', 'Halo, '.auth()->user()->name)

@section('actions')
    <a href="{{ route('penulis.articles.create') }}" class="btn btn-primary">Tulis artikel</a>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
        <a href="{{ route('penulis.articles.index', ['status' => 'published']) }}" class="card block p-5 transition hover:border-cobalt-500 hover:shadow-sm">
            <p class="text-sm text-slate-500">Artikel terbit</p>
            <p class="mt-1 text-3xl font-bold tracking-tight">{{ $stats['published'] }}</p>
        </a>
        <a href="{{ route('penulis.articles.index', ['status' => 'draft']) }}" class="card block p-5 transition hover:border-cobalt-500 hover:shadow-sm">
            <p class="text-sm text-slate-500">Masih draf</p>
            <p class="mt-1 text-3xl font-bold tracking-tight">{{ $stats['drafts'] }}</p>
        </a>
        <a href="{{ route('penulis.articles.index') }}" class="card block p-5 transition hover:border-cobalt-500 hover:shadow-sm">
            <p class="text-sm text-slate-500">Total artikelmu</p>
            <p class="mt-1 text-3xl font-bold tracking-tight">{{ $stats['articles'] }}</p>
        </a>
    </div>

    <section class="card mt-8">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-bold">Artikel terbaru</h2>
            <a href="{{ route('penulis.articles.index') }}" class="text-sm font-semibold text-cobalt-600 hover:underline">Lihat semua</a>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse ($latestArticles as $article)
                <li class="flex items-center justify-between gap-4 px-5 py-3">
                    <div class="min-w-0">
                        <a href="{{ route('penulis.articles.edit', $article) }}" class="block truncate font-semibold hover:text-cobalt-700">{{ $article->title }}</a>
                        <p class="text-sm text-slate-500">{{ $article->category?->name ?? 'Tanpa kategori' }}</p>
                    </div>
                    @include('dashboard.articles._status', ['article' => $article])
                </li>
            @empty
                <li class="px-5 py-10 text-center text-slate-500">
                    Kamu belum menulis apa pun.
                    <a href="{{ route('penulis.articles.create') }}" class="font-semibold text-cobalt-600 hover:underline">Tulis artikel pertamamu</a>.
                </li>
            @endforelse
        </ul>
    </section>
@endsection
