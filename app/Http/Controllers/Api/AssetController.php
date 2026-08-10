<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;

class AssetController extends Controller
{
    // Mengambil daftar semua aset dengan relasi lengkap dalam format JSON
    public function index(Request $request)
    {
        try {
            $query = Asset::with([
                'location:id,name,building,floor,room',
                'category:id,name',
                'department:id,name',
                'user:id,name',
            ]);

            // 1. Filter Pencarian Teks (Nama, Kode, dll)
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('assets.name', 'ilike', "%{$search}%")
                        ->orWhere('asset_code', 'ilike', "%{$search}%")
                        ->orWhere('asset_number', 'ilike', "%{$search}%")
                        ->orWhere('serial_number', 'ilike', "%{$search}%")
                        ->orWhere('accurate_no', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%")
                        ->orWhereHas('location', function ($locationQuery) use ($search) {
                            $locationQuery->where('name', 'ilike', "%{$search}%");
                        });
                });
            }

            // 2. Filter berdasarkan Lokasi
            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            // 3. Filter berdasarkan Kategori
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // 4. Filter berdasarkan Rentang Tanggal Pembelian
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('purchase_date', [$request->date_from, $request->date_to]);
            }

            // 5. Filter Status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $assets = $query->orderBy('updated_at', 'desc')->paginate(50);

            return response()->json([
                'success' => true,
                'message' => 'List Data Aset Berhasil Diambil',
                'data'    => $assets
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data aset: ' . $e->getMessage()
            ], 500);
        }
    }
    // Menyimpan data aset baru dari aplikasi mobile/API
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'           => 'required|string|max:255',
                'category_id'    => 'nullable|exists:asset_categories,id',
                'location_id'    => 'nullable|exists:asset_locations,id',
                'department_id'  => 'nullable|exists:departments,id',
                'user_id'        => 'nullable|exists:users,id',
                'quantity'       => 'nullable|integer|min:1',
                'purchase_price' => 'nullable|numeric|min:0',
                'status'         => 'nullable|string',
            ]);

            // Set default value jika kosong
            $validated['quantity'] = $validated['quantity'] ?? 1;
            $validated['purchase_price'] = $validated['purchase_price'] ?? 0;
            $validated['total_price'] = $validated['quantity'] * $validated['purchase_price'];
            $validated['status'] = $validated['status'] ?? 'active';
            
            if (empty($validated['asset_number'])) {
                $validated['asset_number'] = 'AST-' . strtoupper(\Illuminate\Support\Str::random(10));
            }

            $asset = Asset::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Aset Berhasil Disimpan',
                'data'    => $asset
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan aset: ' . $e->getMessage()
            ], 500);
        }
    }
}