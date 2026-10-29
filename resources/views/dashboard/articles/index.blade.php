@extends('layouts.dashboard')

@php $isAdmin = $area === 'admin'; @endphp
@section('title', $isAdmin ? 'Artikel' : 'Artikel saya')
@section('heading', $isAdmin ? 'Daftar Artikel' : 'Artikel saya')

@section('actions')
    <a href="{{ route($area.'.articles.create') }}" class="btn btn-primary">Tulis artikel</a>
@endsection

@section('content')
    <form id="filter-form" method="GET" action="{{ route($area.'.articles.index') }}" class="mb-5 flex flex-col gap-3 sm:flex-row">
        <label for="q" class="sr-only">Cari judul</label>
        <input id="q" type="search" name="q" value="{{ $term }}" placeholder="Cari judul artikel" class="input sm:max-w-xs">

        <label for="status" class="sr-only">Status</label>
        <select id="status" name="status" class="input sm:w-44">
            <option value="">Semua status</option>
            <option value="published" @selected($status === 'published')>Terbit</option>
            <option value="draft" @selected($status === 'draft')>Draf</option>
        </select>

        <button type="submit" class="btn btn-ghost">Terapkan</button>
    </form>

    <div id="results" aria-live="polite" class="transition-opacity">
    <div class="card overflow-x-auto">
        <table class="rtable w-full min-w-[640px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <tr>
                    <th scope="col" class="px-5 py-3 font-semibold">Judul</th>
                    @if ($isAdmin)
                        <th scope="col" class="px-3 py-3 font-semibold">Penulis</th>
                    @endif
                    <th scope="col" class="px-3 py-3 font-semibold">Kategori</th>
                    <th scope="col" class="px-3 py-3 font-semibold">Status</th>
                    <th scope="col" class="px-3 py-3 font-semibold">Diubah</th>
                    <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($articles as $article)
                    <tr>
                        <td class="rt-title max-w-xs px-5 py-3">
                            <a href="{{ route($area.'.articles.edit', $article) }}" class="block truncate font-semibold hover:text-cobalt-700">{{ $article->title }}</a>
                        </td>
                        @if ($isAdmin)
                            <td class="px-3 py-3" data-label="Penulis">
                                <div class="flex items-center gap-2.5">
                                    @include('partials.avatar', ['user' => $article->author, 'size' => 'h-8 w-8 text-xs'])
                                    <span class="text-slate-600">{{ $article->author->name }}</span>
                                </div>
                            </td>
                        @endif
                        <td class="px-3 py-3 text-slate-600" data-label="Kategori">{{ $article->category?->name ?? 'Tanpa kategori' }}</td>
                        <td class="px-3 py-3" data-label="Status">@include('dashboard.articles._status', ['article' => $article])</td>
                        <td class="px-3 py-3 text-slate-500" data-label="Diubah">{{ $article->updated_at->diffForHumans() }}</td>
                        <td class="rt-actions px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('articles.show', $article) }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Lihat</a>
                                <a href="{{ route($area.'.articles.edit', $article) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                <form method="POST" action="{{ route($area.'.articles.destroy', $article) }}"
                                      onsubmit="return confirm('Hapus artikel &quot;{{ addslashes($article->title) }}&quot;? Artikel yang dihapus tidak bisa dikembalikan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 6 : 5 }}" class="px-5 py-12 text-center text-slate-500">
                            @if ($term !== '' || $status !== '')
                                Tidak ada artikel yang cocok dengan filter.
                            @else
                                Belum ada artikel.
                                <a href="{{ route($area.'.articles.create') }}" class="font-semibold text-cobalt-600 hover:underline">Tulis yang pertama</a>.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $articles->links() }}</div>
    </div>

    {{-- Pencarian langsung: daftar diperbarui saat mengetik atau mengganti status. --}}
    <script>
        (() => {
            const form = document.getElementById('filter-form');
            const results = document.getElementById('results');
            const search = document.getElementById('q');
            const status = document.getElementById('status');
            if (!form || !results) return;

            // Tombol "Terapkan" hanya dipakai bila JavaScript mati.
            const applyButton = form.querySelector('button[type="submit"]');
            if (applyButton) applyButton.hidden = true;

            let timer = null;
            let controller = null;

            const load = async () => {
                const params = new URLSearchParams(new FormData(form));
                for (const [key, value] of [...params]) {
                    if (value === '') params.delete(key);
                }

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
                    const fresh = doc.getElementById('results');
                    if (!fresh) throw new Error('no results block');

                    results.innerHTML = fresh.innerHTML;
                    history.replaceState(null, '', url.pathname + url.search);
                } catch (e) {
                    // Jika gagal (bukan dibatalkan), kembali ke cara biasa: kirim formulir.
                    if (e.name !== 'AbortError') form.submit();
                } finally {
                    results.style.opacity = '';
                }
            };

            search.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(load, 300);
            });
            status.addEventListener('change', load);
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                clearTimeout(timer);
                load();
            });
        })();
    </script>
@endsection
