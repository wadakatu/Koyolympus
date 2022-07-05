<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#000000">

    <meta name=”twitter:card” content=”summary_large_image“/>
    <meta property="og:type" content="website">
    <meta name="twitter:creator" content="@ktwdwdwd"/>
    <meta property="og:url" content="https://koyolympus.gallery"/>
    <meta property="og:title" content="Koyolympus"/>
    <meta property="og:description" content="Tokyo Photographer Koyo Isono with Olympus Camera."/>
    <meta property="og:image" content="https://koyolympus.gallery/images/mylogo_black.jpg"/>

    <title>@yield('title', 'Koyolympus')</title>
    <meta name="author" content="Koyo Isono">
    <meta name="description" content="Enthusiastic OLYMPUS photographer in Tokyo, Japan.
    Please feel free to enjoy visiting my website and seeing my photography.
    I hope you are going to find out something touch your heart in this site.">

    <link rel="stylesheet" href="https://unpkg.com/modern-css-reset/dist/reset.min.css" />
    @stack('css')
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-M1ZF3820H9"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', 'G-M1ZF3820H9');
    </script>
    <script src="https://kit.fontawesome.com/ead0100fa8.js" crossorigin="anonymous"></script>
</head>
<body>
@yield('content')
</body>
</html>
