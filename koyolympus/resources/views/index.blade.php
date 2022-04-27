@extends('layouts.main')

@section('title', 'Koyolympus')

@push('css')
    <link href="{{ asset('/css/main.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div id="app">
        <header-component></header-component>
        <background-image-component></background-image-component>
        <footer-component></footer-component>
    </div>
    <!-- Script -->
    <script type="application/javascript" src="{{ mix('/js/app.js') }}" defer></script>
@endsection
