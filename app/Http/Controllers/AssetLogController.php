<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use Illuminate\Http\Request;

class AssetLogController extends Controller
{
    /**
     * Menampilkan daftar semua log aktivitas aset
     */
    public function index(Request $request)
    {
        $query = AssetLog::with(['asset', 'user'])->latest('created_at');

        // Filter berdasarkan Aset
        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        // Filter berdasarkan Tipe Action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Pencarian Kata Kunci
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                    ->orWhereHas('asset', function ($assetQuery) use ($search) {
                        $assetQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('asset_code', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $logs = $query->paginate(15)->withQueryString();
        $assets = Asset::select('id', 'name', 'asset_code')->orderBy('name')->get();

        return view('admin.asset_logs.index', compact('logs', 'assets'));
    }

    /**
     * Helper Static Method untuk mencatat Log dari Controller Mana Saja
     * 
     * Tipe Action Rekomendasi:
     * - 'create' / 'update' / 'delete'
     * - 'borrow' (Peminjaman) / 'return' (Pengembalian)
     * - 'maintenance' (Perawatan/Perbaikan)
     * - 'transfer' / 'mutation' (Mutasi Lokasi/Penerima)
     */
    public static function log($assetId, string $action, string $description, ?array $properties = null)
    {
        return AssetLog::create([
            'asset_id'    => $assetId,
            'user_id'     => auth()->id(),
            'action'      => $action,
            'description' => $description,
            'properties'  => $properties,
            'created_at'  => now(),
        ]);
    }
}