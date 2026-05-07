{{-- Ganti Logo Laravel Login-Register di guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased h-full bg-no-repeat bg-cover" style="background-image: url('/images/bglogin.jpg');">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <img class="h-24 w-auto items-center"
                        src="{{ asset('images/gmfwhite.png') }}"
                        alt="logo">
                {{-- <h2 class="mt-5 text-center text-2xl font-bold leading-9 tracking-tight text-white">{{ $header }}</h2> --}}
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white bg-opacity-80 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>