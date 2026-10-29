@extends('layouts.dashboard')

@section('title', 'Penulis')
@section('heading', 'Penulis')

@section('actions')
    <a href="{{ route('admin.writers.create') }}" class="btn btn-primary">Tambah penulis</a>
@endsection

@section('content')
    <p class="mb-5 max-w-2xl text-slate-600">
        Hanya akun di daftar ini yang bisa menulis artikel. Pendaftar baru muncul paling atas dan baru bisa masuk
        setelah kamu menyetujuinya. Mencabut akses langsung mengeluarkan penulis dari dasbor; artikelnya tetap ada.
    </p>

    <div class="card overflow-x-auto">
        <table class="rtable w-full min-w-[640px] text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <tr>
                    <th scope="col" class="px-5 py-3 font-semibold">Penulis</th>
                    <th scope="col" class="px-3 py-3 font-semibold">Artikel</th>
                    <th scope="col" class="px-3 py-3 font-semibold">Akses</th>
                    <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($writers as $writer)
                    <tr class="{{ $writer->pending_approval ? 'bg-amber-50/60' : '' }}">
                        <td class="rt-title px-5 py-3">
                            <div class="flex items-center gap-3">
                                @include('partials.avatar', ['user' => $writer, 'size' => 'h-10 w-10 text-sm'])
                                <div class="min-w-0">
                                    <p class="font-semibold">{{ $writer->name }}</p>
                                    <p class="text-slate-500">{{ $writer->email }}</p>
                                </div>
                            </div>
                            @if ($writer->pending_approval && $writer->application_note)
                                <p class="mt-2 max-w-md whitespace-pre-line rounded-lg bg-white px-3 py-2 text-[13px] leading-relaxed text-slate-700 ring-1 ring-amber-200">{{ $writer->application_note }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-slate-600" data-label="Artikel">{{ $writer->articles_count }}</td>
                        <td class="px-3 py-3" data-label="Akses">
                            @if ($writer->pending_approval)
                                <span class="badge bg-amber-100 text-amber-900">Menunggu persetujuan</span>
                            @elseif ($writer->is_active)
                                <span class="badge bg-emerald-100 text-emerald-800">Bisa menulis</span>
                            @else
                                <span class="badge bg-slate-200 text-slate-700">Dicabut</span>
                            @endif
                        </td>
                        <td class="rt-actions px-5 py-3">
                            <div class="flex justify-end gap-2">
                                @if ($writer->pending_approval)
                                    <form method="POST" action="{{ route('admin.writers.approve', $writer) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-primary btn-sm">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.writers.destroy', $writer) }}"
                                          onsubmit="return confirm('Tolak pendaftaran {{ addslashes($writer->name) }}? Akunnya akan dihapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Tolak</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.writers.toggle', $writer) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-ghost btn-sm">
                                            {{ $writer->is_active ? 'Cabut akses' : 'Beri akses' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.writers.edit', $writer) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                    <form method="POST" action="{{ route('admin.writers.destroy', $writer) }}"
                                          onsubmit="return confirm('Hapus akun {{ addslashes($writer->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                            Belum ada penulis.
                            <a href="{{ route('admin.writers.create') }}" class="font-semibold text-cobalt-600 hover:underline">Tambah penulis pertama</a>
                            atau tunggu pendaftaran lewat halaman daftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $writers->links() }}</div>
@endsection
