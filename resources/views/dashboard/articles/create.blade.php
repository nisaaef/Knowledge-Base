@extends('layouts.dashboard')

@section('title', 'Artikel baru')
@section('heading', 'Artikel baru')

@section('content')
    @include('dashboard.articles._form')
@endsection
