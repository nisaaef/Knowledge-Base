<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    @include('partials.favicon')
    <meta name="robots" content="noindex">
    <title>@hasSection('title')@yield('title') - @endif Dasbor {{ \App\Models\Setting::siteName() }}</title>

    {{-- Tentukan keadaan sidebar sebelum halaman digambar agar tidak berkedip. --}}
    <script>
        (function () {
            var saved = null;
            try { saved = localStorage.getItem('kb-sidebar'); } catch (e) {}
            var desktop = window.matchMedia('(min-width: 1024px)').matches;
            document.documentElement.setAttribute('data-sidebar', desktop && saved !== 'closed' ? 'open' : 'closed');
        })();
    </script>
    <style>
        #sidebar {
            transform: translateX(-100%);
            visibility: hidden;
            transition: transform .2s ease, visibility 0s linear .2s;
        }
        html[data-sidebar="open"] #sidebar {
            transform: none;
            visibility: visible;
            transition: transform .2s ease, visibility 0s;
        }
        #sidebar-backdrop { display: none; }
        @media (max-width: 1023.98px) {
            html[data-sidebar="open"] #sidebar-backdrop { display: block; }
        }
        @media (min-width: 1024px) {
            #content { transition: padding-left .2s ease; }
            html[data-sidebar="open"] #content { padding-left: 16rem; }
        }
    </style>
</head>
<body class="bg-mist font-sans text-ink antialiased">
@php
    $user = auth()->user();

    $nav = $user->isAdmin()
        ? [
            ['Beranda', 'admin.dashboard', 'admin.dashboard'],
            ['Artikel', 'admin.articles.index', 'admin.articles.*'],
            ['Kategori', 'admin.categories.index', 'admin.categories.*'],
            ['Penulis', 'admin.writers.index', 'admin.writers.*'],
            ['Pesan', 'admin.messages.index', 'admin.messages.*'],
            ['Pengaturan Website', 'admin.settings.edit', 'admin.settings.*'],
            ['Kelola Profil', 'admin.profile.edit', 'admin.profile.*'],
        ]
        : [
            ['Beranda', 'penulis.dashboard', 'penulis.dashboard'],
            ['Artikel saya', 'penulis.articles.index', 'penulis.articles.*'],
            ['Kelola Profil', 'penulis.profile.edit', 'penulis.profile.*'],
        ];

    $unread = $user->isAdmin() ? \App\Models\ContactMessage::whereNull('read_at')->count() : 0;
    $pendingWriters = $user->isAdmin() ? \App\Models\User::where('pending_approval', true)->count() : 0;

    $notifications = $user->notifications()->latest()->take(8)->get();
    $unreadCount = $user->unreadNotifications()->count();
@endphp

