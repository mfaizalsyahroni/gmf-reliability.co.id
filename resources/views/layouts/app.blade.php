<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Reliability Dashboard') }}</title>

    <!-- Icon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />


    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 flex flex-col">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-[1440px] mx-auto py-3 px-2 sm:px-3 lg:px-4">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('alert');
                document.getElementById('operator-dropdown').addEventListener('change', function() {
                    console.log('Operator changed');
                    const operator = this.value;
                    const aircraftTypeDropdown = document.getElementById('aircraft-type-dropdown');

                    // Kosongkan dropdown AC Type
                    aircraftTypeDropdown.innerHTML = '<option value="">Select Aircraft Type</option>';

                    if (operator) {
                        // Kirim permintaan AJAX
                        fetch(`/get-aircraft-types?operator=${operator}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log(data); // Debugging
                                data.forEach(type => {
                                    const option = document.createElement('option');
                                    option.value = type.ACType;
                                    option.textContent = type.ACType;
                                    aircraftTypeDropdown.appendChild(option);
                                });
                            })
                            .catch(error => console.error('Error fetching aircraft types:', error));
                    }
                });
            });
        </script>

    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('loaded')
    })
</script>


</html>
