<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private function writer(string $email = 'penulis@test.id', bool $active = true): User
    {
        return User::create([
            'name' => 'Budi Santoso', 'email' => $email, 'password' => 'password',
            'role' => User::ROLE_WRITER, 'is_active' => $active,
        ]);
    }

    private function article(User $author, array $overrides = []): Article
    {
        return Article::create($overrides + [
            'user_id' => $author->id,
            'title' => 'Artikel contoh',
            'slug' => 'artikel-contoh-'.uniqid(),
            'body' => 'Isi artikel contoh yang cukup panjang untuk lolos validasi.',
            'status' => Article::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);
    }

    public function test_sitemap_lists_published_pages_only(): void
    {
        $writer = $this->writer();
        $this->article($writer, ['slug' => 'sudah-terbit']);
        $this->article($writer, ['slug' => 'masih-draf', 'status' => Article::STATUS_DRAFT, 'published_at' => null]);
        $this->article($writer, ['slug' => 'belum-waktunya', 'published_at' => now()->addDay()]);

        $response = $this->get('/sitemap.xml')->assertOk();

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee('<urlset', false)
            ->assertSee(route('home'), false)
            ->assertSee(route('about'), false)
            ->assertSee('/artikel/sudah-terbit', false)
            ->assertDontSee('masih-draf', false)
            ->assertDontSee('belum-waktunya', false)
            ->assertSee('/profil/'.$writer->username, false);
    }

    public function test_sitemap_skips_authors_whose_access_was_revoked(): void
    {
        $revoked = $this->writer('mati@test.id', false);
        $this->article($revoked, ['slug' => 'tulisan-lama']);

        $this->get('/sitemap.xml')->assertDontSee('/profil/'.$revoked->username, false);
    }

    public function test_robots_blocks_dashboards_and_points_to_sitemap(): void
    {
        $response = $this->get('/robots.txt')->assertOk();

        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $response->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /penulis', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }

    public function test_home_has_meta_tags_and_uses_the_site_description_when_set(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('name="twitter:card"', false)
            ->assertSee('<meta name="description" content="'.e(Setting::DEFAULTS['hero_subtitle']).'">', false);

        Setting::put(['site_name' => 'KB', 'site_description' => 'Deskripsi khusus untuk Google.']);

        $this->get('/')->assertSee('<meta name="description" content="Deskripsi khusus untuk Google.">', false);
    }

    public function test_article_page_has_structured_data_and_open_graph(): void
    {
        $writer = $this->writer();
        $this->article($writer, ['title' => 'Cara memasang router', 'slug' => 'cara-memasang-router', 'excerpt' => 'Panduan singkat memasang router.']);

        $this->get('/artikel/cara-memasang-router')
            ->assertOk()
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('"headline":"Cara memasang router"', false)
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('<meta name="description" content="Panduan singkat memasang router.">', false)
            ->assertDontSee('noindex', false);
    }

    public function test_meta_text_is_escaped_exactly_once(): void
    {
        $writer = $this->writer();
        $this->article($writer, ['title' => 'Tips "cepat" & aman', 'slug' => 'tips-cepat']);

        $this->get('/artikel/tips-cepat')
            ->assertOk()
            ->assertSee('content="Tips &quot;cepat&quot; &amp; aman - ', false)
            ->assertDontSee('&amp;amp;', false);
    }

    public function test_draft_preview_is_noindex_and_has_no_structured_data(): void
    {
        $writer = $this->writer();
        $this->article($writer, ['slug' => 'draf-rahasia', 'status' => Article::STATUS_DRAFT, 'published_at' => null]);

        $this->actingAs($writer)->get('/artikel/draf-rahasia')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,nofollow">', false)
            ->assertDontSee('application/ld+json', false);
    }

    public function test_login_and_register_are_noindex(): void
    {
        $this->get('/login')->assertSee('content="noindex,nofollow"', false);
        $this->get('/daftar')->assertSee('content="noindex,nofollow"', false);
    }

    public function test_favicon_is_a_letter_icon_until_a_logo_is_uploaded(): void
    {
        $this->get('/')->assertSee('<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,', false);

        Setting::setValue('logo_path', 'branding/logo.png');

        $this->get('/')
            ->assertSee('<link rel="icon" href="', false)
            ->assertSee('storage/branding/logo.png', false)
            ->assertSee('property="og:image"', false);
    }

    public function test_dashboard_also_has_the_favicon(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.id', 'password' => 'password',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/admin')->assertSee('rel="icon"', false);
    }
}
