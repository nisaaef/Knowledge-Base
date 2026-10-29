@php($editing = $article->exists)

<form method="POST"
      action="{{ $editing ? route($area.'.articles.update', $article) : route($area.'.articles.store') }}"
      enctype="multipart/form-data"
      class="grid gap-6 xl:grid-cols-3">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="card space-y-5 p-6 xl:col-span-2">
        <div>
            <label for="title" class="label">Judul</label>
            <input id="title" name="title" type="text" value="{{ old('title', $article->title) }}" required maxlength="150" class="input">
        </div>

        <div>
            <label for="excerpt" class="label">Beranda</label>
            <textarea id="excerpt" name="excerpt" rows="2" maxlength="300" class="input">{{ old('excerpt', $article->excerpt) }}</textarea>
            <p class="hint">Tampil di daftar artikel. Kosongkan untuk memakai awal isi artikel.</p>
        </div>

        <div>
            <label for="body" class="label">Isi artikel</label>
            <textarea id="body" name="body" rows="18" required class="input font-mono text-sm leading-relaxed">{{ old('body', $article->body) }}</textarea>
            <p class="hint">
                Gunakan Markdown: <code>## Subjudul</code>, <code>**tebal**</code>, <code>*miring*</code>,
                <code>[teks](https://alamat)</code>, dan baris diawali <code>-</code> untuk daftar.
            </p>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card space-y-5 p-6">
            <fieldset>
                <legend class="label">Status</legend>
                @php($currentStatus = old('status', $article->status ?: 'draft'))
                <div class="space-y-2">
                    <label class="flex items-start gap-2.5 text-sm">
                        <input type="radio" name="status" value="draft" @checked($currentStatus === 'draft') class="mt-0.5 h-4 w-4 border-slate-300 text-cobalt-600 focus:ring-cobalt-500">
                        <span><span class="font-semibold">Draf</span><br><span class="text-slate-500">Hanya terlihat olehmu dan admin.</span></span>
                    </label>
                    <label class="flex items-start gap-2.5 text-sm">
                        <input type="radio" name="status" value="published" @checked($currentStatus === 'published') class="mt-0.5 h-4 w-4 border-slate-300 text-cobalt-600 focus:ring-cobalt-500">
                        <span><span class="font-semibold">Terbit</span><br><span class="text-slate-500">Tampil untuk semua pengunjung.</span></span>
                    </label>
                </div>
            </fieldset>

            <div>
                <label for="category_id" class="label">Kategori</label>
                <select id="category_id" name="category_id" class="input">
                    <option value="">Tanpa kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $article->category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <label for="new_category" class="mt-3 block text-sm font-medium text-slate-700">Atau buat kategori baru</label>
                <input id="new_category" name="new_category" type="text" value="{{ old('new_category') }}" maxlength="60"
                       class="input mt-1.5" placeholder="Contoh: Keamanan akun">
                <p class="hint">Kalau diisi, kategori ini dipakai menggantikan pilihan di atas. Nama yang sudah ada tidak dibuat ganda.</p>

                <script>
                    (() => {
                        const select = document.getElementById('category_id');
                        const input = document.getElementById('new_category');
                        const sync = () => { select.disabled = input.value.trim() !== ''; };
                        input.addEventListener('input', sync);
                        sync();
                    })();
                </script>
            </div>
        </div>

        <div class="card space-y-3 p-6">
            <label for="cover" class="label">Gambar sampul</label>
            @if ($editing && $article->coverUrl())
                <img src="{{ $article->coverUrl() }}" alt="Sampul saat ini" class="aspect-[16/9] w-full rounded-lg object-cover">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remove_cover" value="1" class="h-4 w-4 rounded border-slate-300 text-cobalt-600 focus:ring-cobalt-500">
                    Hapus gambar sampul
                </label>
            @endif
            <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp"
                   class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-cobalt-50 file:px-3.5 file:py-2 file:text-sm file:font-semibold file:text-cobalt-700 hover:file:bg-cobalt-100">
            <p class="hint">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Simpan perubahan' : 'Simpan artikel' }}</button>
            <a href="{{ route($area.'.articles.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </div>
</form>
