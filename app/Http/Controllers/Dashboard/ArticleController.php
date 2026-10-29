<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Dipakai oleh dua sisi dasbor:
 *  - /admin/artikel   -> admin melihat dan mengelola semua artikel
 *  - /penulis/artikel -> penulis hanya melihat dan mengelola artikelnya sendiri
 */
class ArticleController extends Controller
{
    private function area(): string
    {
        return request()->routeIs('admin.*') ? 'admin' : 'penulis';
    }

    private function ensureCanManage(Article $article): void
    {
        $user = auth()->user();

        abort_unless($user->isAdmin() || $article->user_id === $user->id, 403);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $term = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $articles = Article::query()
            ->with(['author:id,name', 'category:id,name'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($term !== '', fn ($q) => $q->where('title', 'like', '%'.$term.'%'))
            ->when(in_array($status, [Article::STATUS_DRAFT, Article::STATUS_PUBLISHED], true),
                fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.articles.index', [
            'articles' => $articles,
            'area' => $this->area(),
            'term' => $term,
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('dashboard.articles.create', [
            'article' => new Article(['status' => Article::STATUS_DRAFT]),
            'categories' => Category::orderBy('name')->get(),
            'area' => $this->area(),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->payload($request);

        $article = Article::create($payload + [
            'user_id' => $request->user()->id,
            'slug' => Article::makeSlug($payload['title']),
        ]);

        $this->notifyIfWriterPublished($article, $request->user());

        return redirect()->route($this->area().'.articles.index')
            ->with('status', 'Artikel disimpan.');
    }

    public function edit(Article $article)
    {
        $this->ensureCanManage($article);

        return view('dashboard.articles.edit', [
            'article' => $article,
            'categories' => Category::orderBy('name')->get(),
            'area' => $this->area(),
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $this->ensureCanManage($article);

        $wasPublished = $article->status === Article::STATUS_PUBLISHED;
        $user = $request->user();

        $article->update($this->payload($request, $article));

        // Admin diberi tahu saat penulis menerbitkan; penulis diberi tahu saat admin mengubah artikelnya.
        if (! $wasPublished) {
            $this->notifyIfWriterPublished($article, $user);
        }

        if ($user->isAdmin() && $article->user_id !== $user->id && $article->author) {
            $article->author->pushNotice(
                'Artikelmu diubah oleh admin',
                $article->title,
                route('penulis.articles.edit', $article)
            );
        }

        return redirect()->route($this->area().'.articles.index')
            ->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Article $article)
    {
        $this->ensureCanManage($article);

        if ($article->cover_path) {
            Storage::disk('public')->delete($article->cover_path);
        }

        $article->delete();

        $admin = auth()->user();
        if ($admin->isAdmin() && $article->user_id !== $admin->id && $article->author) {
            $article->author->pushNotice(
                'Artikelmu dihapus oleh admin',
                $article->title,
                route('penulis.articles.index')
            );
        }

        return redirect()->route($this->area().'.articles.index')
            ->with('status', 'Artikel dihapus.');
    }

    private function notifyIfWriterPublished(Article $article, User $user): void
    {
        if ($user->isWriter() && $article->status === Article::STATUS_PUBLISHED) {
            User::notifyAdmins(
                $user->name.' menerbitkan artikel',
                $article->title,
                route('articles.show', $article)
            );
        }
    }

    /**
     * Pakai kategori yang sudah ada bila namanya sama (tanpa membedakan huruf besar/kecil),
     * kalau belum ada baru dibuat.
     */
    private function resolveCategory(string $name): Category
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', $name));
        $slug = Str::slug($name);

        $existing = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->when($slug !== '', fn ($q) => $q->orWhere('slug', $slug))
            ->first();

        return $existing ?? Category::create([
            'name' => $name,
            'slug' => Category::makeSlug($name),
        ]);
    }

    private function payload(Request $request, ?Article $article = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'new_category' => ['nullable', 'string', 'min:2', 'max:60', 'regex:/[\p{L}\p{N}]/u'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string', 'min:20'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_cover' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in([Article::STATUS_DRAFT, Article::STATUS_PUBLISHED])],
        ]);

        $payload = [
            'title' => $data['title'],
            'category_id' => $data['category_id'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'status' => $data['status'],
            'published_at' => $data['status'] === Article::STATUS_PUBLISHED
                ? ($article?->published_at ?? now())
                : null,
        ];

        // Kategori baru yang diketik penulis menggantikan pilihan di daftar.
        if (filled($data['new_category'] ?? null)) {
            $payload['category_id'] = $this->resolveCategory($data['new_category'])->id;
        }

        if ($request->hasFile('cover')) {
            if ($article?->cover_path) {
                Storage::disk('public')->delete($article->cover_path);
            }
            $payload['cover_path'] = $request->file('cover')->store('covers', 'public');
        } elseif ($request->boolean('remove_cover') && $article?->cover_path) {
            Storage::disk('public')->delete($article->cover_path);
            $payload['cover_path'] = null;
        }

        return $payload;
    }
}
