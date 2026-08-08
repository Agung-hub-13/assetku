<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\AssetTransfer;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Services\AssetSyncService;
use App\Jobs\SyncAllAssetsJob;        
use App\Jobs\SyncAssetFromAccurateJob; 
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Exports\AssetsExport;

class AssetController extends Controller
{
    private function generateAssetPrefix(string $assetName): string
    {
        $mapping = [
            'meja komputer' => 'MJK',
            'meja belajar'  => 'MJB',
            'meja makan'    => 'MJM',
            'meja'          => 'MJA',
            'lemari'        => 'LMR',
            'laptop'        => 'LT',
            'komputer'      => 'PC',
            'printer'       => 'PRN',
            'kursi'         => 'KRS',
        ];

        $cleanedName = strtolower($assetName);

        foreach ($mapping as $keyword => $prefix) {
            if (str_contains($cleanedName, $keyword)) {
                return $prefix; 
            }
        }

        return 'AST';
    }

    private function generateUniqueAssetCode(string $assetName): string
    {
        $prefix = $this->generateAssetPrefix($assetName);
        $currentYear = date('Y');

        $lastAsset = Asset::where('asset_code', 'like', "{$prefix}-{$currentYear}-%")
            ->orderBy('asset_code', 'desc')
            ->first();

        if ($lastAsset) {
            $lastNumber = (int) substr($lastAsset->asset_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '-' . $currentYear . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        // 1. Eager Loading Relasi
        $query = Asset::with([
            'location:id,name,building,floor,room',
            'category:id,name',
            'department:id,name',
            'user:id,name', // <-- Opsional: Tambahkan relasi user agar bisa di-load jika diperlukan
            'transfer' => function ($q) {
                $q->latest();
            },
            'transfer.toLocation:id,name,building,floor,room',
            'activeLoan.user:id,name',
            'activeMaintenance.technician:id,name',
        ]);

        // 2. Filter Search (PostgreSQL Case-Insensitive ILIKE)
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

        // 3. Filter Kategori, Ruangan, & Status
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('room_id')) {
            $query->where('location_id', $request->room_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->get('depreciated') == '1') {
            $query->where('book_value', '<=', 0);
        }

        // 4. Perhitungan Summary
        $totalInvestasi = (clone $query)->sum('purchase_price');
        $totalBookValueHabis = (clone $query)->where('book_value', '<=', 0)->count();

        // 5. Pagination Data Assets
        $assets = $query->orderBy('updated_at', 'desc')
            ->paginate(50)
            ->appends($request->all());

        // 6. Data Master untuk Options
        $locations = AssetLocation::select('id', 'name', 'building', 'floor', 'room')
            ->orderBy('name')
            ->get();

        $transfers = AssetTransfer::select('id', 'asset_id', 'from_location_id', 'to_location_id', 'created_at')
            ->with([
                'asset:id,name,asset_code',
                'fromLocation:id,name',
                'toLocation:id,name'
            ])
            ->latest()
            ->take(5)
            ->get();

        $categories = AssetCategory::select('id', 'name', 'code_prefix')->get();

        $departments = Department::select('id', 'name')->orderBy('name')->get();

        // 👇 TAMBAHKAN INI: Ambil data users agar dropdown tidak kosong
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        // 7. Penentuan View Path
        $viewPath = ($request->routeIs('mobile.*') || $request->is('mobile/*'))
            ? 'mobile.assets.index'
            : 'admin.assets.index';

        return view($viewPath, compact(
            'assets',
            'locations',
            'totalInvestasi',
            'totalBookValueHabis',
            'transfers',
            'categories',
            'departments',
            'users' // 👇 MASUKKAN KE COMPACT
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_number' => 'nullable|string|max:255|unique:assets,asset_number',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'asset_code' => 'nullable|string|max:255',
            'qr_token' => 'nullable|uuid|unique:assets,qr_token',
            'serial_number' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:asset_categories,id',
            'location_id' => 'nullable|exists:asset_locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'purchase_date' => 'nullable|date',
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'nullable|numeric|min:0',
            'book_value' => 'nullable|numeric|min:0',
            'residual_value' => 'nullable|numeric|min:0',
            'accumulated_depreciation' => 'nullable|numeric|min:0',
            'useful_life_month' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,active,maintenance,disposed,lost',
            'accurate_item_id' => 'nullable|numeric|unique:assets,accurate_item_id',
            'accurate_fixed_asset_id' => 'nullable|numeric|unique:assets,accurate_fixed_asset_id',
            'accurate_purchase_id' => 'nullable|numeric',
            'accurate_db_id' => 'nullable|numeric',
            'accurate_session' => 'nullable|string',
            'accurate_host' => 'nullable|string',
            'accurate_no' => 'nullable|string',
            'accurate_name' => 'nullable|string',
            'accurate_item_type' => 'nullable|string',
            'is_synced' => 'nullable|boolean',
            'from_accurate' => 'nullable|boolean',
            'auto_sync' => 'nullable|boolean',
            'accurate_raw_json' => 'nullable|string',
        ]);

        if (empty($validated['asset_number'])) {
            $validated['asset_number'] = 'AST-' . strtoupper(Str::random(10));
        }

        if (empty($validated['asset_code'])) {
            $validated['asset_code'] = $this->generateUniqueAssetCode($validated['name']);
        }

        $validated['quantity'] = $validated['quantity'] ?? 1;
        $validated['purchase_price'] = $validated['purchase_price'] ?? 0;
        $validated['book_value'] = $validated['book_value'] ?? 0;
        $validated['residual_value'] = $validated['residual_value'] ?? 0;
        $validated['accumulated_depreciation'] = $validated['accumulated_depreciation'] ?? 0;
        $validated['total_price'] = $validated['quantity'] * $validated['purchase_price'];
        $validated['is_synced'] = $request->boolean('is_synced');
        $validated['from_accurate'] = $request->boolean('from_accurate');
        $validated['auto_sync'] = $request->boolean('auto_sync', true);

        Asset::create($validated);

        return redirect()
            ->route('admin.assets.index')
            ->with('success', 'Asset berhasil ditambahkan.');
    }

    public function update(Request $request, Asset $asset)
    {
        Log::info('================ UPDATE ASSET ================');
        Log::info('Asset ID', ['id' => $asset->id]);
        Log::info('Request Data', $request->all());

        try {
            $validated = $request->validate([
                'asset_number' => 'required|string|max:255|unique:assets,asset_number,' . $asset->id,
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'asset_code' => 'nullable|string|max:255',
                'qr_token' => 'nullable|uuid|unique:assets,qr_token,' . $asset->id,
                'serial_number' => 'nullable|string|max:255',
                'category_id' => 'nullable|exists:asset_categories,id',
                'location_id' => 'nullable|exists:asset_locations,id',
                'department_id' => 'nullable|exists:departments,id',
                'user_id' => 'nullable|exists:users,id',
                'purchase_date' => 'nullable|date',
                'quantity' => 'required|integer|min:1',
                'purchase_price' => 'nullable|numeric|min:0',
                'book_value' => 'nullable|numeric|min:0',
                'residual_value' => 'nullable|numeric|min:0',
                'accumulated_depreciation' => 'nullable|numeric|min:0',
                'useful_life_month' => 'nullable|integer|min:0',
                'last_maintenance_date' => 'nullable|date',
                'next_maintenance_date' => 'nullable|date',
                'status' => 'required|in:draft,active,borrowed,maintenance,disposed,lost',
                'accurate_item_id' => 'nullable|numeric|unique:assets,accurate_item_id,' . $asset->id,
                'accurate_fixed_asset_id' => 'nullable|numeric|unique:assets,accurate_fixed_asset_id,' . $asset->id,
                'accurate_purchase_id' => 'nullable|numeric',
                'accurate_db_id' => 'nullable|numeric',
                'accurate_session' => 'nullable|string',
                'accurate_host' => 'nullable|string',
                'accurate_no' => 'nullable|string',
                'accurate_name' => 'nullable|string',
                'accurate_item_type' => 'nullable|string',
                'is_synced' => 'nullable|boolean',
                'from_accurate' => 'nullable|boolean',
                'auto_sync' => 'nullable|boolean',
                'accurate_raw_json' => 'nullable|string',
            ]);

            Log::info('VALIDATED DATA', $validated);

            if (empty($validated['asset_code'])) {
                $validated['asset_code'] = $this->generateUniqueAssetCode($validated['name']);
            }

            $validated['purchase_price'] = $validated['purchase_price'] ?? 0;
            $validated['quantity'] = $validated['quantity'] ?? 1;
            $validated['total_price'] = $validated['purchase_price'] * $validated['quantity'];
            $validated['is_synced'] = $request->boolean('is_synced');
            $validated['from_accurate'] = $request->boolean('from_accurate');
            $validated['auto_sync'] = $request->boolean('auto_sync', true);

            Log::info('DATA UPDATE', $validated);

            $result = $asset->update($validated);

            Log::info('HASIL UPDATE', [
                'success' => $result,
                'asset_id' => $asset->id,
            ]);

            Log::info('================ UPDATE BERHASIL ================');

            return redirect()
                ->route('admin.assets.index')
                ->with('success', 'Asset berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('================ UPDATE GAGAL ================');
            Log::error($e->getMessage());
            Log::error('File : ' . $e->getFile());
            Log::error('Line : ' . $e->getLine());
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $asset = Asset::with(['category', 'location', 'department', 'user', 'activeLoan.user', 'activeMaintenance.technician'])->findOrFail($id);

        $qrCode = QrCode::encoding('UTF-8')
            ->size(200)
            ->style('round')
            ->margin(1)
            ->generate(route('admin.assets.show', $asset->id));

        if (request()->routeIs('mobile.*')) {
            return view('mobile.assets.show', compact('asset'));
        }

        return view('admin.assets.show', compact('asset', 'qrCode'));
    }

    public function printQrCode($id)
    {
        $asset = Asset::findOrFail($id);

        $qrCode = QrCode::encoding('UTF-8')
            ->size(250)
            ->style('round')
            ->margin(1)
            ->generate(route('admin.assets.show', $asset->id));

        return view('admin.assets.print-qrcode', compact('asset', 'qrCode'));
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()
            ->route('admin.assets.index')
            ->with('success', 'Asset berhasil dihapus.');
    }

    public function locationsByDepartmentName($department)
    {
        $department = urldecode($department);

        $root = AssetLocation::whereNull('parent_id')
            ->where('accurate_department_name', $department)
            ->first();

        if (!$root) {
            $locations = AssetLocation::where('accurate_department_name', $department)
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json($locations);
        }

        $locations = AssetLocation::where('parent_id', $root->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($locations);
    }

    public function publicPreview($qr_token)
    {
        $asset = Asset::with(['category', 'location'])
            ->where('qr_token', $qr_token)
            ->firstOrFail();

        return view('admin.assets.print-qrcode', compact('asset'));
    }

    public function exportExcel(Request $request)
    {
        return AssetsExport::exportExcel($request);
    }

    public function bulkAssign(Request $request)
    {
        Log::info('=== DEBUG BULK ASSIGN STARTED ===');
        Log::info('Request Payload:', $request->all());

        try {
            $request->validate([
                'asset_ids'   => 'required',
                'location_id' => 'required|exists:asset_locations,id',
                'category_id' => 'required|exists:asset_categories,id',
                'status'      => 'nullable',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Bulk Assign Validation Failed:', $e->errors());
            return back()->with('error', 'Validasi Gagal: ' . json_encode($e->errors()));
        }

        $ids = is_array($request->asset_ids)
            ? $request->asset_ids
            : array_filter(explode(',', $request->asset_ids));

        Log::info('Parsed Asset IDs:', $ids);

        if (empty($ids)) {
            Log::warning('Bulk Assign Cancelled: Asset IDs Array is Empty');
            return back()->with('error', 'Gagal: Tidak ada ID aset yang terikirim/dipilih.');
        }

        try {
            $category = AssetCategory::findOrFail($request->category_id);
            $prefix = $category->code_prefix ?? 'AST';

            DB::transaction(function () use ($ids, $request, $prefix) {
                $lastAsset = Asset::where('asset_code', 'LIKE', $prefix . '-%')
                    ->orderByRaw("CAST(SPLIT_PART(asset_code, '-', 2) AS INTEGER) DESC")
                    ->first();

                $nextNumber = 1;
                if ($lastAsset) {
                    $lastNumberString = substr($lastAsset->asset_code, strlen($prefix) + 1);
                    $nextNumber = (int)$lastNumberString + 1;
                }

                $assets = Asset::whereIn('id', $ids)->get();

                Log::info('Found Assets Count in DB:', ['count' => $assets->count()]);

                if ($assets->isEmpty()) {
                    throw new \Exception('Data aset tidak ditemukan di database berdasarkan ID yang dikirim.');
                }

                foreach ($assets as $asset) {
                    $formattedCode = $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                    $updateData = [
                        'location_id' => $request->location_id,
                        'category_id' => $request->category_id,
                        'asset_code'  => $formattedCode,
                    ];

                    if ($request->filled('status')) {
                        $updateData['status'] = $request->status;
                    }

                    Log::info("Updating Asset ID {$asset->id}:", $updateData);

                    $updated = $asset->update($updateData);

                    if (!$updated) {
                        throw new \Exception("Gagal mengupdate model Asset ID: {$asset->id}. Cek \$fillable di Model Asset.");
                    }

                    $nextNumber++;
                }
            });

            Log::info('=== BULK ASSIGN SUCCESS ===');
            return back()->with('success', count($ids) . ' asset berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('=== BULK ASSIGN FAILED ===');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());

            return back()->with('error', 'Gagal memperbarui asset: ' . $e->getMessage() . ' (Line ' . $e->getLine() . ')');
        }
    }

    public function bulkPrintQr(Request $request)
    {
        $request->validate([
            'asset_ids' => 'required'
        ]);

        $ids = explode(',', $request->asset_ids);

        $assets = Asset::with(['location', 'category'])->whereIn('id', $ids)->get();

        foreach ($assets as $asset) {
            $asset->generated_qr = QrCode::encoding('UTF-8')
                ->size(150)
                ->style('round')
                ->margin(1)
                ->generate(route('admin.assets.show', $asset->id));
        }

        return view('admin.assets.bulk-print-qrcode', compact('assets'));
    }

    public function syncSingle(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);
        $accurateId = $asset->accurate_fixed_asset_id ?? $asset->accurate_item_id;

        if (!$accurateId) {
            return back()->with('error', 'Aset ini belum memiliki ID Accurate yang terhubung.');
        }

        // Jalankan via Queue Job di background agar tidak blocking / timeout
        SyncAssetFromAccurateJob::dispatch($accurateId);

        return back()->with('success', "Proses sync untuk aset '{$asset->name}' sedang berjalan di latar belakang.");
    }

    public function syncAll()
    {
        try {
            // Jalankan via Queue Job mass sync di background
            SyncAllAssetsJob::dispatch();
            
            return back()->with('success', 'Proses sync seluruh data dari Accurate sedang berjalan di latar belakang!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menjadwalkan sync massal: ' . $e->getMessage());
        }
    }
}