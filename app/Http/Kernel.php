<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // Middleware global
    ];

    protected $middlewareGroups = [
        'web' => [
            // Middleware untuk grup web
        ],

        'api' => [
            // Middleware untuk grup API
        ],
    ];

    protected $routeMiddleware = [
        // Middleware lainnya...
        'admin' => \App\Http\Middleware\CheckAdmin::class, // Menambahkan middleware CheckAdmin
    ];
}