<div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-ink/50" aria-hidden="true"></div>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-ink text-slate-200" aria-label="Menu dasbor">
    <div class="flex items-start justify-between gap-3 px-5 py-5">
        <div class="min-w-0">
            <a href="{{ route('home') }}" class="block truncate text-lg font-bold tracking-tight text-white">{{ \App\Models\Setting::siteName() }}</a>
        </div>
        <button type="button" id="sidebar-close" class="-mr-2 rounded-lg p-2 text-slate-300 hover:bg-white/10 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white lg:hidden">
            <span class="sr-only">Tutup menu</span>
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-3">
        @foreach ($nav as [$label, $route, $pattern])
            <a href="{{ route($route) }}"
               @if (request()->routeIs($pattern)) aria-current="page" @endif
               class="flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ request()->routeIs($pattern) ? 'bg-cobalt-600 text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                {{ $label }}
                @if ($route === 'admin.messages.index' && $unread > 0)
                    <span class="badge bg-amber-400 text-ink">{{ $unread }}</span>
                @elseif ($route === 'admin.writers.index' && $pendingWriters > 0)
                    <span class="badge bg-amber-400 text-ink">{{ $pendingWriters }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <div class="border-t border-white/10 px-5 py-4">
        <div class="flex min-w-0 items-center gap-3">
            @include('partials.avatar', ['user' => $user, 'size' => 'h-9 w-9 text-xs'])
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                <p class="truncate text-xs text-slate-400">{{ $user->email }}</p>
            </div>
        </div>
    </div>
</aside>

<div id="content">
    <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-slate-200 bg-white px-4 lg:px-8">
        <button type="button" id="sidebar-toggle" aria-controls="sidebar" aria-expanded="false"
                class="-ml-2 rounded-lg p-2 text-slate-600 hover:bg-slate-100 hover:text-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-cobalt-600">
            <span class="sr-only">Buka atau tutup menu</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" /></svg>
        </button>
        @php $logoUrl = \App\Models\Setting::logoUrl(); @endphp
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::showsNameWithLogo() ? '' : \App\Models\Setting::siteName() }}" class="h-8 w-auto max-w-[160px] object-contain">
        @endif
        @if (! $logoUrl || \App\Models\Setting::showsNameWithLogo())
            <span class="truncate font-bold tracking-tight">{{ \App\Models\Setting::siteName() }}</span>
        @endif
        <div class="ml-auto flex shrink-0 items-center gap-3">
            <div class="relative">
                <button type="button" id="notif-toggle" aria-controls="notif-panel" aria-expanded="false"
                        class="relative rounded-lg p-2 text-slate-600 hover:bg-slate-100 hover:text-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-cobalt-600">
                    <span class="sr-only">Notifikasi{{ $unreadCount > 0 ? ', '.$unreadCount.' belum dibaca' : '' }}</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                    @if ($unreadCount > 0)
                        <span class="absolute -right-0.5 -top-0.5 grid min-h-[18px] min-w-[18px] place-items-center rounded-full bg-red-600 px-1 text-[11px] font-bold leading-none text-white" aria-hidden="true">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </button>

                <div id="notif-panel" hidden class="absolute right-0 z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                        <h2 class="text-sm font-bold">Notifikasi</h2>
                        @if ($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-cobalt-600 hover:underline">Tandai semua dibaca</button>
                            </form>
                        @endif
                    </div>

                    <ul class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
                        @forelse ($notifications as $notification)
                            <li>
                                <a href="{{ route('notifications.open', $notification->id) }}"
                                   class="block px-4 py-3 hover:bg-slate-50 {{ $notification->read_at ? '' : 'bg-cobalt-50/60' }}">
                                    <span class="block text-sm {{ $notification->read_at ? 'font-medium text-slate-700' : 'font-bold text-ink' }}">{{ $notification->data['title'] ?? '' }}</span>
                                    @if (! empty($notification->data['body']))
                                        <span class="block truncate text-sm text-slate-500">{{ $notification->data['body'] }}</span>
                                    @endif
                                    <span class="mt-0.5 block text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="px-4 py-8 text-center text-sm text-slate-500">Belum ada notifikasi.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="text-sm font-semibold text-cobalt-600 hover:underline">Lihat situs</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
            </form>
        </div>
    </header>

    <main class="min-w-0 px-5 py-8 lg:px-10 lg:py-10">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-3xl font-bold tracking-tight">@yield('heading')</h1>
            @yield('actions')
        </div>

        @include('partials.flash')

        @yield('content')
    </main>
</div>

<script>
    (function () {
        var root = document.documentElement;
        var toggle = document.getElementById('sidebar-toggle');
        var closeBtn = document.getElementById('sidebar-close');
        var backdrop = document.getElementById('sidebar-backdrop');
        var desktopQuery = window.matchMedia('(min-width: 1024px)');

        function isOpen() { return root.getAttribute('data-sidebar') === 'open'; }

        function setOpen(open, remember) {
            root.setAttribute('data-sidebar', open ? 'open' : 'closed');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            // Pilihan hanya diingat di layar lebar; di ponsel menu selalu mulai tertutup.
            if (remember && desktopQuery.matches) {
                try { localStorage.setItem('kb-sidebar', open ? 'open' : 'closed'); } catch (e) {}
            }
        }

        toggle.setAttribute('aria-expanded', isOpen() ? 'true' : 'false');

        toggle.addEventListener('click', function () { setOpen(!isOpen(), true); });
        closeBtn.addEventListener('click', function () { setOpen(false, false); toggle.focus(); });
        backdrop.addEventListener('click', function () { setOpen(false, false); });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen() && !desktopQuery.matches) {
                setOpen(false, false);
                toggle.focus();
            }
        });

        // Saat ukuran jendela melewati batas ponsel/desktop, pakai bawaan layar itu.
        desktopQuery.addEventListener('change', function (e) {
            var saved = null;
            try { saved = localStorage.getItem('kb-sidebar'); } catch (err) {}
            setOpen(e.matches && saved !== 'closed', false);
        });
    })();
</script>

<script>
    (function () {
        var button = document.getElementById('notif-toggle');
        var panel = document.getElementById('notif-panel');
        if (!button || !panel) return;

        function setOpen(open) {
            panel.hidden = !open;
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        button.addEventListener('click', function (e) {
            e.stopPropagation();
            setOpen(panel.hidden);
        });

        panel.addEventListener('click', function (e) { e.stopPropagation(); });
        document.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !panel.hidden) { setOpen(false); button.focus(); }
        });
    })();
</script>

</body>
</html>
