<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OwnerAuth
{
    /**
     * Hanya role 'owner' yang boleh akses /owner/* routes.
     * Middleware ini sudah hanya di-apply ke owner route group.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        if (!$user->isOwner()) {
            return redirect()->route('admin.login')
                ->with('error', 'Akses ditolak. Halaman ini hanya untuk Owner.');
        }

        return $next($request);
    }
}
