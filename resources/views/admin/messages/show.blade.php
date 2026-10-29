@extends('layouts.dashboard')

@section('title', $message->subject)
@section('heading', $message->subject)

@section('actions')
    <a href="{{ route('admin.messages.index') }}" class="btn btn-ghost">Kembali</a>
@endsection

@section('content')
    <article class="card max-w-3xl p-6">
        <dl class="grid gap-x-6 gap-y-1 text-sm sm:grid-cols-[auto_1fr]">
            <dt class="text-slate-500">Dari</dt>
            <dd class="font-semibold">{{ $message->name }}</dd>
            <dt class="text-slate-500">Email</dt>
            <dd><a href="mailto:{{ $message->email }}" class="font-semibold text-cobalt-600 hover:underline">{{ $message->email }}</a></dd>
            <dt class="text-slate-500">Dikirim</dt>
            <dd>{{ $message->created_at->translatedFormat('j F Y, H:i') }}</dd>
        </dl>

        <div class="mt-6 whitespace-pre-line border-t border-slate-200 pt-6 leading-relaxed">{{ $message->message }}</div>

        <div class="mt-8 flex gap-3">
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}" class="btn btn-primary">Balas lewat email</a>
            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Hapus pesan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
        </div>
    </article>
@endsection
