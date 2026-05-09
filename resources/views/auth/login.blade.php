<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Figtree', sans-serif;
        }

        body {
            background: url('/images/bglogin.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* ── STATE DEFAULT: samar/glassmorphism ── */
        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
            transition: background 0.4s ease, transform 0.4s ease;
        }

        .login-card h4,
        .login-card .form-label,
        .login-card .form-check-label {
            color: #ffffff !important;
            transition: color 0.4s ease;
        }

        .login-card .form-control {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: #ffffff;
            transition: background 0.4s ease, color 0.4s ease, border-color 0.4s ease;
        }

        .login-card .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .login-card .form-check-input {
            border-color: rgba(255, 255, 255, 0.5);
            background-color: rgba(255, 255, 255, 0.15);
        }

        .login-card .link-forgot {
            color: rgba(255, 255, 255, 0.8);
            transition: color 0.4s ease;
        }

        /* ── STATE FOCUS: putih solid saat form diisi ── */
        .login-card:focus-within {
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-5px);
        }

        .login-card:focus-within h4,
        .login-card:focus-within .form-label,
        .login-card:focus-within .form-check-label {
            color: #1f2937 !important;
        }

        .login-card:focus-within .form-control {
            background: #ffffff;
            border-color: #d1d5db;
            color: #1f2937;
        }

        .login-card:focus-within .form-control::placeholder {
            color: #9ca3af;
        }

        .login-card:focus-within .form-check-input {
            border-color: #6366f1;
            background-color: #ffffff;
        }

        .login-card:focus-within .link-forgot {
            color: #6b7280;
        }

        .login-card .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .btn-login {
            background-color: #6366f1;
            border: none;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-login:hover {
            background-color: #4f46e5;
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">

        {{-- Logo --}}
        <div class="mb-4">
            <img src="{{ asset('images/gmfwhite.png') }}" alt="GMF Logo" style="height: 80px; width: auto;">
        </div>

        {{-- Card --}}
        <div class="login-card p-4 p-sm-5">

            <h4 class="text-center fw-bold mb-4">{{ __('Sign In') }}</h4>

            @if (session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif

            @if (session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">{{ __('Email') }}</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="you@example.com"
                        required
                        autofocus
                        autocomplete="username"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">{{ __('Password') }}</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="mb-3 form-check">
                    <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
                    <label for="remember_me" class="form-check-label">{{ __('Remember me') }}</label>
                </div>

                {{-- Actions --}}
                <div class="d-flex align-items-center justify-content-between mt-4">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-underline link-forgot small">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <button type="submit" class="btn btn-login px-4">
                        {{ __('Log in') }}
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>