<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_WRITER = 'writer';

    protected $attributes = [
        'role' => self::ROLE_WRITER,
        'is_active' => true,
        'pending_approval' => false,
    ];

    protected $fillable = [
        'name',
        'username',
        'headline',
        'bio',
        'avatar_path',
        'website',
        'instagram',
        'linkedin',
        'email',
        'password',
        'role',
        'is_active',
        'pending_approval',
        'application_note',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        // Akun baru (dari admin, seeder, atau tes) otomatis mendapat username.
        static::creating(function (User $user) {
            if (blank($user->username)) {
                $user->username = static::makeUsername($user->name ?: Str::before((string) $user->email, '@'));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'pending_approval' => 'boolean',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isWriter(): bool
    {
        return $this->role === self::ROLE_WRITER;
    }

    /** Kirim notifikasi dasbor ke semua admin aktif. */
    public static function notifyAdmins(string $title, ?string $body, string $url): void
    {
        $admins = static::where('role', self::ROLE_ADMIN)->where('is_active', true)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new \App\Notifications\SiteNotification($title, $body, $url));
        }
    }

    /** Kirim notifikasi dasbor ke pengguna ini. */
    public function pushNotice(string $title, ?string $body, string $url): void
    {
        $this->notify(new \App\Notifications\SiteNotification($title, $body, $url));
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? asset('storage/'.$this->avatar_path) : null;
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/u', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY);

        return collect($words)
            ->take(2)
            ->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('') ?: '?';
    }

    public function profileUrl(): ?string
    {
        return $this->username ? route('authors.show', ['user' => $this->username]) : null;
    }

    public function instagramUrl(): ?string
    {
        return $this->instagram ? 'https://instagram.com/'.$this->instagram : null;
    }

    public static function makeUsername(string $source, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($source, '_'), 30, '');
        if (strlen($base) < 3) {
            $base = 'penulis';
        }

        $username = $base;
        $i = 2;

        while (static::where('username', $username)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $suffix = '_'.$i++;
            $username = Str::limit($base, 30 - strlen($suffix), '').$suffix;
        }

        return $username;
    }
}
