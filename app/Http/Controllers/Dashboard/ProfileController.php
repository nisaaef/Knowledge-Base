<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Profil milik pengguna yang sedang masuk. Dipakai di dua sisi dasbor:
 *  - /admin/profil
 *  - /penulis/profil
 */
class ProfileController extends Controller
{
    private function area(): string
    {
        return request()->routeIs('admin.*') ? 'admin' : 'penulis';
    }

    public function edit(Request $request)
    {
        return view('dashboard.profile.edit', [
            'user' => $request->user(),
            'area' => $this->area(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Rapikan input sebelum divalidasi: username huruf kecil, Instagram tanpa "@".
        $request->merge([
            'username' => Str::lower(trim((string) $request->input('username'))),
            'instagram' => ltrim(trim((string) $request->input('instagram')), '@'),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'headline' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url:http,https', 'max:200'],
            'instagram' => ['nullable', 'regex:/^[A-Za-z0-9._]{1,30}$/'],
            'linkedin' => ['nullable', 'url:http,https', 'max:200'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, dan garis bawah (_).',
            'username.unique' => 'Username itu sudah dipakai. Coba yang lain.',
            'instagram.regex' => 'Isi username Instagram saja, tanpa tautan lengkap.',
        ]);

        $fields = [
            'name' => $data['name'],
            'username' => $data['username'],
            'headline' => filled($data['headline'] ?? null) ? $data['headline'] : null,
            'bio' => filled($data['bio'] ?? null) ? $data['bio'] : null,
            'website' => filled($data['website'] ?? null) ? $data['website'] : null,
            'instagram' => filled($data['instagram'] ?? null) ? $data['instagram'] : null,
            'linkedin' => filled($data['linkedin'] ?? null) ? $data['linkedin'] : null,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $fields['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $fields['avatar_path'] = null;
        }

        $user->update($fields);

        return redirect()->route($this->area().'.profile.edit')->with('status', 'Profil disimpan.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password.different' => 'Kata sandi baru harus berbeda dari yang lama.',
        ]);

        $request->user()->update(['password' => $data['password']]);

        return redirect()->route($this->area().'.profile.edit')->with('status', 'Kata sandi diganti.');
    }
}
