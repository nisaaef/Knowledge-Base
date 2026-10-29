<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_path',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? asset('storage/'.$this->cover_path) : null;
    }

    public function bodyHtml(): string
    {
        return Str::markdown($this->body, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function summary(): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        return Str::limit(trim(strip_tags($this->bodyHtml())), 160);
    }

    public function readingTime(): int
    {
        $words = preg_split('/\s+/u', trim(strip_tags($this->body)), -1, PREG_SPLIT_NO_EMPTY);

        return max(1, (int) ceil(count($words) / 200));
    }

    public static function makeSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
