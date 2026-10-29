<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses rute berdasarkan peran, contoh: EnsureRole::class.':admin'
 * Akun penulis yang dinonaktifkan admin otomatis dikeluarkan pada request berikutnya.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akses akun ini sudah dinonaktifkan oleh admin.']);
        }

        abort_unless(in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
