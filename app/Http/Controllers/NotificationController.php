<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Tandai satu notifikasi sudah dibaca, lalu buka halaman tujuannya. */
    public function open(Request $request, string $id)
    {
        // Hanya notifikasi milik pengguna sendiri yang bisa dibuka.
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
