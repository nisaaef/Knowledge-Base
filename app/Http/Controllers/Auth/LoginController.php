<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Pendaftar yang belum disetujui diberi tahu dengan jelas (hanya jika kata sandinya benar).
        $candidate = User::where('email', $credentials['email'])->first();
        if ($candidate && $candidate->pending_approval && Hash::check($credentials['password'], $candidate->password)) {
            return back()
                ->withErrors(['email' => 'Akunmu masih menunggu persetujuan admin. Kamu bisa masuk setelah disetujui.'])
                ->onlyInput('email');
        }

        // Hanya akun aktif yang boleh masuk.
        $attempt = Auth::attempt(
            $credentials + ['is_active' => true],
            $request->boolean('remember')
        );

        if (! $attempt) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah, atau akses akun ini sudah dinonaktifkan.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
