<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request and verify user role.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            if ($user->role === 'dokter') {
                return redirect()->route('portal.dashboard')
                    ->with('error', 'Anda tidak memiliki hak akses ke modul manajemen SDM.');
            }

            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki hak akses untuk mengakses halaman tersebut.');
        }

        return $next($request);
    }
}

