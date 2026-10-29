<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function writer(string $name = 'Budi Santoso', string $email = 'budi@test.id'): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'role' => User::ROLE_WRITER,
            'is_active' => true,
        ]);
    }

    private function validProfile(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Budi Santoso',
            'username' => 'budi_s',
            'headline' => 'Teknisi jaringan',
            'bio' => 'Menulis panduan jaringan sejak 2020.',
        ];
    }

    public function test_new_accounts_get_a_unique_username_automatically(): void
    {
        $first = $this->writer('Budi Santoso', 'a@test.id');
        $second = $this->writer('Budi Santoso', 'b@test.id');

        $this->assertSame('budi_santoso', $first->username);
        $this->assertSame('budi_santoso_2', $second->username);
    }

    public function test_writer_can_update_profile(): void
    {
        $writer = $this->writer();

        $this->actingAs($writer)->put('/penulis/profil', $this->validProfile([
            'website' => 'https://budi.example.com',
            'instagram' => '@budi.s',
            'linkedin' => 'https://linkedin.com/in/budi',
        ]))->assertRedirect('/penulis/profil');

        $writer->refresh();
        $this->assertSame('budi_s', $writer->username);
        $this->assertSame('Teknisi jaringan', $writer->headline);
        $this->assertSame('budi.s', $writer->instagram);
        $this->assertSame('https://budi.example.com', $writer->website);
    }

    public function test_admin_can_update_own_profile_in_admin_area(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.id', 'password' => 'password',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/admin/profil')->assertOk();
        $this->actingAs($admin)->put('/admin/profil', $this->validProfile(['name' => 'Admin', 'username' => 'admin_kb']))
            ->assertRedirect('/admin/profil');

        $this->assertSame('admin_kb', $admin->fresh()->username);
    }

    public function test_username_must_be_unique_and_well_formed(): void
    {
        $this->writer('Siti', 'siti@test.id')->update(['username' => 'siti']);
        $writer = $this->writer();

        $this->actingAs($writer)->put('/penulis/profil', $this->validProfile(['username' => 'siti']))
            ->assertSessionHasErrors('username');

        $this->actingAs($writer)->put('/penulis/profil', $this->validProfile(['username' => 'Budi Santoso!']))
            ->assertSessionHasErrors('username');
    }

    public function test_website_must_be_http_or_https(): void
    {
        $this->actingAs($this->writer())->put('/penulis/profil', $this->validProfile(['website' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('website');
    }

    public function test_bio_has_a_length_limit(): void
    {
        $this->actingAs($this->writer())->put('/penulis/profil', $this->validProfile(['bio' => str_repeat('a', 501)]))
            ->assertSessionHasErrors('bio');
    }

    public function test_writer_can_upload_and_remove_avatar(): void
    {
        Storage::fake('public');
        $writer = $this->writer();

        $this->actingAs($writer)->put('/penulis/profil', $this->validProfile([
            'avatar' => UploadedFile::fake()->image('foto.jpg', 300, 300),
        ]))->assertRedirect('/penulis/profil');

        $path = $writer->fresh()->avatar_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($writer)->put('/penulis/profil', $this->validProfile(['remove_avatar' => 1]))
            ->assertRedirect('/penulis/profil');

        $this->assertNull($writer->fresh()->avatar_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_password_change_requires_current_password(): void
    {
        $writer = $this->writer();

        $this->actingAs($writer)->put('/penulis/profil/kata-sandi', [
            'current_password' => 'salah',
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($writer)->put('/penulis/profil/kata-sandi', [
            'current_password' => 'password',
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ])->assertRedirect('/penulis/profil');

        $this->post('/keluar');
        $this->post('/login', ['email' => $writer->email, 'password' => 'sandibaru123'])->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_public_profile_lists_only_published_articles_of_that_author(): void
    {
        $writer = $this->writer();
        $writer->update(['bio' => 'Bio singkat penulis.']);
        $other = $this->writer('Lain', 'lain@test.id');

        $make = fn (User $u, string $title, string $status) => Article::create([
            'user_id' => $u->id, 'title' => $title, 'slug' => str($title)->slug()->toString(),
            'body' => 'Isi artikel yang cukup panjang untuk contoh.', 'status' => $status,
            'published_at' => $status === 'published' ? now()->subMinute() : null,
        ]);

        $make($writer, 'Tulisan terbit saya', 'published');
        $make($writer, 'Tulisan draf saya', 'draft');
        $make($other, 'Tulisan orang lain', 'published');

        $this->get('/profil/'.$writer->username)
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Bio singkat penulis.')
            ->assertSee('Tulisan terbit saya')
            ->assertDontSee('Tulisan draf saya')
            ->assertDontSee('Tulisan orang lain');
    }

    public function test_public_profile_is_hidden_when_access_is_revoked_or_unknown(): void
    {
        $writer = $this->writer();
        $writer->update(['is_active' => false]);

        $this->get('/profil/'.$writer->username)->assertNotFound();
        $this->get('/profil/tidak_ada')->assertNotFound();
    }

    public function test_admin_tables_show_writer_avatars(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.id', 'password' => 'password',
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);
        $withPhoto = $this->writer('Budi Santoso', 'foto@test.id');
        $withPhoto->update(['avatar_path' => 'avatars/budi.jpg']);
        $noPhoto = $this->writer('Siti Aminah', 'tanpa@test.id');

        foreach ([$withPhoto, $noPhoto] as $writer) {
            Article::create([
                'user_id' => $writer->id, 'title' => 'Tulisan '.$writer->name, 'slug' => 'tulisan-'.$writer->id,
                'body' => 'Isi artikel yang cukup panjang untuk contoh.', 'status' => 'published', 'published_at' => now()->subMinute(),
            ]);
        }

        // Tabel artikel (sisi admin) dan tabel penulis sama-sama menampilkan foto, atau inisial bila belum ada.
        foreach (['/admin/artikel', '/admin/penulis'] as $url) {
            $this->actingAs($admin)->get($url)
                ->assertOk()
                ->assertSee('storage/avatars/budi.jpg', false)
                ->assertSee('>SA<', false)
                ->assertSee('rtable', false);
        }
    }

    public function test_article_page_shows_author_box_with_link_to_profile(): void
    {
        $writer = $this->writer();
        $writer->update(['headline' => 'Teknisi jaringan']);
        Article::create([
            'user_id' => $writer->id, 'title' => 'Artikel ber-penulis', 'slug' => 'artikel-ber-penulis',
            'body' => 'Isi artikel yang cukup panjang untuk contoh.', 'status' => 'published', 'published_at' => now()->subMinute(),
        ]);

        $this->get('/artikel/artikel-ber-penulis')
            ->assertOk()
            ->assertSee('Teknisi jaringan')
            ->assertSee('/profil/'.$writer->username, false);
    }
}
