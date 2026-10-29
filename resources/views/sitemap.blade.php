{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        @if ($lastChange)
            <lastmod>{{ $lastChange->toAtomString() }}</lastmod>
        @endif
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('about') }}</loc>
        <priority>0.3</priority>
    </url>
    <url>
        <loc>{{ route('contact') }}</loc>
        <priority>0.3</priority>
    </url>
    @foreach ($articles as $article)
        <url>
            <loc>{{ route('articles.show', $article) }}</loc>
            <lastmod>{{ $article->updated_at->toAtomString() }}</lastmod>
            <priority>0.8</priority>
        </url>
    @endforeach
    @foreach ($authors as $author)
        <url>
            <loc>{{ route('authors.show', ['user' => $author->username]) }}</loc>
            <lastmod>{{ $author->updated_at->toAtomString() }}</lastmod>
            <priority>0.4</priority>
        </url>
    @endforeach
</urlset>
