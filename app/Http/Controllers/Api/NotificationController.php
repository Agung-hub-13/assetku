<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // 1. Ambil daftar notifikasi (mendukung JSON)
    public function index(Request $request)
    {
        $user = $request->user(); // Menggunakan Sanctum/Passport auth

        $notifications = $user->notifications()->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    // 2. Tandai 1 notifikasi sebagai dibaca
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai dibaca',
            'redirect_url' => $notification->data['url'] ?? null
        ]);
    }

    // 3. Tandai semua sebagai dibaca
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah dibaca'
        ]);
    }

    // 4. Hapus 1 notifikasi
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dihapus'
        ]);
    }
}