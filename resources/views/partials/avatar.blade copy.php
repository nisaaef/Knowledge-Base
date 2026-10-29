{{-- Pakai: @include('partials.avatar', ['user' => $user, 'size' => 'h-14 w-14 text-lg']) --}}
@php($avatarUrl = $user->avatarUrl())
@if ($avatarUrl)
    <img src="{{ $avatarUrl }}" alt="" loading="lazy" class="{{ $size ?? 'h-10 w-10 text-sm' }} shrink-0 rounded-full object-cover">
@else
    <span class="{{ $size ?? 'h-10 w-10 text-sm' }} grid shrink-0 place-items-center rounded-full bg-cobalt-100 font-bold text-cobalt-700" aria-hidden="true">{{ $user->initials() }}</span>
@endif
