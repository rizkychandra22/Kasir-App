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
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors([
                'loginAkses' => 'Perlu akses login untuk halaman ini.'
            ]);
        }

        $user = Auth::user();   
        if ($user->role === $role) {
            return $next($request);
        }
        Auth::logout();
        return redirect()
            ->route('login')->withErrors([
                'loginAkses' => 'Anda tidak memiliki akses untuk halaman tersebut.'
            ]);
    }
}
