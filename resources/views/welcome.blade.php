{{-- welcome.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Reliability Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased h-full bg-no-repeat bg-cover" style="background-image: url('/images/bglogin.jpg');">
    <header class="max-w-[1440px] mx-auto p-4">
        @if (Route::has('login'))
            <nav class="flex justify-end space-x-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="rounded-md px-4 py-2 text-white bg-blue-600 hover:bg-blue-700 transition duration-300">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Register</a>
                    @endif
                @endauth
            </nav>
        @endif
    </header>

    <main class="py-12">
        <div class="max-w-[1440px] mx-auto text-center">
            <h1 class="text-4xl font-bold text-white mb-4">Reliability Report</h1>
            <h3 class="text-lg text-white mb-8">Login for full access and great experience</h3>
        </div>

        <div class="max-w-[1440px] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-lg relative">
                <div class="relative h-96">
                    <!-- Power BI embed -->
                    <img src="{{ asset('images/powerbi.png') }}" class="w-full h-full object-cover rounded-lg" alt="Power BI Image">
                    
                    <!-- Overlay for blur effect -->
                    <div class="absolute inset-0 bg-white bg-opacity-50 backdrop-blur-sm pointer-events-none"></div>

                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div class="opacity-50 text-gray-500 font-bold text-8xl">Login</div>
                        <div class="opacity-50 text-gray-500 font-bold text-2xl mt-2">For Full Access</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center p-4 mt-8">
        <p class="text-white">© 2024 Reliability Dashboard. All rights reserved.</p>
    </footer>
</body>
</html>