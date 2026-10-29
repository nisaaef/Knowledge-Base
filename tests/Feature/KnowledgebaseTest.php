<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgebaseTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@test.id',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    private function writer(string $email = 'penulis@test.id', bool $active = true): User
    {
        return User::create([
            'name' => 'Penulis',
            'email' => $email,
            'password' => 'password',
            'role' => User::ROLE_WRITER,
            'is_active' => $active,
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

    public function test_public_pages_are_reachable(): void
    {
        $this->get('/')->assertOk();
        $this->get('/tentang')->assertOk();
        $this->get('/kontak')->assertOk();
        $this->get('/login')->assertOk();
    }

    public function test_guests_are_redirected_from_dashboards(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/penulis')->assertRedirect('/login');
    }

    public function test_writer_cannot_open_admin_side_and_admin_cannot_open_writer_side(): void
    {
        $this->actingAs($this->writer())->get('/admin')->assertForbidden();
        $this->actingAs($this->admin())->get('/penulis')->assertForbidden();
    }

    public function test_login_redirects_each_role_to_its_own_dashboard(): void
    {
        $admin = $this->admin();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');
        $this->actingAs($admin)->get('/dashboard')->assertRedirect('/admin');

        auth()->logout();

        $writer = $this->writer();
        $this->actingAs($writer)->get('/dashboard')->assertRedirect('/penulis');
    }

    public function test_inactive_writer_cannot_log_in(): void
    {
        $writer = $this->writer('mati@test.id', false);

        $this->post('/login', ['email' => $writer->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_revoked_writer_is_blocked_on_next_request(): void
    {
        $writer = $this->writer();
        $this->actingAs($writer)->get('/penulis')->assertOk();

        $writer->update(['is_active' => false]);

        $this->actingAs($writer->fresh())->get('/penulis')->assertRedirect('/login');
    }

    public function test_admin_can_create_toggle_and_delete_writers(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/penulis', [
            'name' => 'Budi',
            'email' => 'budi@test.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'is_active' => 1,
        ])->assertRedirect('/admin/penulis');

        $budi = User::where('email', 'budi@test.id')->firstOrFail();
        $this->assertSame(User::ROLE_WRITER, $budi->role);
        $this->assertTrue($budi->is_active);

        $this->actingAs($admin)->patch("/admin/penulis/{$budi->id}/akses")->assertRedirect();
        $this->assertFalse($budi->fresh()->is_active);

        $this->actingAs($admin)->delete("/admin/penulis/{$budi->id}")->assertRedirect('/admin/penulis');
        $this->assertDatabaseMissing('users', ['email' => 'budi@test.id']);
    }

    public function test_writer_with_articles_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $writer = $this->writer();
        $this->article($writer);

        $this->actingAs($admin)->delete("/admin/penulis/{$writer->id}")->assertSessionHasErrors('writer');
        $this->assertDatabaseHas('users', ['id' => $writer->id]);
    }

    public function test_writer_can_publish_an_article(): void
    {
        $writer = $this->writer();
        $category = Category::create(['name' => 'Panduan', 'slug' => 'panduan']);

        $this->actingAs($writer)->post('/penulis/artikel', [
            'title' => 'Cara memakai Markdown',
            'category_id' => $category->id,
            'body' => 'Ini isi artikel yang cukup panjang untuk diterbitkan.',
            'status' => 'published',
        ])->assertRedirect('/penulis/artikel');

        $article = Article::where('title', 'Cara memakai Markdown')->firstOrFail();
        $this->assertSame($writer->id, $article->user_id);
        $this->assertSame('cara-memakai-markdown', $article->slug);
        $this->assertNotNull($article->published_at);

        $this->get('/artikel/cara-memakai-markdown')->assertOk()->assertSee('Cara memakai Markdown');
    }

    public function test_writer_can_create_a_new_category_while_writing(): void
    {
        $writer = $this->writer();

        $this->actingAs($writer)->post('/penulis/artikel', [
            'title' => 'Mengamankan akun',
            'new_category' => '  Keamanan   akun ',
            'body' => 'Isi artikel yang cukup panjang untuk diterbitkan.',
            'status' => 'published',
        ])->assertRedirect('/penulis/artikel');

        $category = Category::where('name', 'Keamanan akun')->first();
        $this->assertNotNull($category);
        $this->assertSame('keamanan-akun', $category->slug);
        $this->assertSame($category->id, Article::where('title', 'Mengamankan akun')->first()->category_id);
    }

    public function test_new_category_reuses_an_existing_one_with_the_same_name(): void
    {
        $existing = Category::create(['name' => 'Panduan', 'slug' => 'panduan']);

        $this->actingAs($this->writer())->post('/penulis/artikel', [
            'title' => 'Artikel panduan',
            'new_category' => 'panduan',
            'body' => 'Isi artikel yang cukup panjang untuk diterbitkan.',
            'status' => 'draft',
        ])->assertRedirect('/penulis/artikel');

        $this->assertSame(1, Category::count());
        $this->assertSame($existing->id, Article::first()->category_id);
    }

    public function test_new_category_name_is_validated(): void
    {
        $this->actingAs($this->writer())->post('/penulis/artikel', [
            'title' => 'Judul',
            'new_category' => '!!!',
            'body' => 'Isi artikel yang cukup panjang untuk diterbitkan.',
            'status' => 'draft',
        ])->assertSessionHasErrors('new_category');

        $this->assertSame(0, Category::count());
    }

    public function test_writer_still_cannot_manage_categories(): void
    {
        $category = Category::create(['name' => 'Panduan', 'slug' => 'panduan']);
        $writer = $this->writer();

        $this->actingAs($writer)->get('/admin/kategori')->assertForbidden();
        $this->actingAs($writer)->delete("/admin/kategori/{$category->id}")->assertForbidden();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_writer_cannot_touch_someone_elses_article(): void
    {
        $owner = $this->writer('pemilik@test.id');
        $other = $this->writer('lain@test.id');
        $article = $this->article($owner);

        $this->actingAs($other)->get("/penulis/artikel/{$article->id}/edit")->assertForbidden();
        $this->actingAs($other)->delete("/penulis/artikel/{$article->id}")->assertForbidden();
        $this->assertDatabaseHas('articles', ['id' => $article->id]);
    }

    public function test_writer_only_sees_own_articles_but_admin_sees_all(): void
    {
        $a = $this->writer('a@test.id');
        $b = $this->writer('b@test.id');
        $this->article($a, ['title' => 'Milik A']);
        $this->article($b, ['title' => 'Milik B']);

        $this->actingAs($a)->get('/penulis/artikel')->assertSee('Milik A')->assertDontSee('Milik B');
        $this->actingAs($this->admin())->get('/admin/artikel')->assertSee('Milik A')->assertSee('Milik B');
    }

    public function test_admin_can_edit_any_article(): void
    {
        $writer = $this->writer();
        $article = $this->article($writer, ['title' => 'Judul lama']);

        $this->actingAs($this->admin())->put("/admin/artikel/{$article->id}", [
            'title' => 'Judul baru',
            'body' => 'Isi artikel yang sudah diperbarui oleh admin.',
            'status' => 'published',
        ])->assertRedirect('/admin/artikel');

        $this->assertSame('Judul baru', $article->fresh()->title);
    }

    public function test_home_lists_only_published_articles(): void
    {
        $writer = $this->writer();
        $this->article($writer, ['title' => 'Sudah terbit']);
        $this->article($writer, ['title' => 'Masih draf', 'status' => Article::STATUS_DRAFT, 'published_at' => null]);

        $this->get('/')->assertSee('Sudah terbit')->assertDontSee('Masih draf');
    }

    public function test_home_search_filters_articles(): void
    {
        $writer = $this->writer();
        $this->article($writer, ['title' => 'Belajar Laravel']);
        $this->article($writer, ['title' => 'Resep nasi goreng']);

        $this->get('/?q=Laravel')->assertSee('Belajar Laravel')->assertDontSee('Resep nasi goreng');
    }

    public function test_draft_is_hidden_from_public_but_previewable_by_its_author(): void
    {
        $writer = $this->writer();
        $draft = $this->article($writer, [
            'title' => 'Rahasia',
            'slug' => 'rahasia',
            'status' => Article::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $this->get('/artikel/rahasia')->assertNotFound();
        $this->actingAs($writer)->get('/artikel/rahasia')->assertOk()->assertSee('pratinjau');
    }

    public function test_article_body_strips_raw_html(): void
    {
        $writer = $this->writer();
        $this->article($writer, [
            'slug' => 'aman',
            'body' => "Halo\n\n<script>alert('x')</script>\n\n**tebal**",
        ]);

        $this->get('/artikel/aman')->assertOk()->assertDontSee('<script>alert', false)->assertSee('<strong>tebal</strong>', false);
    }

    public function test_contact_form_stores_message_and_admin_can_read_it(): void
    {
        $this->post('/kontak', [
            'name' => 'Sari',
            'email' => 'sari@test.id',
            'subject' => 'Tanya artikel',
            'message' => 'Bagaimana cara menjadi penulis di sini?',
        ])->assertRedirect('/kontak');

        $message = ContactMessage::firstOrFail();
        $this->assertNull($message->read_at);

        $this->actingAs($this->admin())->get("/admin/pesan/{$message->id}")->assertOk();
        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_contact_honeypot_discards_bot_messages(): void
    {
        $this->post('/kontak', [
            'name' => 'Bot',
            'email' => 'bot@test.id',
            'subject' => 'Spam',
            'message' => 'Beli sekarang juga, murah sekali!',
            'website' => 'http://spam.example',
        ])->assertRedirect('/kontak');

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_form_validates_input(): void
    {
        $this->post('/kontak', ['name' => '', 'email' => 'bukan-email', 'subject' => '', 'message' => 'pendek'])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }
}
