@extends('layouts.dashboard')

@section('title', 'Pesan')
@section('heading', 'Pesan')

@section('content')
    <div class="card">
        <ul class="divide-y divide-slate-100">
            @forelse ($messages as $message)
                <li>
                    <a href="{{ route('admin.messages.show', $message) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50">
                        <span class="min-w-0">
                            <span class="block truncate {{ $message->isUnread() ? 'font-bold' : 'font-medium text-slate-700' }}">{{ $message->subject }}</span>
                            <span class="block truncate text-sm text-slate-500">{{ $message->name }} &lt;{{ $message->email }}&gt;</span>
                        </span>
                        <span class="flex shrink-0 items-center gap-3">
                            @if ($message->isUnread())
                                <span class="badge bg-amber-100 text-amber-900">Baru</span>
                            @endif
                            <span class="text-xs text-slate-500">{{ $message->created_at->diffForHumans() }}</span>
                        </span>
                    </a>
                </li>
            @empty
                <li class="px-5 py-14 text-center text-slate-500">Belum ada pesan masuk. Pesan dari halaman kontak akan muncul di sini.</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-6">{{ $messages->links() }}</div>
@endsection
