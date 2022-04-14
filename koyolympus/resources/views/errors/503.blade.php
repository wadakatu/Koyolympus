@extends('layouts.main')

@section('title', 'Maintenance in Progress')

@push('css')
    <link href="{{ asset('/css/maintenance.css') }}" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css">
@endpush

@section('content')
    <header>
        <img src="/images/mylogo_white.png" class="logo" alt="myLogo">
    </header>
    <body>
    <h1>COMING SOON</h1>
    </body>
    <footer class="social-container">
        <ul class="social-icons">
            <li><a href="#"><i class="fa fa-github"></i></a></li>
            <li><a href="#"><i class="fa fa-twitter"></i></a></li>
            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
            <li><a href="#"><i class="fa fa-instagram"></i></a></li>
        </ul>
    </footer>
@endsection
