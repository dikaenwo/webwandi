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

        // Kasir/Owner yang mencoba akses admin panel → redirect ke login
        // BUKAN redirect ke dashboard mereka — ini mencegah tab admin tiba-tiba
        // menampilkan halaman kasir/owner ketika sesi berubah di tab lain.
        if (!$user->isAdmin() && $request->is('admin/*') && !$request->is('admin/logout')) {
            return redirect()->route('admin.login')
                ->with('error', 'Akses ditolak. Silakan login sebagai Admin.');
        }

        return $next($request);
    }
}
