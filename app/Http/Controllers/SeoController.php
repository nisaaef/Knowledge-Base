<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;

class SeoController extends Controller
{
    public function sitemap()
    {
        $articles = Article::published()->orderByDesc('updated_at')->get(['id', 'slug', 'updated_at']);

        $authors = User::where('is_active', true)
            ->whereNotNull('username')
            ->whereHas('articles', fn ($q) => $q->published())
            ->get(['id', 'username', 'updated_at']);

        return response()
            ->view('sitemap', [
                'articles' => $articles,
                'authors' => $authors,
                'lastChange' => $articles->max('updated_at'),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /penulis',
            'Disallow: /dashboard',
            'Disallow: /login',
            'Disallow: /daftar',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
