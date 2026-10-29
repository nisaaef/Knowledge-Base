<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ganti kata sandi kedua akun ini setelah login pertama.
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrator', 'password' => 'password', 'role' => User::ROLE_ADMIN, 'is_active' => true]
        );

        $writer = User::firstOrCreate(
            ['email' => 'penulis@example.com'],
            ['name' => 'Penulis Contoh', 'password' => 'password', 'role' => User::ROLE_WRITER, 'is_active' => true]
        );

        $categories = collect([
            'Panduan' => 'Langkah awal dan penjelasan dasar.',
            'Tutorial' => 'Cara mengerjakan sesuatu, langkah demi langkah.',
            'FAQ' => 'Pertanyaan yang sering ditanyakan.',
        ])->map(fn ($description, $name) => Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'description' => $description]
        ));

        $samples = [
            [
                'title' => 'Memulai: cara membaca knowledge base ini',
                'category' => 'Panduan',
                'excerpt' => 'Tiga cara tercepat menemukan jawaban di sini.',
                'body' => <<<'MD'
## Cari lebih dulu

Ketik kata kunci di kolom pencarian pada halaman utama. Pencarian memeriksa judul, ringkasan, dan isi artikel.

## Telusuri lewat kategori

Pilih kategori di bawah kolom pencarian untuk mempersempit daftar artikel.

## Tidak ketemu?

Kirim pertanyaanmu lewat halaman **Kontak**. Pertanyaan yang sering muncul akan dijadikan artikel baru.
MD,
            ],
            [
                'title' => 'Cara menulis artikel dengan Markdown',
                'category' => 'Tutorial',
                'excerpt' => 'Ringkasan format yang bisa dipakai penulis di editor artikel.',
                'body' => <<<'MD'
Editor artikel mendukung **Markdown**. Beberapa format yang sering dipakai:

- `## Judul bagian` untuk subjudul
- `**tebal**` dan `*miring*`
- `[teks tautan](https://contoh.com)` untuk tautan
- Baris diawali `-` untuk daftar

Simpan sebagai *draf* dulu untuk memeriksa tampilannya lewat tombol **Lihat**, lalu ubah status menjadi *Terbit*.
MD,
            ],
            [
                'title' => 'Bagaimana cara menjadi penulis?',
                'category' => 'FAQ',
                'excerpt' => 'Akun penulis dibuat dan diatur oleh admin.',
                'body' => <<<'MD'
Akun penulis dibuat oleh admin. Kirim pesan lewat halaman **Kontak** dengan menyebutkan nama dan topik yang ingin kamu tulis.

Admin dapat mencabut atau memberikan kembali akses penulis kapan saja.
MD,
            ],
        ];

        foreach ($samples as $sample) {
            $slug = Str::slug($sample['title']);

            Article::firstOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $sample['category'] === 'Panduan' ? $admin->id : $writer->id,
                    'category_id' => $categories[$sample['category']]->id,
                    'title' => $sample['title'],
                    'excerpt' => $sample['excerpt'],
                    'body' => $sample['body'],
                    'status' => Article::STATUS_PUBLISHED,
                    'published_at' => now(),
                ]
            );
        }
    }
}
