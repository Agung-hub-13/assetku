<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssetAudit;
use App\Models\AssetAuditItem;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssetAuditController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query utama audit beserta relasi & pencarian
        $query = AssetAudit::with(['location', 'auditor', 'items.asset'])
            ->withCount('items');

        // Filter berdasarkan kode audit / judul jika user melakukan pencarian
        if ($request->filled('audit_code')) {
            $query->where(function ($q) use ($request) {
                $q->where('audit_code', 'like', '%' . $request->audit_code . '%');

                if (Schema::hasColumn('asset_audits', 'title')) {
                    $q->orWhere('title', 'like', '%' . $request->audit_code . '%');
                }
            });
        }

        // Filter berdasarkan status audit
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Eksekusi data paginasi
        $audits = $query->latest()->paginate(10)->withQueryString();

        // 2. Ambil data pendukung untuk dropdown modal
        $locations = AssetLocation::select('id', 'name')->orderBy('name')->get();
        $auditors  = User::select('id', 'name')->orderBy('name')->get();

        // Menentukan kolom aset secara fleksibel dan aman dari duplicate column
        $assetColumns = ['id', 'name'];
        if (Schema::hasColumn('assets', 'asset_code')) {
            $assetColumns[] = 'asset_code';
        }
        if (Schema::hasColumn('assets', 'code')) {
            $assetColumns[] = 'code';
        }

        $assets = Asset::select(array_unique($assetColumns))
            ->orderBy('name')
            ->get();

        // 3. Render view
        return view('admin.asset_audits.index', compact('audits', 'locations', 'auditors', 'assets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            // Ubah rule in: dengan lowercase, atau tangani dengan strtolower di bawah, 
            // atau gunakan validasi yang lebih fleksibel:
            'scope_type'  => 'required|string',
            'location_id' => 'nullable|exists:asset_locations,id',
            'auditor_id'  => 'required|exists:users,id',
            'start_date'  => 'required|date',
            'notes'       => 'nullable|string',
        ]);

        // Normalisasi scope_type menjadi huruf kecil agar aman dari input kapital (LOCATION -> location)
        $scopeType = strtolower($request->scope_type);

        DB::transaction(function () use ($request, $scopeType) {
            // 1. Buat Header Audit
            $audit = AssetAudit::create([
                'audit_code'  => 'AUD-' . strtoupper(Str::random(8)),
                'title'       => $request->title,
                'location_id' => $scopeType === 'location' ? $request->location_id : null,
                'auditor_id'  => $request->auditor_id,
                'start_date'  => $request->start_date,
                'status'      => 'in_progress',
                'notes'       => $request->notes,
            ]);

            // 2. Ambil Aset berdasarkan Scope
            $query = Asset::query();
            if ($scopeType === 'location' && $request->location_id) {
                $query->where('location_id', $request->location_id);
            }
            $assetIds = $query->pluck('id');

            // 3. Populate Item Audit secara Bulk Insert
            $now = now();
            $itemsData = [];

            foreach ($assetIds as $assetId) {
                $itemsData[] = [
                    'asset_audit_id'  => $audit->id,
                    'asset_id'        => $assetId,
                    'physical_status' => 'pending',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            if (!empty($itemsData)) {
                foreach (array_chunk($itemsData, 500) as $chunk) {
                    AssetAuditItem::insert($chunk);
                }
            }
        });

        return redirect()->route('admin.asset_audits.index')
            ->with('success', 'Sesi audit berhasil dibuat dan item aset telah siap diaudit.');
    }

    public function show($id)
    {
        $audit = AssetAudit::with(['location', 'auditor', 'items.asset.location'])->findOrFail($id);
        return view('admin.asset_audits.show', compact('audit'));
    }

    // Update Status Fisik Item Aset (Individual/Bulk)
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'physical_status' => 'required|in:pending,found,missing,damaged,transferred',
            'notes'           => 'nullable|string',
        ]);

        $item = AssetAuditItem::findOrFail($itemId);
        $item->update([
            'physical_status' => $request->physical_status,
            'notes'           => $request->notes,
        ]);

        return back()->with('success', 'Status fisik aset berhasil diperbarui.');
    }

    // Selesaikan Sesi Audit
    public function complete($id)
    {
        $audit = AssetAudit::findOrFail($id);
        $audit->update(['status' => 'completed']);

        return redirect()->route('admin.asset_audits.index')
            ->with('success', 'Sesi audit telah ditandai Selesai.');
    }

    /**
     * Handle fast scan barcode / QR berdasarkan auditCode
     */
    public function scan(Request $request, $auditCode)
    {
        // Validasi input asset_code dari form
        $request->validate([
            'asset_code' => 'required|string',
        ], [
            'asset_code.required' => 'Kode atau Barcode aset tidak boleh kosong.',
        ]);

        $audit = AssetAudit::where('audit_code', $auditCode)->firstOrFail();

        // Cari aset berdasarkan asset_code atau qr_token
        $asset = Asset::where('asset_code', $request->asset_code)
            ->orWhere('qr_token', $request->asset_code)
            ->first();

        if (!$asset) {
            return back()->withErrors(['asset_code' => 'Aset dengan kode tersebut tidak ditemukan.']);
        }

        // Cari item audit yang sesuai dengan sesi audit ini
        $auditItem = AssetAuditItem::where('asset_audit_id', $audit->id)
            ->where('asset_id', $asset->id)
            ->first();

        if (!$auditItem) {
            return back()->withErrors(['asset_code' => "Aset '{$asset->name}' tidak terdaftar dalam cakupan sesi audit ini."]);
        }

        // Update status fisik item menjadi 'found' (ditemukan)
        $auditItem->update([
            'physical_status' => 'found',
            'updated_at' => now(),
        ]);

        return back()->with('success', "Aset '{$asset->name}' berhasil diverifikasi (Ditemukan).");
    }

    /**
     * Handle scan QR umum
     */
    public function scanQr(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        $asset = Asset::where('qr_token', $request->qr_token)
            ->orWhere('asset_code', $request->qr_token)
            ->first();

        if (!$asset) {
            return response()->json([
                'success' => false,
                'message' => 'Aset tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $asset,
        ]);
    }
}
