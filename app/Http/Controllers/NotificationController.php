<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar semua notifikasi milik user yang sedang login dengan paginasi.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Mengambil notifikasi terbaru secara teratur
        $notifications = $user->notifications()
            ->latest()
            ->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Tandai 1 notifikasi sebagai dibaca lalu redirect ke link tujuan (jika ada).
     */
    public function markAsRead($id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $notification = $user->notifications()->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        // Ambil URL tujuan dari payload data notifikasi
        $redirectUrl = $notification->data['url'] ?? route('admin.notifications.index');

        return redirect($redirectUrl);
    }

    /**
     * Tandai semua notifikasi milik user sebagai dibaca.
     */
    public function markAllAsRead()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    /**
     * Menghapus 1 notifikasi.
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}