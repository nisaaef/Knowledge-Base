<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;

class AuthorController extends Controller
{
    public function show(User $user)
    {
        // Akun yang aksesnya dicabut tidak lagi punya halaman publik.
        abort_unless($user->is_active, 404);

        $articles = Article::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->where('user_id', $user->id)
            ->latest('published_at')
            ->paginate(9);

        return view('authors.show', [
            'author' => $user,
            'articles' => $articles,
        ]);
    }
}
