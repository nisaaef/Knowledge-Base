@extends('layouts.app')

@section('title', 'Daftar jadi penulis')
@section('description', 'Daftar sebagai penulis. Akun aktif setelah disetujui admin.')

@section('content')
    <div class="mx-auto max-w-xl px-5 py-14">
        <h1 class="text-3xl font-bold tracking-tight">Daftar jadi penulis</h1>
        <p class="mt-2 text-slate-600">
            Isi formulir ini dengan benar. Anda bisa masuk setelah disetujui oleh admin.
        </p>

        <div class="mt-8">
            @include('partials.flash')

            <form method="POST" action="{{ route('register') }}" class="card space-y-5 p-6">
                @csrf

                {{-- Jebakan bot: manusia tidak melihat kolom ini. --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="label">Nama</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="100" autocomplete="name" class="input">
                    </div>
                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="150" autocomplete="email" class="input">
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="label">Kata sandi</label>
                        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="input">
                        <p class="hint">Minimal 8 karakter.</p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="label">Ulangi kata sandi</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="input">
                    </div>
                </div>

                <div>
                    <label for="application_note" class="label">Topik yang ingin kamu tulis</label>
                    <textarea id="application_note" name="application_note" rows="5" required minlength="20" maxlength="1000" class="input">{{ old('application_note') }}</textarea>
                    <p class="hint">Misalnya bidang keahlianmu dan contoh artikel yang ingin kamu buat. Minimal 20 karakter.</p>
                </div>

                <button type="submit" class="btn btn-primary w-full">Kirim pendaftaran</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-cobalt-600 hover:underline">Masuk</a>
            </p>
        </div>
    </div>
@endsection
