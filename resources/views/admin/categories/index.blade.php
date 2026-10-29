@extends('layouts.dashboard')

@section('title', 'Kategori')
@section('heading', 'Kategori')

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="card h-fit space-y-5 p-6">
            @csrf
            <h2 class="font-bold">Kategori baru</h2>
            <div>
                <label for="name" class="label">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="60" class="input">
            </div>
            <div>
                <label for="description" class="label">Deskripsi</label>
                <input id="description" name="description" type="text" value="{{ old('description') }}" maxlength="200" class="input">
                <p class="hint">Opsional. Satu kalimat singkat.</p>
            </div>
            <button type="submit" class="btn btn-primary">Tambah kategori</button>
        </form>

        <div class="card overflow-x-auto xl:col-span-2">
            <table class="rtable w-full min-w-[480px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Kategori</th>
                        <th scope="col" class="px-3 py-3 font-semibold">Artikel</th>
                        <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="rt-title px-5 py-3">
                                <p class="font-semibold">{{ $category->name }}</p>
                                @if ($category->description)
                                    <p class="text-slate-500">{{ $category->description }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-slate-600" data-label="Artikel">{{ $category->articles_count }}</td>
                            <td class="rt-actions px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                          onsubmit="return confirm('Hapus kategori {{ addslashes($category->name) }}? Artikelnya tetap ada, hanya menjadi tanpa kategori.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-12 text-center text-slate-500">Belum ada kategori. Tambahkan lewat formulir di samping.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
