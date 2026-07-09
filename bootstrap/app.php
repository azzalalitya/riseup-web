<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        Laravel\Socialite\SocialiteServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'user'  => \App\Http\Middleware\EnsureUserRole::class,
            'admin' => \App\Http\Middleware\EnsureAdminRole::class,
        ]);

        // Railway/hosting berjalan di belakang reverse proxy (https).
        // Trust proxy agar url() dan redirect Google OAuth memakai https.
        $middleware->trustProxies(at: '*');

        // Midtrans webhook datang dari server Midtrans, bukan browser user.
        // Tidak boleh dikenai verifikasi CSRF token.
        $middleware->validateCsrfTokens(except: [
            'midtrans/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
