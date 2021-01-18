<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device=width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title> {{ config('app.name', 'Koyolympus') }}</title>

{{--  <!-- Styles -->--}}
{{--  <link href="{{ mix('/css/app.css') }}" rel="stylesheet">--}}
  <link href="{{ asset('/css/index.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="container">

  </div>
<!-- Script -->
<script src="{{ mix('/js/app.js') }}" defer></script>
</body>
</html>
