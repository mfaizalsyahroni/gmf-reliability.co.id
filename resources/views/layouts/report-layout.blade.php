<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Vite — samain dengan yang ada di layouts/app.blade.php --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Bootstrap — kalau project pakai Bootstrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    {{-- HTMX --}}
    <script src="https://unpkg.com/htmx.org@2.0.0"></script>
    <script>
        document.addEventListener('htmx:configRequest', function(e) {
            e.detail.headers['X-CSRF-TOKEN'] =
                document.querySelector('meta[name="csrf-token"]').content;
        });
    </script>
</head>
<body>

    {{-- Navbar — copy dari layouts/app.blade.php kamu --}}
    @include('layouts.navigation')

    {{-- Konten --}}
    @yield('content')

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>