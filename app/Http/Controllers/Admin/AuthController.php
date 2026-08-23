<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Jika sudah login DAN mengakses login page secara langsung (bukan karena di-redirect oleh middleware),
        // redirect ke dashboard sesuai role.
        // PENTING: jika user diredirect ke sini karena role salah (misal kasir buka /owner),
        // kita logout sesinya dan tampilkan form login dengan pesan — bukan redirect ke kasir.dashboard.
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();

            // Cek apakah ada pesan error (berasal dari role middleware redirect)
            // Jika ada, logout dan tampilkan form login dengan pesan tersebut
            if (session()->has('error')) {
                Auth::guard('admin')->logout();
                session()->invalidate();
                session()->regenerateToken();
                return view('admin.login');
            }

            // Tidak ada error → user memang mengakses /admin/login langsung → redirect ke dashboard
            if ($user->isOwner()) {
                return redirect()->route('owner.dashboard');
            }
            if ($user->isKasir()) {
                return redirect()->route('kasir.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::guard('admin')->user();

            // Redirect based on role
            if ($user->isOwner()) {
                return redirect()->intended(route('owner.dashboard'));
            }

            if ($user->isKasir()) {
                return redirect()->intended(route('kasir.dashboard'));
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
