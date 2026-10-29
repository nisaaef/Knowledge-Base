@php($editing = $writer->exists)

<form method="POST"
      action="{{ $editing ? route('admin.writers.update', $writer) : route('admin.writers.store') }}"
      class="card max-w-xl space-y-5 p-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div>
        <label for="name" class="label">Nama</label>
        <input id="name" name="name" type="text" value="{{ old('name', $writer->name) }}" required maxlength="100" class="input">
    </div>

    <div>
        <label for="email" class="label">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $writer->email) }}" required maxlength="150" class="input">
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="password" class="label">{{ $editing ? 'Kata sandi baru' : 'Kata sandi' }}</label>
            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" @required(! $editing) class="input">
            <p class="hint">{{ $editing ? 'Kosongkan jika tidak ingin mengubah.' : 'Minimal 8 karakter.' }}</p>
        </div>
        <div>
            <label for="password_confirmation" class="label">Ulangi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="input">
        </div>
    </div>

    <label class="flex items-start gap-2.5 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing ? $writer->is_active : true))
               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-cobalt-600 focus:ring-cobalt-500">
        <span>
            <span class="font-semibold">Boleh menulis artikel</span><br>
            <span class="text-slate-500">Hilangkan centang untuk mencabut akses tanpa menghapus akun.</span>
        </span>
    </label>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">{{ $editing ? 'Simpan perubahan' : 'Tambah penulis' }}</button>
        <a href="{{ route('admin.writers.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
