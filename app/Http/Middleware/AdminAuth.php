<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::guard('admin')->user();

        // Kasir users should not access admin panel
        if ($user->isKasir() && $request->is('admin/*') && !$request->is('admin/logout')) {
            return redirect()->route('kasir.dashboard');
        }

        // Owner users should not access admin panel (except logout)
        if ($user->isOwner() && $request->is('admin/*') && !$request->is('admin/logout')) {
            return redirect()->route('owner.dashboard');
        }

        return $next($request);
    }
}
