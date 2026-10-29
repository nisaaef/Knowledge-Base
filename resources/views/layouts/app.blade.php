<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    @include('partials.head')
    @include('partials.favicon')
    <title>@hasSection('title')@yield('title') - @endif{{ \App\Models\Setting::siteName() }}</title>
    @include('partials.seo')
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-mist font-sans text-ink antialiased">

    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-2 px-5 py-4">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5 text-lg font-bold tracking-tight">
                {{-- isi logo tetap sama seperti sebelumnya --}}
            </a>

            {{-- Tombol hamburger (hanya di HP) --}}
            <button type="button" id="menu-toggle"
                    class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 md:hidden"
                    aria-controls="main-menu" aria-expanded="false" aria-label="Buka menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <nav id="main-menu"
                class="hidden w-full flex-col gap-1 pt-2 text-sm font-semibold md:flex md:w-auto md:flex-row md:items-center md:pt-0"
                aria-label="Menu utama">
                @php
                    $links = [
                        ['Home', 'home'],
                        ['About', 'about'],
                        ['Contact', 'contact'],
                    ];
                @endphp
                @foreach ($links as [$label, $route])
                    <a href="{{ route($route) }}"
                    @if (request()->routeIs($route)) aria-current="page" @endif
                    class="rounded-lg px-3 py-2 {{ request()->routeIs($route) ? 'bg-cobalt-50 text-cobalt-700' : 'text-slate-600 hover:bg-slate-100 hover:text-ink' }}">
                        {{ $label }}
                    </a>
                @endforeach

                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm mt-2 md:ml-2 md:mt-0">Dasboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm mt-2 md:ml-2 md:mt-0">LogIn</a>
                    @if (config('knowledgebase.registration_open'))
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">SignUp</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ \App\Models\Setting::siteName() }}</p>
        </div>
    </footer>

    <script>
    const toggle = document.getElementById('menu-toggle');
    const menu = document.getElementById('main-menu');
    toggle.addEventListener('click', () => {
        menu.classList.toggle('hidden');
        menu.classList.toggle('flex');
        toggle.setAttribute('aria-expanded', menu.classList.contains('flex'));
    });
    </script>

</body>
</html>
