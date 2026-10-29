<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\User;

class DashboardController extends Controller
{
    public function admin()
    {
        return view('dashboard.admin', [
            'stats' => [
                'writers' => User::where('role', User::ROLE_WRITER)->count(),
                'activeWriters' => User::where('role', User::ROLE_WRITER)->where('is_active', true)->count(),
                'pending' => User::where('role', User::ROLE_WRITER)->where('pending_approval', true)->count(),
                'articles' => Article::count(),
                'published' => Article::where('status', Article::STATUS_PUBLISHED)->count(),
                'drafts' => Article::where('status', Article::STATUS_DRAFT)->count(),
                'unread' => ContactMessage::whereNull('read_at')->count(),
            ],
            'latestArticles' => Article::with(['author:id,name', 'category:id,name'])->latest()->take(5)->get(),
            'latestMessages' => ContactMessage::latest()->take(5)->get(),
        ]);
    }

    public function writer()
    {
        $userId = auth()->id();

        return view('dashboard.writer', [
            'stats' => [
                'articles' => Article::where('user_id', $userId)->count(),
                'published' => Article::where('user_id', $userId)->where('status', Article::STATUS_PUBLISHED)->count(),
                'drafts' => Article::where('user_id', $userId)->where('status', Article::STATUS_DRAFT)->count(),
            ],
            'latestArticles' => Article::with('category:id,name')
                ->where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
