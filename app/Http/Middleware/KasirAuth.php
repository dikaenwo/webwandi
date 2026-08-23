<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class KasirAuth
{
    /**
     * Hanya role 'kasir' yang boleh akses /kasir/* routes.
     * Middleware ini sudah hanya di-apply ke kasir route group.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        if (!$user->isKasir()) {
            return redirect()->route('admin.login')
                ->with('error', 'Akses ditolak. Halaman ini hanya untuk Kasir.');
        }

        return $next($request);
    }
}
