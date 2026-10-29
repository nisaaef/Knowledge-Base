@extends('layouts.app')

@section('title', 'Tentang')
@section('description', 'Apa itu knowledge base ini dan siapa yang menulisnya.')

@section('content')
    <div class="mx-auto max-w-3xl px-5 py-14">
        <h1 class="text-4xl font-bold leading-tight tracking-tight">Tentang {{ config('app.name') }}</h1>
        <p class="mt-4 text-lg leading-relaxed text-slate-600">
            {{-- Ganti teks di halaman ini sesuai organisasi atau topik kamu. --}}
            Tempat kami mengumpulkan panduan, tutorial, dan jawaban pertanyaan supaya kamu tidak perlu bertanya dua kali.
        </p>

        <div class="mt-12 space-y-10">
            <section>
                <h2 class="text-xl font-bold tracking-tight">Apa yang ada di sini</h2>
                <p class="mt-2 leading-relaxed text-slate-700">
                    Setiap artikel ditulis untuk menjawab satu pertanyaan secara lengkap. Gunakan kolom pencarian di
                    <a href="{{ route('home') }}" class="font-semibold text-cobalt-600 hover:underline">beranda</a>
                    atau telusuri lewat kategori.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold tracking-tight">Siapa yang menulis</h2>
                <p class="mt-2 leading-relaxed text-slate-700">
                    Artikel ditulis oleh penulis yang ditunjuk admin. Nama penulis tercantum di setiap artikel,
                    dan admin memeriksa isi yang terbit.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold tracking-tight">Ingin ikut menulis atau menemukan kesalahan?</h2>
                <p class="mt-2 leading-relaxed text-slate-700">
                    Kirim pesan lewat halaman kontak. Sebutkan topik yang ingin kamu tulis, atau artikel mana yang perlu diperbaiki.
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                    @if (config('knowledgebase.registration_open'))
                        <a href="{{ route('register') }}" class="btn btn-primary">Daftar jadi penulis</a>
                    @endif
                    <a href="{{ route('contact') }}" class="btn {{ config('knowledgebase.registration_open') ? 'btn-ghost' : 'btn-primary' }}">Hubungi kami</a>
                </div>
            </section>
        </div>
    </div>
@endsection
