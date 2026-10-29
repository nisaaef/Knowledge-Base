@extends('layouts.app')

@section('title', $article->title)
@section('description', $article->summary())

@section('content')
    <article class="mx-auto max-w-3xl px-5 py-12">
        @if ($isPreview)
            <div class="mb-8 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                Ini pratinjau. Artikel masih berstatus draf dan belum tampil di halaman utama.
            </div>
        @endif

        <nav class="text-sm text-slate-500" aria-label="Navigasi artikel">
            <a href="{{ route('home') }}" class="font-semibold text-cobalt-600 hover:underline">Daftar Artikel</a>
            @if ($article->category)
                <span class="mx-1" aria-hidden="true">/</span>
                <a href="{{ route('home', ['kategori' => $article->category->slug]) }}" class="hover:text-ink hover:underline">{{ $article->category->name }}</a>
            @endif
        </nav>

        <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight sm:text-[2.75rem]">{{ $article->title }}</h1>

        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
            <span>
                Ditulis oleh
                @if ($article->author->username)
                    <a href="{{ $article->author->profileUrl() }}" class="font-semibold text-slate-700 hover:text-cobalt-700 hover:underline">{{ $article->author->name }}</a>
                @else
                    {{ $article->author->name }}
                @endif
            </span>
            @if ($article->published_at)
                <span>{{ $article->published_at->translatedFormat('j F Y') }}</span>
            @endif
            <span>{{ $article->readingTime() }} menit baca</span>
        </div>

        @if ($article->coverUrl())
            <img src="{{ $article->coverUrl() }}" alt="" class="mt-8 aspect-[16/9] w-full rounded-xl object-cover">
        @endif

        <div class="prose prose-slate reading mt-10 max-w-none prose-a:text-cobalt-600 prose-code:rounded prose-code:bg-slate-100 prose-code:px-1 prose-code:py-0.5 prose-code:before:content-none prose-code:after:content-none">
            {!! $article->bodyHtml() !!}
        </div>

        <aside class="mt-14 flex gap-4 rounded-xl border border-slate-200 bg-white p-5" aria-label="Tentang penulis">
            @include('partials.avatar', ['user' => $article->author, 'size' => 'h-14 w-14 text-lg'])
            <div class="min-w-0">
                <p class="text-sm text-slate-500">Ditulis oleh</p>
                <p class="font-bold">{{ $article->author->name }}</p>
                @if ($article->author->headline)
                    <p class="text-sm text-slate-600">{{ $article->author->headline }}</p>
                @endif
                @if ($article->author->bio)
                    <p class="mt-2 text-[15px] leading-relaxed text-slate-700">{{ \Illuminate\Support\Str::limit($article->author->bio, 220) }}</p>
                @endif
                @if ($article->author->username)
                    <a href="{{ $article->author->profileUrl() }}" class="mt-3 inline-block text-sm font-semibold text-cobalt-600 hover:underline">
                        Lihat semua tulisan {{ \Illuminate\Support\Str::before($article->author->name, ' ') }}
                    </a>
                @endif
            </div>
        </aside>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-slate-200 bg-white">
            <div class="mx-auto max-w-6xl px-5 py-12">
                <h2 class="text-xl font-bold tracking-tight">Baca juga</h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        @include('partials.article-card', ['article' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
