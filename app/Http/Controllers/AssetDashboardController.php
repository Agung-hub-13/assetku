<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssetDashboardController extends Controller
{
    /**
     * Menampilkan Halaman Utama Dashboard (Blade View)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Deteksi jika admin mengakses dashboard desktop lewat gawai mobile
        $userAgent = $request->header('User-Agent');
        $isMobileDevice = preg_match('/(android|bb\d+|meego).+mobile|iphone|ipad/i', $userAgent);

        if ($isMobileDevice) {
            return redirect()->route('mobile.dashboard');
        }

        // Proteksi Hak Akses
        if (!$user->hasRole('super_admin') && !$user->hasPermissionTo('access-desktop')) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi Anda diakhiri karena akun Anda tidak memiliki izin akses ke Dashboard Desktop.'
            ]);
        }

        // 1. Ambil data KPI Utama
        $locations = AssetLocation::all();
        $totalAssetCount = Asset::count();
        $totalInvestmentValue = Asset::sum('total_price');
        $totalBookValueHabis = Asset::where('book_value', 0)->count();

        // 2. Data Grafik Chart Lokasi (Kuantitas Aset)
        $rawLokasiData = Asset::select('asset_locations.name as lokasi_name', DB::raw('count(assets.id) as total'))
            ->leftJoin('asset_locations', 'assets.location_id', '=', 'asset_locations.id')
            ->groupBy('assets.location_id', 'asset_locations.name')
            ->get();

        $chartLokasiData = [
            'labels' => $rawLokasiData->map(function($item) {
                return $item->lokasi_name ?? 'Tanpa Lokasi';
            })->toArray(),
            'values' => $rawLokasiData->pluck('total')->toArray(),
        ];

        // 3. Data Grafik Chart Departemen (Join langsung ke tabel departments dari assets)
        $rawDepartemenData = Asset::select('departments.name as dept_name', DB::raw('sum(assets.total_price) as total_nilai'))
            ->leftJoin('departments', 'assets.department_id', '=', 'departments.id')
            ->groupBy('departments.id', 'departments.name')
            ->get();

        $chartDeptData = [
            'labels' => $rawDepartemenData->map(function($item) {
                return $item->dept_name ?? 'Tanpa Departemen';
            })->toArray(),
            'values' => $rawDepartemenData->pluck('total_nilai')->toArray(),
        ];

        $filterLokasi = AssetLocation::select('id', 'name')->whereNotNull('name')->orderBy('name', 'asc')->get();
        $filterDepartemen = Department::select('id', 'name')->orderBy('name', 'asc')->get();

        return view('admin.dashboard', compact(
            'locations',
            'totalAssetCount',
            'totalInvestmentValue',
            'totalBookValueHabis',
            'chartLokasiData',
            'chartDeptData',
            'filterLokasi',
            'filterDepartemen'
        ));
    }

    /**
     * Mengambil data internal dashboard (KPI & Chart) via API JSON (AJAX)
     */
    public function getDashboardData(Request $request)
    {
        // 1. Definisikan Base Query utama dengan join yang benar
        $baseQuery = Asset::query()
            ->leftJoin('asset_locations', 'assets.location_id', '=', 'asset_locations.id')
            ->leftJoin('departments', 'assets.department_id', '=', 'departments.id');

        // 2. Terapkan filter ke base query
        if ($request->filled('lokasi')) {
            $baseQuery->where('assets.location_id', $request->lokasi);
        }
        if ($request->filled('department_id')) {
            $baseQuery->where('assets.department_id', $request->department_id);
        }
        if ($request->filled('status')) {
            $baseQuery->where('assets.status', $request->status);
        }

        // 3. Hitung KPI dari query yang sudah difilter
        $totalCount = (clone $baseQuery)->count('assets.id');
        $totalValue = (clone $baseQuery)->sum('assets.total_price');
        $totalBookValueHabis = (clone $baseQuery)->where('assets.book_value', 0)->count();

        $totalLocation = $request->filled('lokasi') ? 1 : AssetLocation::count();

        // 4. Query Grafik 1: Distribusi Jumlah Aset per Lokasi
        $lokasiData = (clone $baseQuery)
            ->select('asset_locations.name as lokasi_name', DB::raw('count(assets.id) as total'))
            ->groupBy('assets.location_id', 'asset_locations.name')
            ->get();

        $chartLokasiLabels = $lokasiData->map(function($item) {
            return $item->lokasi_name ?? 'Tanpa Lokasi';
        })->toArray();
        $chartLokasiValues = $lokasiData->pluck('total')->toArray();

        // 5. Query Grafik 2: Finansial Total Nilai per Departemen
        $departemenData = (clone $baseQuery)
            ->select('departments.name as dept_name', DB::raw('SUM(assets.total_price) as total_nilai'))
            ->groupBy('departments.id', 'departments.name')
            ->get();

        $chartDeptLabels = $departemenData->map(function($item) {
            return $item->dept_name ?? 'Tanpa Departemen';
        })->toArray();
        $chartDeptValues = $departemenData->pluck('total_nilai')->toArray();

        // Response JSON
        return response()->json([
            'kpi' => [
                'total_count'            => $totalCount,
                'total_value'            => 'Rp ' . number_format($totalValue, 0, ',', '.'),
                'total_location'         => $totalLocation,
                'total_book_value_habis' => $totalBookValueHabis
            ],
            'chart_lokasi' => [
                'labels' => $chartLokasiLabels,
                'values' => $chartLokasiValues,
            ],
            'chart_departemen' => [
                'labels' => $chartDeptLabels,
                'values' => $chartDeptValues,
            ]
        ]);
    }
}