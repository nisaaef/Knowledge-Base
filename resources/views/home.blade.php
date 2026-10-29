@extends('layouts.app')

@section('description', 'Kumpulan artikel dan panduan yang bisa dicari.')

@section('content')
    {{-- Pencarian adalah pintu utama knowledge base, jadi ia dibuat paling besar. --}}
    <section class="bg-ink text-white">
        <div class="mx-auto max-w-6xl px-5 py-14 sm:py-20">
            <h1 class="max-w-2xl text-4xl font-bold leading-tight tracking-tight sm:text-5xl">
                {{ \App\Models\Setting::read('hero_title') }}
            </h1>
            <p class="mt-4 max-w-xl text-lg text-slate-300">
                {{ \App\Models\Setting::read('hero_subtitle') }}
            </p>

            <form action="{{ route('home') }}" method="GET" role="search" class="mt-8 flex max-w-3xl flex-col gap-3 sm:flex-row">
                @if ($activeCategory)
                    <input type="hidden" name="kategori" value="{{ $activeCategory }}">
                @endif
                <label for="q" class="sr-only">Cari artikel</label>
                <input id="q" type="search" name="q" value="{{ $term }}" placeholder="Ketik kata kunci, misalnya &quot;markdown&quot;"
                       class="w-full rounded-xl border-0 bg-white px-5 py-4 text-lg text-ink placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-cobalt-500/60">
                <button type="submit" class="btn btn-primary shrink-0 px-7 py-4 text-base">Cari artikel</button>
            </form>

            @if ($categories->isNotEmpty())
                <div id="chips" class="mt-6 flex flex-wrap items-center gap-2" aria-label="Kategori">
                    @php
                        $chip = 'rounded-full border px-4 py-1.5 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';
                    @endphp
                    <a href="{{ route('home', array_filter(['q' => $term])) }}"
                       class="{{ $chip }} {{ $activeCategory === '' ? 'border-white bg-white text-ink' : 'border-slate-600 text-slate-200 hover:border-slate-300' }}">
                        Semua
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('home', array_filter(['q' => $term, 'kategori' => $category->slug])) }}"
                           class="{{ $chip }} {{ $activeCategory === $category->slug ? 'border-white bg-white text-ink' : 'border-slate-600 text-slate-200 hover:border-slate-300' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section id="results" aria-live="polite" class="mx-auto max-w-6xl px-5 py-12 transition-opacity">
        @if ($term !== '' || $activeCategory !== '')
            <p class="mb-6 text-sm text-slate-600">
                {{ $articles->total() }} artikel ditemukan
                @if ($term !== '') untuk &ldquo;{{ $term }}&rdquo; @endif
                <a href="{{ route('home') }}" class="ml-2 font-semibold text-cobalt-600 hover:underline">Hapus filter</a>
            </p>
        @endif

        @if ($articles->isEmpty())
            <div class="card px-6 py-14 text-center">
                <h2 class="text-lg font-bold">Belum ada artikel yang cocok</h2>
                <p class="mx-auto mt-2 max-w-md text-slate-600">
                    Coba kata kunci yang lebih umum, atau tanyakan langsung lewat halaman kontak.
                </p>
                <a href="{{ route('contact') }}" class="btn btn-primary mt-6">Kirim pertanyaan</a>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    @include('partials.article-card', ['article' => $article])
                @endforeach
            </div>

            <div class="mt-10">
                {{ $articles->links() }}
            </div>
        @endif
    </section>

    {{-- Pencarian langsung: hasil diperbarui saat mengetik, tanpa menekan tombol. --}}
    <script>
        (() => {
            const input = document.getElementById('q');
            const results = document.getElementById('results');
            const chips = document.getElementById('chips');
            if (!input || !results) return;

            const form = input.closest('form');
            let timer = null;
            let controller = null;

            const load = async () => {
                const params = new URLSearchParams(new FormData(form));
                if (!params.get('q')) params.delete('q');

                const url = new URL(form.action, location.href);
                url.search = params.toString();

                if (controller) controller.abort();
                controller = new AbortController();
                results.style.opacity = '.55';

                try {
                    const res = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: controller.signal,
                    });
                    if (!res.ok) throw new Error('bad response');

                    const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                    const newResults = doc.getElementById('results');
                    if (!newResults) throw new Error('no results block');

                    results.innerHTML = newResults.innerHTML;

                    const newChips = doc.getElementById('chips');
                    if (chips && newChips) chips.innerHTML = newChips.innerHTML;

                    history.replaceState(null, '', url.pathname + url.search);
                } catch (e) {
                    // Jika gagal (bukan dibatalkan), kembali ke cara biasa: kirim formulir.
                    if (e.name !== 'AbortError') form.submit();
                } finally {
                    results.style.opacity = '';
                }
            };

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(load, 300);
            });
        })();
    </script>
@endsection