<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Department;
use App\Models\AssetTransfer;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AssetDashboardController extends Controller
{
    /**
     * Menampilkan Halaman Utama Dashboard (Blade View)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $userAgent = $request->header('User-Agent');
        $isMobileDevice = preg_match('/(android|bb\d+|meego).+mobile|iphone|ipad/i', $userAgent);

        if ($isMobileDevice) {
            return redirect()->route('mobile.dashboard');
        }

        if (!$user->hasRole('super_admin') && !$user->hasPermissionTo('access-desktop')) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi Anda diakhiri karena akun Anda tidak memiliki izin akses ke Dashboard Desktop.'
            ]);
        }

        // 1. KPI Utama
        $locations = AssetLocation::all();
        $totalAssetCount = Asset::count();
        $totalBookValueHabis = Asset::where('book_value', 0)->count();
        $totalNew = Asset::where(function ($q) {
            $q->where('purchase_date', '>=', Carbon::now()->subDays(7))
                ->orWhere('created_at', '>=', Carbon::now()->subDays(7));
        })->count();

        $statusCounts = Asset::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalActive = $statusCounts['active'] ?? 0;
        $totalMaintenance = $statusCounts['maintenance'] ?? 0;

        // 2. Chart Distribusi Lokasi
        $rawLokasiData = Asset::select('asset_locations.name as lokasi_name', DB::raw('count(assets.id) as total'))
            ->leftJoin('asset_locations', 'assets.location_id', '=', 'asset_locations.id')
            ->groupBy('assets.location_id', 'asset_locations.name')
            ->get();

        $chartLokasiData = [
            'labels' => $rawLokasiData->map(fn($item) => $item->lokasi_name ?? 'Tanpa Lokasi')->toArray(),
            'values' => $rawLokasiData->pluck('total')->toArray(),
        ];

        // 3. Chart Volume Departemen
        $rawDepartemenData = Asset::select('departments.name as dept_name', DB::raw('count(assets.id) as total_unit'))
            ->leftJoin('departments', 'assets.department_id', '=', 'departments.id')
            ->groupBy('departments.id', 'departments.name')
            ->get();

        $chartDeptData = [
            'labels' => $rawDepartemenData->map(fn($item) => $item->dept_name ?? 'Tanpa Departemen')->toArray(),
            'values' => $rawDepartemenData->pluck('total_unit')->toArray(),
        ];

        // 4. Chart Status Aset (Pie)
        $chartStatusData = $this->buildStatusChart(Asset::query());

        // 5. Chart Tren Akuisisi Aset (12 bulan terakhir)
        $chartTrendData = $this->buildMonthlyTrend(Asset::query(), 12);

        // 6. Aktivitas Terbaru
        $recentActivities = $this->getRecentActivities(10);

        $filterLokasi = AssetLocation::select('id', 'name')->whereNotNull('name')->orderBy('name', 'asc')->get();
        $filterDepartemen = Department::select('id', 'name')->orderBy('name', 'asc')->get();

        return view('admin.dashboard', compact(
            'locations',
            'totalAssetCount',
            'totalBookValueHabis',
            'totalActive',
            'totalMaintenance',
            'chartLokasiData',
            'chartDeptData',
            'chartStatusData',
            'chartTrendData',
            'recentActivities',
            'filterLokasi',
            'filterDepartemen',
            'totalNew'
        ));
    }

    /**
     * Mengambil data internal dashboard (KPI & Chart) via API JSON (AJAX)
     */
    public function getDashboardData(Request $request)
    {
        $baseQuery = Asset::query()
            ->leftJoin('asset_locations', 'assets.location_id', '=', 'asset_locations.id')
            ->leftJoin('departments', 'assets.department_id', '=', 'departments.id');

        if ($request->filled('lokasi')) {
            $baseQuery->where('assets.location_id', $request->lokasi);
        }
        if ($request->filled('department_id')) {
            $baseQuery->where('assets.department_id', $request->department_id);
        }
        if ($request->filled('status')) {
            $baseQuery->where('assets.status', $request->status);
        }

        $totalCount = (clone $baseQuery)->count('assets.id');
        $totalBookValueHabis = (clone $baseQuery)->where('assets.book_value', 0)->count();
        $totalLocation = $request->filled('lokasi') ? 1 : AssetLocation::count();

        $statusCounts = (clone $baseQuery)
            ->select('assets.status', DB::raw('count(*) as total'))
            ->groupBy('assets.status')
            ->pluck('total', 'assets.status');

        $totalActive = $statusCounts['active'] ?? 0;
        $totalMaintenance = $statusCounts['maintenance'] ?? 0;

        $lokasiData = (clone $baseQuery)
            ->select('asset_locations.name as lokasi_name', DB::raw('count(assets.id) as total'))
            ->groupBy('assets.location_id', 'asset_locations.name')
            ->get();

        $chartLokasiLabels = $lokasiData->map(fn($item) => $item->lokasi_name ?? 'Tanpa Lokasi')->toArray();
        $chartLokasiValues = $lokasiData->pluck('total')->toArray();

        $departemenData = (clone $baseQuery)
            ->select('departments.name as dept_name', DB::raw('count(assets.id) as total_unit'))
            ->groupBy('departments.id', 'departments.name')
            ->get();

        $chartDeptLabels = $departemenData->map(fn($item) => $item->dept_name ?? 'Tanpa Departemen')->toArray();
        $chartDeptValues = $departemenData->pluck('total_unit')->toArray();

        $chartStatusData = $this->buildStatusChart($baseQuery);
        $chartTrendData = $this->buildMonthlyTrend($baseQuery, 12);

        return response()->json([
            'kpi' => [
                'total_count'            => $totalCount,
                'total_location'         => $totalLocation,
                'total_book_value_habis' => $totalBookValueHabis,
                'total_active'           => $totalActive,
                'total_maintenance'      => $totalMaintenance,
            ],
            'chart_lokasi' => [
                'labels' => $chartLokasiLabels,
                'values' => $chartLokasiValues,
            ],
            'chart_departemen' => [
                'labels' => $chartDeptLabels,
                'values' => $chartDeptValues,
            ],
            'chart_status' => $chartStatusData,
            'chart_trend'  => $chartTrendData,
        ]);
    }

    /**
     * Membangun data breakdown status aset (Active, Draft, Maintenance, Disposed, Lost).
     */
    private function buildStatusChart($query): array
    {
        $labelMap = [
            'active'      => 'Aktif',
            'draft'       => 'Draft',
            'maintenance' => 'Maintenance',
            'disposed'    => 'Disposed',
            'lost'        => 'Lost',
            'borrowed'    => 'Dipinjam',
        ];

        $raw = (clone $query)
            ->select('assets.status', DB::raw('count(*) as total'))
            ->groupBy('assets.status')
            ->pluck('total', 'assets.status');

        $labels = [];
        $values = [];

        foreach ($labelMap as $key => $label) {
            if (isset($raw[$key])) {
                $labels[] = $label;
                $values[] = (int) $raw[$key];
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Membangun data tren jumlah aset masuk per bulan (N bulan terakhir).
     */
    private function buildMonthlyTrend($query, int $months = 12): array
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        $raw = (clone $query)
            ->selectRaw("TO_CHAR(assets.purchase_date, 'YYYY-MM') as ym, COUNT(*) as total")
            ->whereNotNull('assets.purchase_date')
            ->where('assets.purchase_date', '>=', $start)
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $values = [];

        for ($i = 0; $i < $months; $i++) {
            $date = $start->copy()->addMonths($i);
            $key = $date->format('Y-m');
            $labels[] = $date->format('M Y');
            $values[] = (int) ($raw[$key] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Menggabungkan aktivitas terbaru dari mutasi, peminjaman, dan maintenance.
     */
    private function getRecentActivities(int $limit = 10)
    {
        $transfers = AssetTransfer::with('asset:id,name')
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($t) {
                $tujuan = $t->to_location_name ?? optional($t->toLocation)->name;
                return [
                    'type'        => 'transfer',
                    'icon'        => '🔄',
                    'title'       => 'Mutasi Aset',
                    'description' => 'Aset "' . ($t->asset->name ?? '-') . '"' . ($tujuan ? ' dipindahkan ke ' . $tujuan : ' diajukan mutasi'),
                    'status'      => $t->status,
                    'time'        => $t->created_at,
                ];
            });

        $loans = AssetLoan::with('asset:id,name')
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($l) {
                return [
                    'type'        => 'loan',
                    'icon'        => '📤',
                    'title'       => 'Peminjaman Aset',
                    'description' => 'Aset "' . ($l->asset->name ?? '-') . '" (No. ' . $l->loan_number . ')',
                    'status'      => $l->status,
                    'time'        => $l->created_at,
                ];
            });

        $maintenances = AssetMaintenance::with('asset:id,name')
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($m) {
                return [
                    'type'        => 'maintenance',
                    'icon'        => '🔧',
                    'title'       => 'Maintenance Aset',
                    'description' => 'Aset "' . ($m->asset->name ?? '-') . '" - ' . $m->title,
                    'status'      => $m->status,
                    'time'        => $m->created_at,
                ];
            });

        return $transfers->concat($loans)->concat($maintenances)
            ->sortByDesc('time')
            ->take($limit)
            ->values();
    }
}
