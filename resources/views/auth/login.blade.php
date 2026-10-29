@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <div class="mx-auto max-w-md px-5 py-16">
        <h1 class="text-3xl text-center font-bold tracking-tight">Masuk</h1>

        <div class="mt-8">
            @include('partials.flash')

            <form method="POST" action="{{ route('login') }}" class="card space-y-5 p-6">
                @csrf

                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input">
                </div>

                <div>
                    <label for="password" class="label">Kata sandi</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="input">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-cobalt-600 focus:ring-cobalt-500">
                    Tetap masuk di perangkat ini
                </label>

                <button type="submit" class="btn btn-primary w-full">Masuk</button>
            </form>

            @if (config('knowledgebase.registration_open'))
                <p class="mt-6 text-center text-sm text-slate-600">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-cobalt-600 hover:underline">Daftar</a>
                </p>
            @endif
        </div>
    </div>
@endsection
