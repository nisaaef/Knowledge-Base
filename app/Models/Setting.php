<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Pengaturan situs yang bisa diubah admin dari dasbor.
 *
 * Pakai di Blade: {{ \App\Models\Setting::read('hero_title') }}
 * Nama situs: {{ \App\Models\Setting::siteName() }}
 * Logo: \App\Models\Setting::logoUrl() (null bila belum diunggah)
 */
class Setting extends Model
{
    /** Pengaturan teks. Mengosongkan salah satunya mengembalikan nilai bawaan. */
    public const KEYS = ['site_name', 'site_description', 'hero_title', 'hero_subtitle', 'logo_show_name'];

    /** Nilai bawaan bila admin belum mengisi atau mengosongkan kolomnya. */
    public const DEFAULTS = [
        'hero_title' => 'Temukan jawabanmu disini',
        'hero_subtitle' => 'Semua panduan, tutorial dan solusi yang kamu butuhin ada disini.',
        'logo_show_name' => '1',
    ];

    protected $fillable = ['key', 'value'];

    /** Semua nilai tersimpan, di-cache supaya tidak query di setiap halaman. */
    public static function allValues(): array
    {
        return Cache::rememberForever('site_settings', function () {
            try {
                return static::query()->pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                // Tabel belum dimigrasi: pakai nilai bawaan saja.
                return [];
            }
        });
    }

    public static function read(string $key): ?string
    {
        $value = static::allValues()[$key] ?? null;

        return filled($value) ? $value : static::fallback($key);
    }

    public static function fallback(string $key): ?string
    {
        return match ($key) {
            'site_name' => config('app.name'),
            // Deskripsi untuk Google: bila kosong, pakai kalimat di bawah judul beranda.
            'site_description' => static::read('hero_subtitle'),
            default => self::DEFAULTS[$key] ?? null,
        };
    }

    public static function siteName(): string
    {
        return (string) static::read('site_name');
    }

    public static function logoUrl(): ?string
    {
        $path = static::allValues()['logo_path'] ?? null;

        return filled($path) ? asset('storage/'.$path) : null;
    }

    /** Apakah nama situs ikut tampil di samping logo? (bawaan: ya) */
    public static function showsNameWithLogo(): bool
    {
        return static::read('logo_show_name') !== '0';
    }

    /** Simpan pengaturan teks; kolom yang dikosongkan kembali ke nilai bawaan. */
    public static function put(array $data): void
    {
        foreach (self::KEYS as $key) {
            $value = trim((string) ($data[$key] ?? ''));

            if ($value === '') {
                static::where('key', $key)->delete();
            } else {
                static::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        Cache::forget('site_settings');
    }

    /** Simpan satu nilai apa adanya (dipakai untuk lokasi file logo); null menghapusnya. */
    public static function setValue(string $key, ?string $value): void
    {
        if ($value === null) {
            static::where('key', $key)->delete();
        } else {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget('site_settings');
    }
}
