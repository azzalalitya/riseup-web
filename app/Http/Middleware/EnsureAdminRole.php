<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Hanya izinkan sesi dengan role 'admin'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('auth_role') !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
