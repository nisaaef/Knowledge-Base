<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600&display=swap" rel="stylesheet">

{{-- Tailwind via CDN supaya tidak perlu build. Untuk produksi, pindahkan ke Vite (npm run build). --}}
<script src="https://cdn.tailwindcss.com?plugins=typography"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"Instrument Sans"', 'system-ui', 'sans-serif'],
                    serif: ['Newsreader', 'Georgia', 'serif'],
                },
                colors: {
                    ink: '#0f1b2d',
                    mist: '#f3f5f7',
                    cobalt: {
                        50: '#eef1fe',
                        100: '#dde3fc',
                        500: '#2f4bdb',
                        600: '#2540c2',
                        700: '#1d33a0',
                    },
                },
            },
        },
    };
</script>
<style type="text/tailwindcss">
    @layer components {
        .label { @apply mb-1.5 block text-sm font-semibold text-ink; }
        .hint { @apply mt-1.5 text-xs text-slate-500; }
        .input { @apply w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-[15px] text-ink placeholder:text-slate-400 focus:border-cobalt-600 focus:outline-none focus:ring-2 focus:ring-cobalt-500/30; }
        .btn { @apply inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cobalt-600; }
        .btn-primary { @apply bg-cobalt-600 text-white hover:bg-cobalt-700; }
        .btn-ghost { @apply border border-slate-300 bg-white text-ink hover:bg-slate-50; }
        .btn-danger { @apply border border-red-200 bg-white text-red-700 hover:bg-red-50; }
        .btn-sm { @apply px-3 py-1.5 text-[13px]; }
        .badge { @apply inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold; }
        .card { @apply rounded-xl border border-slate-200 bg-white; }
    }
</style>
<style>
    /* Tabel selalu rata kiri; kolom yang perlu rata kanan memakai kelas text-right. */
    table th, table td { text-align: left; }

    /* Di HP, tabel ber-kelas "rtable" tampil sebagai kartu: judul di atas, data berlabel, tombol di bawah. */
    @media (max-width: 767.98px) {
        .rtable { min-width: 0 !important; }
        .rtable thead { display: none; }
        .rtable, .rtable tbody, .rtable tr, .rtable td { display: block; width: 100%; }
        .rtable tr { padding: .875rem 1rem; }
        .rtable td {
            display: flex; align-items: center; justify-content: space-between; gap: .75rem;
            max-width: none !important; padding: .2rem 0 !important; text-align: right !important;
        }
        .rtable td[data-label]::before {
            content: attr(data-label); flex-shrink: 0;
            color: #64748b; font-size: .8125rem; font-weight: 600; text-align: left;
        }
        .rtable td.rt-title { display: block; padding-bottom: .5rem !important; text-align: left !important; }
        .rtable td.rt-title a { white-space: normal; overflow: visible; text-overflow: clip; }
        .rtable td.rt-actions { display: block; padding-top: .6rem !important; text-align: left !important; }
        .rtable td.rt-actions > div { justify-content: flex-start; flex-wrap: wrap; }
        .rtable td[colspan] { display: block; text-align: center !important; }
    }
    .prose.reading { font-family: 'Newsreader', Georgia, serif; font-size: 1.2rem; line-height: 1.75; }
    .prose.reading h2, .prose.reading h3 { font-family: 'Instrument Sans', system-ui, sans-serif; letter-spacing: -0.015em; }
    @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
</style>
