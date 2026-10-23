<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()?->active || !$request->user()->hasActiveOperationalProfile()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['identity' => 'La cuenta o el perfil está desactivado o no existe.']);
        }
        return $next($request);
    }
}
