<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $email = 'admin@test.id'): User
    {
        return User::create([
            'name' => 'Admin', 'email' => $email, 'password' => 'password',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);
    }

    private function writer(string $email = 'penulis@test.id', array $extra = []): User
    {
        return User::create($extra + [
            'name' => 'Budi Santoso', 'email' => $email, 'password' => 'password',
            'role' => User::ROLE_WRITER, 'is_active' => true,
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

    private function articlePayload(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'Judul artikel baru',
            'body' => 'Isi artikel yang cukup panjang untuk diterbitkan.',
            'status' => 'published',
        ];
    }

    public function test_contact_message_notifies_admins_only(): void
    {
        $admin = $this->admin();
        $writer = $this->writer();

        $this->post('/kontak', [
            'name' => 'Sari', 'email' => 'sari@test.id', 'subject' => 'Tanya artikel',
            'message' => 'Bagaimana cara menjadi penulis di sini?',
        ])->assertRedirect('/kontak');

        $this->assertSame(1, $admin->unreadNotifications()->count());
        $this->assertSame('Pesan baru dari Sari', $admin->notifications()->first()->data['title']);
        $this->assertSame(0, $writer->notifications()->count());
    }

    public function test_bot_contact_message_does_not_notify(): void
    {
        $admin = $this->admin();

        $this->post('/kontak', [
            'name' => 'Bot', 'email' => 'bot@test.id', 'subject' => 'Spam',
            'message' => 'Beli sekarang juga, murah sekali!', 'website' => 'http://spam.example',
        ]);

        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_new_registration_notifies_admins(): void
    {
        $admin = $this->admin();

        $this->post('/daftar', [
            'name' => 'Rina Pendaftar', 'email' => 'rina@test.id',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            'application_note' => 'Saya ingin menulis panduan jaringan rumahan untuk pemula.',
        ])->assertRedirect('/login');

        $notice = $admin->notifications()->first();
        $this->assertSame('Pendaftar penulis baru', $notice->data['title']);
        $this->assertStringContainsString('Rina Pendaftar', $notice->data['body']);
    }

    public function test_writer_publishing_notifies_admin_once(): void
    {
        $admin = $this->admin();
        $writer = $this->writer();

        // Draf tidak memberi tahu siapa pun.
        $this->actingAs($writer)->post('/penulis/artikel', $this->articlePayload(['status' => 'draft']));
        $this->assertSame(0, $admin->notifications()->count());

        // Draf diterbitkan: admin diberi tahu.
        $article = Article::first();
        $this->actingAs($writer)->put("/penulis/artikel/{$article->id}", $this->articlePayload());
        $this->assertSame(1, $admin->notifications()->count());
        $this->assertStringContainsString('Budi Santoso', $admin->notifications()->first()->data['title']);

        // Mengubah artikel yang sudah terbit tidak mengirim notifikasi lagi.
        $this->actingAs($writer)->put("/penulis/artikel/{$article->id}", $this->articlePayload(['title' => 'Judul diperbarui']));
        $this->assertSame(1, $admin->notifications()->count());
    }

    public function test_publishing_directly_as_a_writer_notifies_admin(): void
    {
        $admin = $this->admin();

        $this->actingAs($this->writer())->post('/penulis/artikel', $this->articlePayload());

        $this->assertSame(1, $admin->notifications()->count());
    }

    public function test_admin_publishing_does_not_notify_admins(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/artikel', $this->articlePayload());

        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_admin_editing_a_writers_article_notifies_the_writer(): void
    {
        $admin = $this->admin();
        $writer = $this->writer();
        $article = $this->article($writer);

        $this->actingAs($admin)->put("/admin/artikel/{$article->id}", $this->articlePayload(['title' => 'Judul dikoreksi admin']))
            ->assertRedirect('/admin/artikel');

        $notice = $writer->notifications()->first();
        $this->assertSame('Artikelmu diubah oleh admin', $notice->data['title']);
        $this->assertSame('Judul dikoreksi admin', $notice->data['body']);
        $this->assertStringContainsString("/penulis/artikel/{$article->id}/edit", $notice->data['url']);
    }

    public function test_writer_editing_own_article_does_not_notify_the_writer(): void
    {
        $writer = $this->writer();
        $article = $this->article($writer);

        $this->actingAs($writer)->put("/penulis/artikel/{$article->id}", $this->articlePayload(['title' => 'Revisi sendiri']));

        $this->assertSame(0, $writer->notifications()->count());
    }

    public function test_admin_deleting_a_writers_article_notifies_the_writer(): void
    {
        $admin = $this->admin();
        $writer = $this->writer();
        $article = $this->article($writer, ['title' => 'Akan dihapus']);

        $this->actingAs($admin)->delete("/admin/artikel/{$article->id}")->assertRedirect('/admin/artikel');

        $notice = $writer->notifications()->first();
        $this->assertSame('Artikelmu dihapus oleh admin', $notice->data['title']);
        $this->assertSame('Akan dihapus', $notice->data['body']);
    }

    public function test_writer_deleting_own_article_does_not_notify(): void
    {
        $writer = $this->writer();
        $article = $this->article($writer);

        $this->actingAs($writer)->delete("/penulis/artikel/{$article->id}");

        $this->assertSame(0, $writer->notifications()->count());
    }

    public function test_approving_an_applicant_notifies_the_applicant(): void
    {
        $admin = $this->admin();
        $applicant = $this->writer('baru@test.id', ['is_active' => false, 'pending_approval' => true]);

        $this->actingAs($admin)->patch("/admin/penulis/{$applicant->id}/setujui")->assertRedirect();

        $this->assertSame('Akunmu sudah disetujui', $applicant->notifications()->first()->data['title']);
    }

    public function test_granting_access_to_an_applicant_through_toggle_also_notifies(): void
    {
        $applicant = $this->writer('baru@test.id', ['is_active' => false, 'pending_approval' => true]);

        $this->actingAs($this->admin())->patch("/admin/penulis/{$applicant->id}/akses");

        $this->assertSame(1, $applicant->notifications()->count());
    }

    public function test_revoking_or_toggling_a_normal_writer_does_not_notify(): void
    {
        $writer = $this->writer();

        $this->actingAs($this->admin())->patch("/admin/penulis/{$writer->id}/akses");

        $this->assertSame(0, $writer->notifications()->count());
    }

    public function test_opening_a_notification_marks_it_read_and_redirects(): void
    {
        $admin = $this->admin();
        $admin->pushNotice('Judul', 'Isi', route('admin.messages.index'));
        $notification = $admin->notifications()->first();

        $this->actingAs($admin)->get('/notifikasi/'.$notification->id)->assertRedirect(route('admin.messages.index'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_users_cannot_open_other_peoples_notifications(): void
    {
        $admin = $this->admin();
        $writer = $this->writer();
        $admin->pushNotice('Rahasia admin', null, route('admin.dashboard'));
        $notification = $admin->notifications()->first();

        $this->actingAs($writer)->get('/notifikasi/'.$notification->id)->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read(): void
    {
        $writer = $this->writer();
        $writer->pushNotice('Satu', null, route('penulis.dashboard'));
        $writer->pushNotice('Dua', null, route('penulis.dashboard'));

        $this->actingAs($writer)->post('/notifikasi/baca-semua')->assertRedirect();

        $this->assertSame(0, $writer->unreadNotifications()->count());
        $this->assertSame(2, $writer->notifications()->count());
    }

    public function test_bell_shows_unread_count_in_both_dashboards(): void
    {
        $admin = $this->admin();
        $writer = $this->writer();
        $admin->pushNotice('Pesan baru dari Sari', 'Tanya artikel', route('admin.messages.index'));
        $writer->pushNotice('Akunmu sudah disetujui', 'Selamat menulis', route('penulis.dashboard'));
        $writer->pushNotice('Artikelmu diubah oleh admin', 'Judul', route('penulis.articles.index'));

        $this->actingAs($admin)->get('/admin')
            ->assertSee('1 belum dibaca')
            ->assertSee('Pesan baru dari Sari');

        $this->actingAs($writer)->get('/penulis')
            ->assertSee('2 belum dibaca')
            ->assertSee('Akunmu sudah disetujui');
    }

    public function test_bell_is_empty_for_new_users(): void
    {
        $this->actingAs($this->writer())->get('/penulis')->assertSee('Belum ada notifikasi.');
    }

    public function test_notification_routes_require_login_and_active_account(): void
    {
        $this->get('/notifikasi/abc')->assertRedirect('/login');
        $this->post('/notifikasi/baca-semua')->assertRedirect('/login');

        $revoked = $this->writer('mati@test.id', ['is_active' => false]);
        $this->actingAs($revoked)->post('/notifikasi/baca-semua')->assertRedirect('/login');
    }

    public function test_contact_message_link_in_notification_points_to_that_message(): void
    {
        $admin = $this->admin();
        $this->post('/kontak', [
            'name' => 'Sari', 'email' => 'sari@test.id', 'subject' => 'Tanya artikel',
            'message' => 'Bagaimana cara menjadi penulis di sini?',
        ]);

        $message = ContactMessage::firstOrFail();

        $this->assertSame(route('admin.messages.show', $message), $admin->notifications()->first()->data['url']);
    }
}
