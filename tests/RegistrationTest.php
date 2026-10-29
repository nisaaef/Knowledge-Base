<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Rina Pendaftar',
            'email' => 'rina@test.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'application_note' => 'Saya ingin menulis panduan jaringan rumahan untuk pemula.',
        ];
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin@test.id', 'password' => 'password',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);
    }

    private function registerRina(): User
    {
        $this->post('/daftar', $this->payload())->assertRedirect('/login');

        return User::where('email', 'rina@test.id')->firstOrFail();
    }

    public function test_registration_page_is_open_to_guests(): void
    {
        $this->get('/daftar')->assertOk()->assertSee('Daftar jadi penulis');
    }

    public function test_registration_creates_a_pending_inactive_writer(): void
    {
        $user = $this->registerRina();

        $this->assertSame(User::ROLE_WRITER, $user->role);
        $this->assertFalse($user->is_active);
        $this->assertTrue($user->pending_approval);
        $this->assertSame('Saya ingin menulis panduan jaringan rumahan untuk pemula.', $user->application_note);
        $this->assertNotSame('rahasia123', $user->password);
        $this->assertNotNull($user->username);
        $this->assertGuest();
    }

    public function test_pending_writer_cannot_log_in_and_is_told_why(): void
    {
        $this->registerRina();

        $this->post('/login', ['email' => 'rina@test.id', 'password' => 'rahasia123'])
            ->assertSessionHasErrors(['email' => 'Akunmu masih menunggu persetujuan admin. Kamu bisa masuk setelah disetujui.']);

        $this->assertGuest();
    }

    public function test_wrong_password_does_not_reveal_pending_status(): void
    {
        $this->registerRina();

        $this->post('/login', ['email' => 'rina@test.id', 'password' => 'salah-total'])
            ->assertSessionHasErrors('email');

        $this->assertStringNotContainsString(
            'menunggu persetujuan',
            collect(session('errors')->get('email'))->implode(' ')
        );
    }

    public function test_pending_writer_has_no_public_profile(): void
    {
        $user = $this->registerRina();

        $this->get('/profil/'.$user->username)->assertNotFound();
    }

    public function test_admin_sees_pending_applicants_and_can_approve(): void
    {
        $admin = $this->admin();
        $user = $this->registerRina();

        $this->actingAs($admin)->get('/admin/penulis')
            ->assertOk()
            ->assertSee('Menunggu persetujuan')
            ->assertSee('Saya ingin menulis panduan jaringan rumahan untuk pemula.');

        $this->actingAs($admin)->get('/admin')->assertSee('1 pendaftar penulis menunggu persetujuanmu.');

        $this->actingAs($admin)->patch("/admin/penulis/{$user->id}/setujui")->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->is_active);
        $this->assertFalse($user->pending_approval);

        auth()->logout();

        $this->post('/login', ['email' => 'rina@test.id', 'password' => 'rahasia123'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_reject_an_applicant(): void
    {
        $user = $this->registerRina();

        $this->actingAs($this->admin())->delete("/admin/penulis/{$user->id}")->assertRedirect('/admin/penulis');

        $this->assertDatabaseMissing('users', ['email' => 'rina@test.id']);
    }

    public function test_granting_access_through_toggle_also_clears_pending(): void
    {
        $user = $this->registerRina();

        $this->actingAs($this->admin())->patch("/admin/penulis/{$user->id}/akses")->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->is_active);
        $this->assertFalse($user->pending_approval);
    }

    public function test_only_admin_can_approve(): void
    {
        $user = $this->registerRina();
        $other = User::create([
            'name' => 'Penulis Lain', 'email' => 'lain@test.id', 'password' => 'password',
            'role' => User::ROLE_WRITER, 'is_active' => true,
        ]);

        $this->actingAs($other)->patch("/admin/penulis/{$user->id}/setujui")->assertForbidden();
        $this->assertTrue($user->fresh()->pending_approval);
    }

    public function test_registration_is_validated(): void
    {
        $this->post('/daftar', $this->payload(['application_note' => 'pendek', 'password_confirmation' => 'beda']))
            ->assertSessionHasErrors(['application_note', 'password']);

        User::create([
            'name' => 'Sudah Ada', 'email' => 'ada@test.id', 'password' => 'password',
            'role' => User::ROLE_WRITER, 'is_active' => true,
        ]);

        $this->post('/daftar', $this->payload(['email' => 'ada@test.id']))->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'rina@test.id']);
    }

    public function test_honeypot_discards_bot_registrations(): void
    {
        $this->post('/daftar', $this->payload(['website' => 'http://spam.example']))->assertRedirect('/login');

        $this->assertDatabaseMissing('users', ['email' => 'rina@test.id']);
    }

    public function test_registration_can_be_closed(): void
    {
        config(['knowledgebase.registration_open' => false]);

        $this->get('/daftar')->assertNotFound();
        $this->post('/daftar', $this->payload())->assertNotFound();
        $this->get('/login')->assertDontSee('Daftar jadi penulis');
    }

    public function test_logged_in_users_are_redirected_away_from_register(): void
    {
        $this->actingAs($this->admin())->get('/daftar')->assertRedirect();
    }
}
