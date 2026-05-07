<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use app\Http\Controllers\UserController;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah pengguna terautentikasi
        if (Auth::check()) {
            // Cek apakah posisi pengguna adalah 'Admin'
            if (Auth::user()->Position === 'Admin') {
                return $next($request);
            }
        }

        // Jika bukan admin, kembalikan respons 404 Not Found
        abort(404);
    }
}