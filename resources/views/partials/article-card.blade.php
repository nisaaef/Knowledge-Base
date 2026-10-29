<a href="{{ route('articles.show', $article) }}"
   class="card group flex h-full flex-col overflow-hidden transition hover:border-cobalt-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cobalt-600">
    @if ($article->coverUrl())
        <img src="{{ $article->coverUrl() }}" alt="" loading="lazy" class="aspect-[16/9] w-full object-cover">
    @else
        <div class="flex aspect-[16/9] w-full items-center justify-center bg-cobalt-50 text-cobalt-500" aria-hidden="true">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
        </div>
    @endif

    <div class="flex flex-1 flex-col p-5">
        @if ($article->category)
            <span class="text-sm font-semibold text-cobalt-600">{{ $article->category->name }}</span>
        @endif
        <h3 class="mt-1 text-lg font-bold leading-snug tracking-tight text-ink group-hover:text-cobalt-700">{{ $article->title }}</h3>
        <p class="mt-2 line-clamp-3 text-[15px] leading-relaxed text-slate-600">{{ $article->summary() }}</p>

        <div class="mt-auto flex flex-wrap gap-x-3 gap-y-1 pt-4 text-[13px] text-slate-500">
            <span>{{ $article->author->name }}</span>
            <span>{{ $article->published_at?->translatedFormat('j M Y') }}</span>
            <span>{{ $article->readingTime() }} menit baca</span>
        </div>
    </div>
</a>
