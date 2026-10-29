@extends('layouts.dashboard')

@section('title', 'Ubah artikel')
@section('heading', 'Ubah artikel')

@section('actions')
    <a href="{{ route('articles.show', $article) }}" target="_blank" rel="noopener" class="btn btn-ghost">Lihat artikel</a>
@endsection

@section('content')
    @include('dashboard.articles._form')
@endsection
