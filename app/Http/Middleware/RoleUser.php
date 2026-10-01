<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors([
                'loginAkses' => 'Perlu akses login untuk halaman ini.'
            ]);
        }

        $user = Auth::user();   
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        if ($user->role === 'Admin') {
            return redirect()->route('admin.dashboard')->withErrors([
                'loginAkses' => 'Anda tidak memiliki akses untuk halaman tersebut.'
            ]);
        } elseif ($user->role === 'Kasir') {
            return redirect()->route('kasir.dashboard')->withErrors([
                'loginAkses' => 'Anda tidak memiliki akses untuk halaman tersebut.'
            ]);
        }

        Auth::logout();
        return redirect()
            ->route('login')->withErrors([
                'loginAkses' => 'Anda tidak memiliki akses untuk halaman tersebut.'
            ]);
    }
}
