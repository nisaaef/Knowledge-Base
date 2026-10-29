<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    private function ensureOpen(): void
    {
        abort_unless(config('knowledgebase.registration_open'), 404);
    }

    public function create()
    {
        $this->ensureOpen();

        return view('auth.register');
    }

    public function store(Request $request)
    {
        $this->ensureOpen();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'application_note' => ['required', 'string', 'min:20', 'max:1000'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Coba masuk, atau pakai email lain.',
            'application_note.required' => 'Ceritakan singkat topik yang ingin kamu tulis.',
            'application_note.min' => 'Tulis minimal 20 karakter supaya admin bisa menilai pendaftaranmu.',
        ]);

        $message = 'Pendaftaran terkirim. Kamu bisa masuk setelah admin menyetujui akunmu.';

        // Kolom "website" disembunyikan dari manusia; bila terisi, itu bot.
        if ($request->filled('website')) {
            return redirect()->route('login')->with('status', $message);
        }

        $applicant = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_WRITER,
            'is_active' => false,
            'pending_approval' => true,
            'application_note' => $data['application_note'],
        ]);

        User::notifyAdmins(
            'Pendaftar penulis baru',
            $applicant->name.' menunggu persetujuanmu',
            route('admin.writers.index')
        );

        return redirect()->route('login')->with('status', $message);
    }
}
