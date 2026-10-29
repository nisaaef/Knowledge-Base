<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WriterController extends Controller
{
    private function ensureWriter(User $writer): void
    {
        abort_unless($writer->isWriter(), 404);
    }

    /** Pendaftar melihat ini begitu pertama kali masuk. */
    private function notifyApproved(User $writer): void
    {
        $writer->pushNotice(
            'Akunmu sudah disetujui',
            'Kamu sekarang bisa menulis artikel. Mulai dari menu Artikel saya.',
            route('penulis.dashboard')
        );
    }

    public function index()
    {
        $writers = User::where('role', User::ROLE_WRITER)
            ->withCount('articles')
            ->orderByDesc('pending_approval')
            ->latest()
            ->paginate(15);

        return view('admin.writers.index', compact('writers'));
    }

    public function create()
    {
        return view('admin.writers.create', ['writer' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_WRITER,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.writers.index')->with('status', 'Penulis ditambahkan.');
    }

    public function edit(User $writer)
    {
        $this->ensureWriter($writer);

        return view('admin.writers.edit', compact('writer'));
    }

    public function update(Request $request, User $writer)
    {
        $this->ensureWriter($writer);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($writer->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $wasPending = (bool) $writer->pending_approval;

        $writer->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $request->boolean('is_active'),
        ]);

        // Memberi akses lewat formulir ubah juga dianggap menyetujui pendaftaran.
        if ($writer->is_active) {
            $writer->pending_approval = false;
        }

        if (! empty($data['password'])) {
            $writer->password = $data['password'];
        }

        $writer->save();

        if ($wasPending && $writer->is_active) {
            $this->notifyApproved($writer);
        }

        return redirect()->route('admin.writers.index')->with('status', 'Data penulis disimpan.');
    }

    public function approve(User $writer)
    {
        $this->ensureWriter($writer);

        $writer->update(['is_active' => true, 'pending_approval' => false]);
        $this->notifyApproved($writer);

        return back()->with('status', $writer->name.' disetujui dan kini bisa masuk.');
    }

    public function toggle(User $writer)
    {
        $this->ensureWriter($writer);

        // Memberi akses kepada pendaftar sama artinya dengan menyetujuinya.
        $wasPending = (bool) $writer->pending_approval;

        $writer->update([
            'is_active' => ! $writer->is_active,
            'pending_approval' => false,
        ]);

        if ($wasPending && $writer->is_active) {
            $this->notifyApproved($writer);
        }

        return back()->with('status', $writer->is_active
            ? $writer->name.' kini bisa menulis lagi.'
            : 'Akses '.$writer->name.' dicabut.');
    }

    public function destroy(User $writer)
    {
        $this->ensureWriter($writer);

        if ($writer->articles()->exists()) {
            return back()->withErrors([
                'writer' => 'Penulis yang masih punya artikel tidak bisa dihapus. Cabut aksesnya saja, atau hapus artikelnya lebih dulu.',
            ]);
        }

        $writer->delete();

        return redirect()->route('admin.writers.index')->with('status', 'Penulis dihapus.');
    }
}
