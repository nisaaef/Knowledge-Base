@extends('layouts.app')

@section('title', 'Kontak')
@section('description', 'Kirim pertanyaan atau saran ke admin.')

@section('content')
    <div class="mx-auto max-w-5xl px-5 py-14">
        <div class="grid gap-12 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <h1 class="text-4xl font-bold leading-tight tracking-tight">Hubungi kami</h1>
                <p class="mt-4 leading-relaxed text-slate-600">
                    Pertanyaanmu belum terjawab di artikel? Tulis di sini. Admin membalas lewat email yang kamu isi.
                </p>
                <p class="mt-6 text-sm text-slate-500">
                    Atau kirim langsung ke
                    <a href="mailto:{{ config('mail.from.address') }}" class="font-semibold text-cobalt-600 hover:underline">{{ config('mail.from.address') }}</a>
                </p>
            </div>

            <div class="lg:col-span-3">
                @include('partials.flash')

                <form method="POST" action="{{ route('contact.store') }}" class="card space-y-5 p-6">
                    @csrf

                    {{-- Jebakan bot: manusia tidak melihat kolom ini. --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="label">Nama</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="100" class="input">
                        </div>
                        <div>
                            <label for="email" class="label">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="150" class="input">
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="label">Subjek</label>
                        <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required maxlength="150" class="input">
                    </div>

                    <div>
                        <label for="message" class="label">Pesan</label>
                        <textarea id="message" name="message" rows="6" required minlength="10" maxlength="3000" class="input">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Kirim pesan</button>
                </form>
            </div>
        </div>
    </div>
@endsection
