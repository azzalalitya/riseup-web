<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Hanya izinkan sesi dengan role 'user' (student).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('auth_role') !== 'user') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
