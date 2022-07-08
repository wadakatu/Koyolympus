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
    <body class="slide-show">
    <h1>COMING SOON</h1>
    <p>I'm working hard to improve my website.</p>
    <p>It should be back shortly. Thank you for your patience.</p>
    </body>
    <footer class="social-container" ontouchstart="">
        <ul class="social-icons">
            <li><a href="https://github.com/wadakatu"><i class="fa fa-github"></i></a></li>
            <li><a href="https://twitter.com/koyolympus"><i class="fa fa-twitter"></i></a></li>
            <li><a href="https://m.facebook.com/people/Koyo-Isono/100006224742543"><i class="fa fa-facebook"></i></a></li>
            <li><a href="https://www.instagram.com/wadakatu1234/?hl=ja"><i class="fa fa-instagram"></i></a></li>
        </ul>
    </footer>
@endsection
