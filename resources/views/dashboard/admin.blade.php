@extends('layouts.dashboard')

@section('title', 'Beranda')
@section('heading', 'Beranda')

@section('actions')
    <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">Tulis artikel</a>
@endsection

@section('content')
    @if ($stats['pending'] > 0)
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-semibold">{{ $stats['pending'] }} pendaftar penulis menunggu persetujuanmu.</p>
            <a href="{{ route('admin.writers.index') }}" class="btn btn-ghost btn-sm">Tinjau pendaftar</a>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.articles.index', ['status' => 'published']) }}" class="card block p-5 transition hover:border-cobalt-500 hover:shadow-sm">
            <p class="text-sm text-slate-500">Artikel terbit</p>
            <p class="mt-1 text-3xl font-bold tracking-tight">{{ $stats['published'] }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $stats['drafts'] }} masih draf</p>
        </a>
        <a href="{{ route('admin.writers.index') }}" class="card block p-5 transition hover:border-cobalt-500 hover:shadow-sm">
            <p class="text-sm text-slate-500">Penulis aktif</p>
            <p class="mt-1 text-3xl font-bold tracking-tight">{{ $stats['activeWriters'] }}</p>
            <p class="mt-1 text-sm text-slate-500">dari {{ $stats['writers'] }} akun penulis</p>
        </a>
        <a href="{{ route('admin.articles.index') }}" class="card block p-5 transition hover:border-cobalt-500 hover:shadow-sm">
            <p class="text-sm text-slate-500">Total artikel</p>
            <p class="mt-1 text-3xl font-bold tracking-tight">{{ $stats['articles'] }}</p>
        </a>
        <a href="{{ route('admin.messages.index') }}" class="card block p-5 transition hover:border-cobalt-500 hover:shadow-sm">
            <p class="text-sm text-slate-500">Pesan belum dibaca</p>
            <p class="mt-1 text-3xl font-bold tracking-tight">{{ $stats['unread'] }}</p>
        </a>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <section class="card">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold">Artikel terbaru</h2>
                <a href="{{ route('admin.articles.index') }}" class="text-sm font-semibold text-cobalt-600 hover:underline">Lihat semua</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($latestArticles as $article)
                    <li class="flex items-center justify-between gap-4 px-5 py-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.articles.edit', $article) }}" class="block truncate font-semibold hover:text-cobalt-700">{{ $article->title }}</a>
                            <p class="text-sm text-slate-500">{{ $article->author->name }}</p>
                        </div>
                        @include('dashboard.articles._status', ['article' => $article])
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-slate-500">Belum ada artikel. Mulai dengan menulis yang pertama.</li>
                @endforelse
            </ul>
        </section>

        <section class="card">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold">Pesan terbaru</h2>
                <a href="{{ route('admin.messages.index') }}" class="text-sm font-semibold text-cobalt-600 hover:underline">Lihat semua</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($latestMessages as $message)
                    <li class="px-5 py-3">
                        <a href="{{ route('admin.messages.show', $message) }}" class="flex items-center justify-between gap-4">
                            <span class="min-w-0">
                                <span class="block truncate {{ $message->isUnread() ? 'font-bold' : 'font-medium text-slate-700' }}">{{ $message->subject }}</span>
                                <span class="block truncate text-sm text-slate-500">{{ $message->name }}</span>
                            </span>
                            <span class="shrink-0 text-xs text-slate-500">{{ $message->created_at->diffForHumans() }}</span>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-slate-500">Belum ada pesan masuk.</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
