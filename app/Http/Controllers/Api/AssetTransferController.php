<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AssetLogController;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\AssetTransfer;
use App\Models\Department;
use App\Models\User;
use App\Notifications\AssetTransferNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AssetTransferController extends Controller
{
    /**
     * Menampilkan daftar mutasi aset dengan filter & pagination (JSON)
     */
    public function index(Request $request)
    {
        Log::info('[API][AssetTransferController@index] Memuat daftar mutasi aset', [
            'user_id' => Auth::id(),
            'filter'  => $request->all()
        ]);

        try {
            $query = AssetTransfer::with([
                'asset',
                'fromLocation',
                'toLocation',
                'fromDepartment',
                'toDepartment',
                'fromUser',
                'toUser',
                'creator',
                'approver'
            ]);

            // Filter pencarian berdasarkan nama aset atau kode aset
            if ($request->filled('search')) {
                $search = strtolower($request->input('search'));
                $query->whereHas('asset', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->whereRaw('LOWER(name) like ?', ['%' . $search . '%'])
                            ->orWhereRaw('LOWER(asset_code) like ?', ['%' . $search . '%']);
                    });
                });
            }

            if ($request->filled('transfer_type')) {
                $query->where('transfer_type', $request->input('transfer_type'));
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('from_location_id')) {
                $query->where('from_location_id', $request->input('from_location_id'));
            }

            if ($request->filled('to_location_id')) {
                $query->where('to_location_id', $request->input('to_location_id'));
            }

            if ($request->filled('start_date')) {
                $query->whereDate('transfer_date', '>=', $request->input('start_date'));
            }

            if ($request->filled('end_date')) {
                $query->whereDate('transfer_date', '<=', $request->input('end_date'));
            }

            $perPage = $request->input('per_page', 15);
            $transfers = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Daftar mutasi aset berhasil dimuat.',
                'data'    => $transfers
            ], 200);

        } catch (Throwable $e) {
            Log::error('[API][AssetTransferController@index] Error:', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data mutasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil master data pendukung form mutasi (Aset, Lokasi, Departemen, User)
     */
    public function getMasterData(Request $request)
    {
        try {
            $assets      = Asset::select('id', 'name', 'asset_code', 'accurate_no', 'location_id', 'department_id', 'status')
                                ->orderBy('name')
                                ->limit(200)
                                ->get();
            $locations   = AssetLocation::select('id', 'name', 'building', 'floor', 'room')->orderBy('name')->get();
            $departments = Department::select('id', 'name')->orderBy('name')->get();
            $users       = User::select('id', 'name', 'email')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'message' => 'Master data berhasil dimuat.',
                'data'    => [
                    'assets'      => $assets,
                    'locations'   => $locations,
                    'departments' => $departments,
                    'users'       => $users,
                ]
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat master data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan pengajuan / draft mutasi aset baru dari mobile
     */
    public function store(Request $request)
    {
        Log::info('[API][AssetTransferController@store] Menambah draft mutasi aset', [
            'user_id' => Auth::id(),
            'payload' => $request->all()
        ]);

        $request->validate([
            'asset_id'           => 'required|exists:assets,id',
            'transfer_type'      => 'required|in:location_change,temporary,return',
            'to_location_id'     => 'nullable|exists:asset_locations,id',
            'to_department_id'   => 'nullable|exists:departments,id',
            'to_user_id'         => 'nullable|exists:users,id',
            'transfer_date'      => 'required|date',
            'document_number'    => 'nullable|string|max:255',
            'reason'             => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
            'entry_method'       => 'nullable|in:manual,qr_scan',
            'attachment'         => 'nullable|string',
        ]);

        try {
            $transfer = null;

            DB::transaction(function () use ($request, &$transfer) {
                $asset = Asset::with(['location', 'department', 'user'])->findOrFail($request->asset_id);

                $targetLocation   = $request->filled('to_location_id') ? AssetLocation::find($request->to_location_id) : null;
                $targetDepartment = $request->filled('to_department_id') ? Department::find($request->to_department_id) : null;
                $targetUser       = $request->filled('to_user_id') ? User::find($request->to_user_id) : null;

                $fromLocationName   = $asset->location ? $asset->location->name : '-';
                $fromDepartmentName = $asset->department ? $asset->department->name : ($asset->location->accurate_department_name ?? $asset->location->department_name ?? '-');
                $fromUserName       = $asset->user ? $asset->user->name : '-';

                $toLocationName   = $targetLocation ? $targetLocation->name : '-';
                $toDepartmentName = $targetDepartment ? $targetDepartment->name : ($targetLocation->accurate_department_name ?? $targetLocation->department_name ?? '-');
                $toUserName       = $targetUser ? $targetUser->name : '-';

                $dataToInsert = [
                    'asset_id'             => $asset->id,
                    'transfer_type'        => $request->transfer_type,
                    'from_location_id'     => $asset->location_id,
                    'to_location_id'       => $targetLocation ? $targetLocation->id : null,
                    'from_location_name'   => $fromLocationName,
                    'from_department_name' => $fromDepartmentName,
                    'from_user_name'       => $fromUserName,
                    'to_location_name'     => $toLocationName,
                    'to_department_name'   => $toDepartmentName,
                    'to_user_name'         => $toUserName,
                    'transfer_date'        => $request->transfer_date,
                    'document_number'      => $request->document_number,
                    'reason'               => $request->reason,
                    'notes'                => $request->notes,
                    'status'               => 'draft',
                    'created_by'           => Auth::id(),
                    'entry_method'         => $request->entry_method ?? 'manual',
                    'attachment'           => $request->attachment,
                ];

                if (Schema::hasColumn('asset_transfers', 'from_department_id')) {
                    $dataToInsert['from_department_id'] = $asset->department_id ?? ($asset->location ? $asset->location->department_id ?? null : null);
                }
                if (Schema::hasColumn('asset_transfers', 'from_user_id')) {
                    $dataToInsert['from_user_id'] = $asset->user_id ?? $asset->assigned_to ?? null;
                }
                if (Schema::hasColumn('asset_transfers', 'to_department_id')) {
                    $dataToInsert['to_department_id'] = $targetDepartment ? $targetDepartment->id : null;
                }
                if (Schema::hasColumn('asset_transfers', 'to_user_id')) {
                    $dataToInsert['to_user_id'] = $targetUser ? $targetUser->id : null;
                }

                $transfer = AssetTransfer::create($dataToInsert);

                AssetLogController::log(
                    $asset->id,
                    'transfer_requested',
                    "Pengajuan mutasi aset ke {$toLocationName} (Tipe: {$request->transfer_type}).",
                    [
                        'transfer_id'   => $transfer->id,
                        'lokasi_asal'   => $fromLocationName,
                        'lokasi_tujuan' => $toLocationName,
                        'tgl_mutasi'    => $request->transfer_date,
                        'alasan'        => $request->reason ?? '-',
                    ]
                );
            });

            if ($transfer) {
                $this->sendTransferNotification($transfer, 'created');
            }

            return response()->json([
                'success' => true,
                'message' => 'Draft mutasi berhasil ditambahkan.',
                'data'    => $transfer->load(['asset', 'fromLocation', 'toLocation'])
            ], 201);

        } catch (Throwable $e) {
            Log::error('[API][AssetTransferController@store] Error:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat draft mutasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail mutasi aset
     */
    public function show($id)
    {
        try {
            $assetTransfer = AssetTransfer::with([
                'asset.category',
                'fromLocation',
                'toLocation',
                'fromDepartment',
                'toDepartment',
                'fromUser',
                'toUser',
                'creator',
                'approver'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail mutasi aset berhasil dimuat.',
                'data'    => $assetTransfer
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data mutasi tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * Menyetujui (Approve) pengajuan mutasi aset
     */
    public function approve(Request $request, $id)
    {
        $transfer = AssetTransfer::where('id', $id)->lockForUpdate()->first();

        if (!$transfer) {
            return response()->json([
                'success' => false,
                'message' => 'Data mutasi tidak ditemukan.'
            ], 404);
        }

        if (!in_array($transfer->status, ['draft', 'waiting_approval'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status transaksi tidak valid untuk disetujui.'
            ], 422);
        }

        try {
            DB::transaction(function () use ($transfer) {
                $transfer->update([
                    'status'      => 'completed',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                $asset = Asset::findOrFail($transfer->asset_id);

                $assetUpdateData = [];
                if ($transfer->to_location_id) {
                    $assetUpdateData['location_id'] = $transfer->to_location_id;
                }
                if ($transfer->to_department_id) {
                    $assetUpdateData['department_id'] = $transfer->to_department_id;
                }
                if ($transfer->to_user_id) {
                    if (Schema::hasColumn('assets', 'user_id')) {
                        $assetUpdateData['user_id'] = $transfer->to_user_id;
                    }
                    if (Schema::hasColumn('assets', 'assigned_to')) {
                        $assetUpdateData['assigned_to'] = $transfer->to_user_id;
                    }
                }

                if (!empty($assetUpdateData)) {
                    $asset->update($assetUpdateData);
                }

                AssetLogController::log(
                    $asset->id,
                    'transfer',
                    "Mutasi aset disetujui. Berpindah dari Lokasi: {$transfer->from_location_name} ke {$transfer->to_location_name}.",
                    [
                        'transfer_id'    => $transfer->id,
                        'lokasi_asal'    => $transfer->from_location_name,
                        'lokasi_tujuan'  => $transfer->to_location_name,
                        'disetujui_oleh' => Auth::user()->name ?? 'System Admin',
                        'tgl_eksekusi'   => now()->toDateTimeString(),
                    ]
                );
            });

            $this->sendTransferNotification($transfer, 'approved');

            return response()->json([
                'success' => true,
                'message' => 'Mutasi disetujui & data aset berhasil diperbarui.',
                'data'    => $transfer->fresh(['asset', 'fromLocation', 'toLocation'])
            ], 200);

        } catch (Throwable $e) {
            Log::error('[API][AssetTransferController@approve] Error:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui mutasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menolak (Reject) pengajuan mutasi aset
     */
    public function reject(Request $request, $id)
    {
        $transfer = AssetTransfer::where('id', $id)->lockForUpdate()->first();

        if (!$transfer) {
            return response()->json([
                'success' => false,
                'message' => 'Data mutasi tidak ditemukan.'
            ], 404);
        }

        if ($transfer->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Mutasi yang sudah selesai tidak dapat ditolak.'
            ], 422);
        }

        $rejectionReason = $request->input('rejection_reason');

        try {
            DB::transaction(function () use ($transfer, $rejectionReason) {
                $transfer->update([
                    'status'           => 'rejected',
                    'rejection_reason' => $rejectionReason
                ]);

                AssetLogController::log(
                    $transfer->asset_id,
                    'transfer_rejected',
                    "Pengajuan mutasi ke {$transfer->to_location_name} telah ditolak." . ($rejectionReason ? " Alasan: {$rejectionReason}" : ''),
                    [
                        'transfer_id'   => $transfer->id,
                        'lokasi_tujuan' => $transfer->to_location_name,
                        'ditolak_oleh'  => Auth::user()->name ?? 'System Admin',
                        'alasan'        => $rejectionReason ?? '-',
                    ]
                );
            });

            $this->sendTransferNotification($transfer, 'rejected');

            return response()->json([
                'success' => true,
                'message' => 'Mutasi berhasil ditolak.',
                'data'    => $transfer->fresh()
            ], 200);

        } catch (Throwable $e) {
            Log::error('[API][AssetTransferController@reject] Error:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak mutasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus data mutasi (jika masih draft)
     */
    public function destroy($id)
    {
        try {
            $transfer = AssetTransfer::findOrFail($id);

            if ($transfer->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Data mutasi yang sudah selesai tidak boleh dihapus.'
                ], 422);
            }

            $transfer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data mutasi berhasil dihapus.'
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data mutasi tidak ditemukan atau gagal dihapus.'
            ], 404);
        }
    }

    /**
     * Helper Kirim Notifikasi
     */
    private function sendTransferNotification(AssetTransfer $transfer, string $action): void
    {
        try {
            $recipients = collect();

            if ($transfer->created_by) {
                $creator = User::find($transfer->created_by);
                if ($creator) $recipients->push($creator);
            }

            if ($transfer->from_user_id) {
                $fromUser = User::find($transfer->from_user_id);
                if ($fromUser) $recipients->push($fromUser);
            }
            if ($transfer->to_user_id) {
                $toUser = User::find($transfer->to_user_id);
                if ($toUser) $recipients->push($toUser);
            }

            if (auth()->check()) {
                $recipients->push(auth()->user());
            }

            if ($action === 'created') {
                $admins = User::role(['Super Admin', 'Admin Asset', 'Supervisor'], 'web')->get();
                foreach ($admins as $admin) {
                    $recipients->push($admin);
                }
            }

            if ($transfer->approved_by) {
                $approver = User::find($transfer->approved_by);
                if ($approver) $recipients->push($approver);
            }

            $recipients     = $recipients->unique('id');
            $assetName      = $transfer->asset ? $transfer->asset->name : 'Aset';
            $transferNumber = $transfer->document_number ?? "TRF-{$transfer->id}";
            $url            = route('admin.asset_transfers.show', $transfer->id);

            switch ($action) {
                case 'created':
                    $title   = "Pengajuan Mutasi Aset Baru (#{$transferNumber})";
                    $message = "Pengajuan mutasi untuk aset '{$assetName}' telah berhasil dibuat.";
                    break;
                case 'approved':
                    $title   = "Mutasi Aset Disetujui (#{$transferNumber})";
                    $message = "Pengajuan mutasi untuk aset '{$assetName}' telah DISETUJUI.";
                    break;
                case 'rejected':
                    $title   = "Mutasi Aset Ditolak (#{$transferNumber})";
                    $reason  = $transfer->rejection_reason ? " Alasan: {$transfer->rejection_reason}" : '';
                    $message = "Pengajuan mutasi untuk aset '{$assetName}' DITOLAK.{$reason}";
                    break;
                default:
                    $title   = "Update Mutasi Aset (#{$transferNumber})";
                    $message = "Status mutasi aset '{$assetName}' diperbarui.";
                    break;
            }

            foreach ($recipients as $user) {
                $user->notify(new AssetTransferNotification(
                    $title,
                    $message,
                    $transferNumber,
                    $transfer->asset_id,
                    $url
                ));
            }
        } catch (Throwable $e) {
            Log::error('[API][AssetTransferController@sendTransferNotification] Gagal:', ['error' => $e->getMessage()]);
        }
    }
}