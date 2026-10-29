<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $categorySlug = (string) $request->query('kategori', '');

        $categories = Category::query()
            ->whereHas('articles', fn ($q) => $q->published())
            ->orderBy('name')
            ->get();

        $articles = Article::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->when($term !== '', function ($query) use ($term) {
                $like = '%'.$term.'%';
                $query->where(fn ($w) => $w
                    ->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('body', 'like', $like));
            })
            ->when($categorySlug !== '', fn ($query) => $query
                ->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('home', [
            'articles' => $articles,
            'categories' => $categories,
            'term' => $term,
            'activeCategory' => $categorySlug,
        ]);
    }

    public function show(Article $article)
    {
        $user = auth()->user();
        $canPreview = $user && ($user->isAdmin() || $user->id === $article->user_id);

        abort_unless($article->isPublished() || $canPreview, 404);

        $article->load(['author', 'category:id,name,slug']);

        $related = Article::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->where('id', '!=', $article->id)
            ->when($article->category_id, fn ($q) => $q->where('category_id', $article->category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('articles.show', [
            'article' => $article,
            'related' => $related,
            'isPreview' => ! $article->isPublished(),
        ]);
    }
}
