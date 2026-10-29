@extends('layouts.dashboard')

@section('title', 'Kelola Profil')
@section('heading', 'Kelola Profil')

@section('actions')
    @if ($user->username)
        <a href="{{ $user->profileUrl() }}" target="_blank" rel="noopener" class="btn btn-ghost">Lihat profil publik</a>
    @endif
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <form method="POST" action="{{ route($area.'.profile.update') }}" enctype="multipart/form-data" class="card space-y-6 p-6 xl:col-span-2">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                @include('partials.avatar', ['user' => $user, 'size' => 'h-20 w-20 text-2xl'])
                <div class="space-y-2">
                    <label for="avatar" class="label mb-0">Foto profil</label>
                    <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-cobalt-50 file:px-3.5 file:py-2 file:text-sm file:font-semibold file:text-cobalt-700 hover:file:bg-cobalt-100">
                    <p class="hint !mt-0">JPG, PNG, atau WebP. Maksimal 2 MB. Foto persegi paling pas.</p>
                    @if ($user->avatar_path)
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="remove_avatar" value="1" class="h-4 w-4 rounded border-slate-300 text-cobalt-600 focus:ring-cobalt-500">
                            Hapus foto
                        </label>
                    @endif
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="100" class="input">
                </div>
                <div>
                    <label for="username" class="label">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required minlength="3" maxlength="30"
                           pattern="[a-z0-9_]+" autocapitalize="none" spellcheck="false" class="input">
                    <p class="hint">Huruf kecil, angka, dan _. Alamat profilmu: {{ url('/profil') }}/<strong>{{ old('username', $user->username) }}</strong></p>
                </div>
            </div>

            <div>
                <label for="headline" class="label">Judul singkat</label>
                <input id="headline" name="headline" type="text" value="{{ old('headline', $user->headline) }}" maxlength="100" class="input"
                       placeholder="Contoh: Teknisi jaringan, penulis panduan">
                <p class="hint">Satu baris di bawah namamu, misalnya jabatan atau keahlian.</p>
            </div>

            <div>
                <label for="bio" class="label">Bio</label>
                <textarea id="bio" name="bio" rows="4" maxlength="500" class="input">{{ old('bio', $user->bio) }}</textarea>
                <p class="hint">Maksimal 500 karakter. Tampil di profil publik dan di akhir artikelmu.</p>
            </div>

            <fieldset class="space-y-5">
                <legend class="label">Tautan</legend>
                <div>
                    <label for="website" class="sr-only">Situs web</label>
                    <input id="website" name="website" type="url" value="{{ old('website', $user->website) }}" maxlength="200" class="input" placeholder="Situs web, mulai dengan https://">
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="instagram" class="sr-only">Username Instagram</label>
                        <input id="instagram" name="instagram" type="text" value="{{ old('instagram', $user->instagram) }}" maxlength="30" class="input" placeholder="Username Instagram, tanpa @">
                    </div>
                    <div>
                        <label for="linkedin" class="sr-only">LinkedIn</label>
                        <input id="linkedin" name="linkedin" type="url" value="{{ old('linkedin', $user->linkedin) }}" maxlength="200" class="input" placeholder="Alamat profil LinkedIn">
                    </div>
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary">Simpan profil</button>
        </form>

        <form method="POST" action="{{ route($area.'.profile.password') }}" class="card h-fit space-y-5 p-6">
            @csrf
            @method('PUT')
            <h2 class="font-bold">Ganti kata sandi</h2>

            <div>
                <label for="current_password" class="label">Kata sandi saat ini</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="input">
            </div>
            <div>
                <label for="password" class="label">Kata sandi baru</label>
                <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="input">
                <p class="hint">Minimal 8 karakter.</p>
            </div>
            <div>
                <label for="password_confirmation" class="label">Ulangi kata sandi baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="input">
            </div>

            <p class="text-sm text-slate-500">Email akun: {{ $user->email }}. Untuk mengganti email, minta admin.</p>

            <button type="submit" class="btn btn-ghost">Ganti kata sandi</button>
        </form>
    </div>
@endsection
