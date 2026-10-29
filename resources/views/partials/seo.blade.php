{{-- Meta SEO untuk halaman publik. Halaman mengisi @section('title') dan @section('description').
     Halaman artikel dikenali dari variabel $article (dan $isPreview untuk draf). --}}
@php
    $isArticlePage = isset($article) && $article instanceof \App\Models\Article;
    $isDraftPreview = $isArticlePage && ! empty($isPreview);

    $seoTitle = html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES);
    $seoTitle = ($seoTitle !== '' ? $seoTitle.' - ' : '').\App\Models\Setting::siteName();

    $seoDescription = html_entity_decode(trim($__env->yieldContent('description')), ENT_QUOTES);
    $seoDescription = \Illuminate\Support\Str::limit(
        $seoDescription !== '' ? $seoDescription : (string) \App\Models\Setting::read('site_description'),
        160
    );

    $seoImage = ($isArticlePage ? $article->coverUrl() : null) ?: \App\Models\Setting::logoUrl();
    $seoType = $isArticlePage ? 'article' : 'website';

    // Pratinjau draf serta halaman masuk dan daftar tidak boleh masuk Google.
    $seoRobots = ($isDraftPreview || request()->routeIs('login', 'register')) ? 'noindex,nofollow' : '';
@endphp
<meta name="description" content="{{ $seoDescription }}">
@if ($seoRobots !== '')
    <meta name="robots" content="{{ $seoRobots }}">
@endif
<link rel="canonical" href="{{ url()->current() }}">

<meta property="og:site_name" content="{{ \App\Models\Setting::siteName() }}">
<meta property="og:locale" content="id_ID">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ url()->current() }}">
@if ($seoImage)
    <meta property="og:image" content="{{ $seoImage }}">
@endif

<meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
@if ($seoImage)
    <meta name="twitter:image" content="{{ $seoImage }}">
@endif

@if ($isArticlePage && ! $isDraftPreview)
    {{-- Data terstruktur supaya Google paham ini artikel, siapa penulisnya, dan kapan terbit. --}}
    @php
        $jsonLd = json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->summary(),
            'image' => $article->coverUrl(),
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at?->toIso8601String(),
            'mainEntityOfPage' => route('articles.show', $article),
            'author' => array_filter([
                '@type' => 'Person',
                'name' => $article->author->name,
                'url' => $article->author->profileUrl(),
            ]),
            'publisher' => array_filter([
                '@type' => 'Organization',
                'name' => \App\Models\Setting::siteName(),
                'logo' => \App\Models\Setting::logoUrl()
                    ? ['@type' => 'ImageObject', 'url' => \App\Models\Setting::logoUrl()]
                    : null,
            ]),
        ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    @endphp
    <script type="application/ld+json">{!! str_replace('</', '<\/', $jsonLd) !!}</script>
@endif
