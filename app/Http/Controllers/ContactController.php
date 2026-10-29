<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function create()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        // Kolom "website" disembunyikan dari manusia; bila terisi, itu bot.
        if (! $request->filled('website')) {
            $message = ContactMessage::create($data);

            User::notifyAdmins(
                'Pesan baru dari '.$message->name,
                $message->subject,
                route('admin.messages.show', $message)
            );
        }

        return redirect()->route('contact')
            ->with('status', 'Pesan terkirim. Admin akan membalas lewat email yang kamu isi.');
    }
}
