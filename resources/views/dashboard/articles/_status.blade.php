@if ($article->status === \App\Models\Article::STATUS_PUBLISHED)
    <span class="badge shrink-0 bg-emerald-100 text-emerald-800">Terbit</span>
@else
    <span class="badge shrink-0 bg-amber-100 text-amber-900">Draf</span>
@endif
