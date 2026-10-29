<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin@test.id', 'password' => 'password',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);
    }

    public function test_defaults_are_used_before_anything_is_saved(): void
    {
        config(['app.name' => 'Nama Dari Env']);

        $this->assertSame('Nama Dari Env', Setting::siteName());
        $this->get('/')->assertOk()->assertSee('Nama Dari Env')->assertSee('Temukan jawabanmu disini');
    }

    public function test_admin_can_change_site_name_and_hero_text(): void
    {
        $this->actingAs($this->admin())->put('/admin/pengaturan', [
            'site_name' => 'Pusat Bantuan Kita',
            'hero_title' => 'Ada yang bisa dibantu?',
            'hero_subtitle' => 'Cari panduan di bawah ini.',
        ])->assertRedirect('/admin/pengaturan');

        $this->get('/')
            ->assertOk()
            ->assertSee('Pusat Bantuan Kita')
            ->assertSee('Ada yang bisa dibantu?')
            ->assertSee('Cari panduan di bawah ini.');

        $this->get('/tentang')->assertSee('Tentang Pusat Bantuan Kita');
        $this->actingAs($this->admin())->get('/admin')->assertSee('Pusat Bantuan Kita');
    }

    public function test_clearing_hero_text_returns_to_default(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/pengaturan', [
            'site_name' => 'Situs A', 'hero_title' => 'Judul khusus', 'hero_subtitle' => '',
        ]);
        $this->assertSame('Judul khusus', Setting::read('hero_title'));

        $this->actingAs($admin)->put('/admin/pengaturan', [
            'site_name' => 'Situs A', 'hero_title' => '', 'hero_subtitle' => '',
        ]);
        $this->assertSame('Temukan jawabanmu disini', Setting::read('hero_title'));
    }

    public function test_site_name_is_required_and_limited(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/pengaturan', ['site_name' => ''])->assertSessionHasErrors('site_name');
        $this->actingAs($admin)->put('/admin/pengaturan', ['site_name' => str_repeat('a', 61)])->assertSessionHasErrors('site_name');
    }

    public function test_site_name_is_escaped_in_pages(): void
    {
        $this->actingAs($this->admin())->put('/admin/pengaturan', ['site_name' => '<script>alert(1)</script>']);

        $this->get('/')->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_admin_can_upload_replace_and_remove_the_logo(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->get('/')->assertDontSee('storage/branding', false);

        $this->actingAs($admin)->put('/admin/pengaturan', [
            'site_name' => 'Situs A',
            'logo' => UploadedFile::fake()->image('logo.png', 200, 80),
            'logo_show_name' => '1',
        ])->assertRedirect('/admin/pengaturan');

        $first = \App\Models\Setting::allValues()['logo_path'];
        Storage::disk('public')->assertExists($first);
        $this->get('/')->assertSee('storage/branding', false);
        $this->actingAs($admin)->get('/admin')->assertSee('storage/branding', false);

        // Mengganti logo menghapus file lama.
        $this->actingAs($admin)->put('/admin/pengaturan', [
            'site_name' => 'Situs A',
            'logo' => UploadedFile::fake()->image('baru.png', 200, 80),
        ]);
        $second = \App\Models\Setting::allValues()['logo_path'];
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        // Hapus logo.
        $this->actingAs($admin)->put('/admin/pengaturan', ['site_name' => 'Situs A', 'remove_logo' => 1]);
        $this->assertNull(\App\Models\Setting::logoUrl());
        Storage::disk('public')->assertMissing($second);
        $this->get('/')->assertDontSee('storage/branding', false);
    }

    public function test_logo_must_be_a_small_png_jpg_or_webp_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/pengaturan', [
            'site_name' => 'Situs A',
            'logo' => UploadedFile::fake()->create('logo.pdf', 50, 'application/pdf'),
        ])->assertSessionHasErrors('logo');

        $this->actingAs($admin)->put('/admin/pengaturan', [
            'site_name' => 'Situs A',
            'logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
        ])->assertSessionHasErrors('logo');

        $this->actingAs($admin)->put('/admin/pengaturan', [
            'site_name' => 'Situs A',
            'logo' => UploadedFile::fake()->image('besar.png')->size(2048),
        ])->assertSessionHasErrors('logo');

        $this->assertNull(\App\Models\Setting::logoUrl());
    }

    public function test_name_can_be_hidden_next_to_the_logo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->put('/admin/pengaturan', [
            'site_name' => 'Nama Unik Situs',
            'logo' => UploadedFile::fake()->image('logo.png', 200, 80),
            'logo_show_name' => '0',
        ]);

        $this->assertFalse(\App\Models\Setting::showsNameWithLogo());
        // Nama tidak lagi tampil sebagai teks di header, tetapi tetap jadi alt gambar.
        $this->get('/')->assertSee('alt="Nama Unik Situs"', false);
    }

    public function test_only_admin_can_open_settings(): void
    {
        $writer = User::create([
            'name' => 'Penulis', 'email' => 'p@test.id', 'password' => 'password',
            'role' => User::ROLE_WRITER, 'is_active' => true,
        ]);

        $this->get('/admin/pengaturan')->assertRedirect('/login');
        $this->actingAs($writer)->get('/admin/pengaturan')->assertForbidden();
        $this->actingAs($writer)->put('/admin/pengaturan', ['site_name' => 'Peretas'])->assertForbidden();
    }
}
