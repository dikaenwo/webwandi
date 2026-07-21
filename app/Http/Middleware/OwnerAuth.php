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

        if (!$user->isOwner()) {
            // Admin yang mencoba akses /owner → redirect ke admin dashboard
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
