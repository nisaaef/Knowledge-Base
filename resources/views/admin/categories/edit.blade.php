@extends('layouts.dashboard')

@section('title', 'Ubah kategori')
@section('heading', 'Ubah kategori')

@section('content')
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="card max-w-xl space-y-5 p-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="label">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required maxlength="60" class="input">
        </div>

        <div>
            <label for="description" class="label">Deskripsi</label>
            <input id="description" name="description" type="text" value="{{ old('description', $category->description) }}" maxlength="200" class="input">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
@endsection
