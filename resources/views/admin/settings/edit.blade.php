@extends('layouts.dashboard')

@section('title', 'Pengaturan')
@section('heading', 'Pengaturan Website')

@section('actions')
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-ghost">Lihat beranda</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="card max-w-2xl space-y-6 p-6">
        @csrf
        @method('PUT')

        <div>
            <label for="site_name" class="label">Nama situs</label>
            <input id="site_name" name="site_name" type="text" value="{{ old('site_name', $values['site_name']) }}" required maxlength="60" class="input">
            <p class="hint">Tampil di header, footer, judul tab browser, dan dasbor.</p>
        </div>

        <fieldset class="space-y-3">
            <legend class="label">Logo</legend>

            @if ($logoUrl)
                <div class="flex flex-wrap items-center gap-4">
                    <img src="{{ $logoUrl }}" alt="Logo saat ini" class="h-14 w-auto max-w-[200px] rounded-lg border border-slate-200 bg-white p-2 object-contain">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="remove_logo" value="1" class="h-4 w-4 rounded border-slate-300 text-cobalt-600 focus:ring-cobalt-500">
                        Hapus logo
                    </label>
                </div>
            @endif

            <div>
                <label for="logo" class="sr-only">Unggah logo</label>
                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp"
                       class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-cobalt-50 file:px-3.5 file:py-2 file:text-sm file:font-semibold file:text-cobalt-700 hover:file:bg-cobalt-100">
                <p class="hint">PNG, JPG, atau WebP, maksimal 1 MB. PNG berlatar transparan paling bagus. Tinggi sekitar 40 piksel sudah cukup. Tanpa logo, header memakai ikon huruf pertama nama situs.</p>
            </div>

            <div>
                <input type="hidden" name="logo_show_name" value="0">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="logo_show_name" value="1" @checked((string) old('logo_show_name', $values['logo_show_name']) !== '0')
                           class="h-4 w-4 rounded border-slate-300 text-cobalt-600 focus:ring-cobalt-500">
                    Tampilkan nama situs di samping logo
                </label>
                <p class="hint">Hilangkan centang kalau gambar logomu sudah memuat nama situs.</p>
            </div>
        </fieldset>

        <div>
            <label for="hero_title" class="label">Judul besar di beranda</label>
            <input id="hero_title" name="hero_title" type="text" value="{{ old('hero_title', $values['hero_title']) }}" maxlength="100" class="input">
        </div>

        <div>
            <label for="hero_subtitle" class="label">Kalimat di bawah judul</label>
            <textarea id="hero_subtitle" name="hero_subtitle" rows="2" maxlength="200" class="input">{{ old('hero_subtitle', $values['hero_subtitle']) }}</textarea>
            <p class="hint">Kosongkan judul atau kalimat ini untuk kembali ke teks bawaan.</p>
        </div>

        <div>
            <label for="site_description" class="label">Deskripsi situs untuk Google</label>
            <textarea id="site_description" name="site_description" rows="2" maxlength="160" class="input">{{ old('site_description', $values['site_description']) }}</textarea>
            <p class="hint">Satu atau dua kalimat (maksimal 160 karakter) yang bisa tampil di hasil pencarian Google. Kosongkan untuk memakai kalimat di bawah judul beranda. Artikel memakai ringkasannya sendiri.</p>
        </div>

        <button type="submit" class="btn btn-primary">Simpan pengaturan</button>
    </form>
@endsection
