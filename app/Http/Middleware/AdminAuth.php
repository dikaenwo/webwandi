<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Hanya role 'admin' yang boleh akses protected /admin/* routes.
     *
     * Bug sebelumnya: cek $request->is('admin/*') tidak match /admin (root),
     * sehingga kasir/owner bisa akses /admin langsung.
     *
     * Fix: tidak perlu cek path — middleware ini sudah hanya di-apply ke
     * protected admin route group, sehingga cukup cek role saja.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::guard('admin')->user();

        if (!$user->isAdmin()) {
            return redirect()->route('admin.login')
                ->with('error', 'Akses ditolak. Halaman ini hanya untuk Admin.');
        }

        return $next($request);
    }
}
