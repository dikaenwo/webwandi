<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class KasirAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        if (!$user->isKasir()) {
            // Non-kasir users trying to access /kasir → redirect to their own dashboard
            if ($user->isOwner()) {
                return redirect()->route('owner.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
