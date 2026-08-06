<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetReportController extends Controller
{
    /**
     * Ringkasan Utama Aset (Dashboard Laporan)
     */
    public function index(Request $request): View
    {
        // Ubah kolom acuan nilai ke 'purchase_price' (atau kolom yang dipakai di halaman asset)
        $columnName = 'purchase_price'; 

        // Jika quantity berpengaruh (misal: harga beli * quantity), ubah cara hitung totalValue:
        // $totalValue = Asset::sum(\DB::raw('purchase_price * quantity'));
        
        // Atau jika cukup sum langsung dari purchase_price:
        $totalValue  = Asset::sum($columnName); 
        $totalAssets = Asset::count();

        // Sebaran Status Aset
        $reportByStatus = Asset::select('status', \DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Laporan Per Kategori (Gunakan columnName yang sama agar sinkron)
        $reportByCategory = AssetCategory::withCount('assets')
            ->withSum('assets as total_price_sum', $columnName)
            ->get();

        // Laporan Per Lokasi
        $reportByLocation = AssetLocation::withCount('assets')
            ->withSum('assets as total_price_sum', $columnName)
            ->get();

        return view('admin.asset_reports.index', compact(
            'totalAssets',
            'totalValue', // Variabel ini yang dikirim ke view
            'reportByStatus',
            'reportByCategory',
            'reportByLocation'
        ));
    }

    /**
     * Laporan Riwayat Peminjaman Aset
     */
    public function loanReport(Request $request): View
    {
        $query = AssetLoan::with(['user', 'asset']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans = $query->latest()->paginate(10)->withQueryString();

        return view('admin.asset_reports.loan', compact('loans'));
    }

    /**
     * Laporan Maintenance & Perbaikan Aset
     */
    public function maintenanceReport(Request $request): View
    {
        $query = AssetMaintenance::with('asset');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        // Menggunakan 'total_cost' sesuai struktur tabel asset_maintenances
        $costColumn = 'total_cost';

        $totalCost = (clone $query)->sum($costColumn);

        $maintenances = $query->latest()->paginate(10)->withQueryString();

        return view('admin.asset_reports.maintenance', compact('maintenances', 'totalCost'));
    }
}
