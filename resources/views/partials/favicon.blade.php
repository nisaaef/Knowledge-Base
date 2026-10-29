{{-- Ikon tab browser: logo yang diunggah di Pengaturan, atau huruf pertama nama situs bila belum ada logo. --}}
@php $faviconUrl = \App\Models\Setting::logoUrl(); @endphp
@if ($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@else
    @php
        $faviconLetter = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(\App\Models\Setting::siteName(), 0, 1));
        $faviconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="#2540c2"/><text x="32" y="45" font-size="38" font-weight="700" font-family="Arial,sans-serif" text-anchor="middle" fill="#ffffff">'.e($faviconLetter).'</text></svg>';
    @endphp
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,{{ rawurlencode($faviconSvg) }}">
@endif
