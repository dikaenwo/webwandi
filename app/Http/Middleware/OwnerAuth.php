<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OwnerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Non-owner yang mencoba akses /owner/* → redirect ke login
        // (bukan ke admin dashboard, agar tidak cross-contaminate antar tab)
        if (!$user->isOwner()) {
            return redirect()->route('admin.login')
                ->with('error', 'Akses ditolak. Halaman ini hanya untuk Owner.');
        }

        return $next($request);
    }
}
