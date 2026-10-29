@extends('layouts.app')

@section('title', $author->name)
@section('description', $author->bio ? \Illuminate\Support\Str::limit($author->bio, 150) : 'Tulisan oleh '.$author->name)

@section('content')
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-6 px-5 py-12 sm:flex-row sm:items-start">
            @include('partials.avatar', ['user' => $author, 'size' => 'h-28 w-28 text-4xl'])

            <div class="min-w-0 max-w-2xl">
                <h1 class="text-3xl font-bold leading-tight tracking-tight sm:text-4xl">{{ $author->name }}</h1>
                <p class="text-sm text-slate-500">&#64;{{ $author->username }}</p>

                @if ($author->headline)
                    <p class="mt-2 text-lg text-slate-700">{{ $author->headline }}</p>
                @endif

                @if ($author->bio)
                    <p class="mt-4 whitespace-pre-line leading-relaxed text-slate-700">{{ $author->bio }}</p>
                @endif

                <div class="mt-5">
                    @include('partials.author-links', ['author' => $author])
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-5 py-12">
        <h2 class="text-xl font-bold tracking-tight">
            Tulisan oleh {{ \Illuminate\Support\Str::before($author->name, ' ') }}
            <span class="font-medium text-slate-500">({{ $articles->total() }})</span>
        </h2>

        @if ($articles->isEmpty())
            <div class="card mt-6 px-6 py-14 text-center">
                <p class="text-slate-600">{{ $author->name }} belum menerbitkan artikel.</p>
                <a href="{{ route('home') }}" class="btn btn-primary mt-5">Lihat semua artikel</a>
            </div>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    @include('partials.article-card', ['article' => $article])
                @endforeach
            </div>

            <div class="mt-10">{{ $articles->links() }}</div>
        @endif
    </section>
@endsection
