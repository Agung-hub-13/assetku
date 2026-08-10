<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset; // Pastikan Model Asset Anda sudah ada

class AssetController extends Controller
{
    public function index()
    {
        // Mengambil semua data aset dari database
        $assets = Asset::all();

        return response()->json([
            'success' => true,
            'message' => 'List Data Aset',
            'data' => $assets
        ], 200);
    }

    public function store(Request $request)
    {
        // Menyimpan data aset baru ke database
        $asset = Asset::create([
            'code' => $request->code,
            'name' => $request->name,
            'location' => $request->location,
            'status' => $request->status ?? 'Aktif',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Aset Berhasil Disimpan',
            'data' => $asset
        ], 201);
    }
}