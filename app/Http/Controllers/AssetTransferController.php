<?php

namespace App\Http\Controllers;

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
    public function index(Request $request)
    {
        Log::info('[AssetTransferController@index] Memuat daftar mutasi aset', [
            'user_id' => Auth::id(),
            'filter'  => $request->all()
        ]);

        try {
            // 1. Inisialisasi query dengan eager loading lengkap sesuai relasi model
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

            // 2. Terapkan Filter sesuai parameter request
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

            if ($request->filled('from_department_id')) {
                $query->where('from_department_id', $request->input('from_department_id'));
            }

            if ($request->filled('to_department_id')) {
                $query->where('to_department_id', $request->input('to_department_id'));
            }

            if ($request->filled('from_user_id')) {
                $query->where('from_user_id', $request->input('from_user_id'));
            }

            if ($request->filled('to_user_id')) {
                $query->where('to_user_id', $request->input('to_user_id'));
            }

            if ($request->filled('start_date')) {
                $query->whereDate('transfer_date', '>=', $request->input('start_date'));
            }

            if ($request->filled('end_date')) {
                $query->whereDate('transfer_date', '<=', $request->input('end_date'));
            }

            // 3. Ambil data dengan Pagination
            $transfers = $query->latest()->paginate(50)->appends($request->all());

            // Batasi master data agar tidak membebani RAM (Memory Limit Exhausted)
            $assets     = Asset::orderBy('name')->limit(100)->get();
            $locations  = AssetLocation::orderBy('name')->get();
            $departments = Department::orderBy('name')->get();
            $users      = User::orderBy('name')->limit(100)->get();

            if ($request->routeIs('mobile.*')) {
                return view('mobile.asset_transfers.index', compact('transfers', 'assets', 'locations', 'departments', 'users'));
            }

            return view('admin.asset_transfers.index', compact('transfers', 'assets', 'locations', 'departments', 'users'));
        } catch (Throwable $e) {
            Log::error('[AssetTransferController@index] Error:', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal memuat data mutasi: ' . $e->getMessage());
        }
    }


    public function store(Request $request)
    {
        Log::info('[AssetTransferController@store] Menambah draft mutasi aset', [
            'user_id' => Auth::id(),
            'payload' => $request->except(['_token'])
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
                // Load asset beserta relasi lokasi, departemen, dan user saat ini
                $asset = Asset::with(['location', 'department', 'user'])->findOrFail($request->asset_id);

                // Ambil data tujuan jika diisi
                $targetLocation   = $request->filled('to_location_id') ? AssetLocation::find($request->to_location_id) : null;
                $targetDepartment = $request->filled('to_department_id') ? Department::find($request->to_department_id) : null;
                $targetUser       = $request->filled('to_user_id') ? User::find($request->to_user_id) : null;

                // Tentukan nama snapshot asal
                $fromLocationName   = $asset->location ? $asset->location->name : '-';
                $fromDepartmentName = $asset->department ? $asset->department->name : ($asset->location->accurate_department_name ?? $asset->location->department_name ?? '-');
                $fromUserName       = $asset->user ? $asset->user->name : '-';

                // Tentukan nama snapshot tujuan
                $toLocationName   = $targetLocation ? $targetLocation->name : '-';
                $toDepartmentName = $targetDepartment ? $targetDepartment->name : ($targetLocation->accurate_department_name ?? $targetLocation->department_name ?? '-');
                $toUserName       = $targetUser ? $targetUser->name : '-';

                // Data dasar yang dijamin kolomnya ada di tabel asset_transfers
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

                // Pengecekan dinamis kolom opsional agar tidak error jika belum ada di database
                if (\Schema::hasColumn('asset_transfers', 'from_department_id')) {
                    $dataToInsert['from_department_id'] = $asset->department_id ?? ($asset->location ? $asset->location->department_id ?? null : null);
                }
                if (\Schema::hasColumn('asset_transfers', 'from_user_id')) {
                    $dataToInsert['from_user_id'] = $asset->user_id ?? $asset->assigned_to ?? null;
                }
                if (\Schema::hasColumn('asset_transfers', 'to_department_id')) {
                    $dataToInsert['to_department_id'] = $targetDepartment ? $targetDepartment->id : null;
                }
                if (\Schema::hasColumn('asset_transfers', 'to_user_id')) {
                    $dataToInsert['to_user_id'] = $targetUser ? $targetUser->id : null;
                }

                $transfer = AssetTransfer::create($dataToInsert);

                // Catat log pengajuan mutasi
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

            return redirect()->back()->with('success', 'Draft mutasi berhasil ditambahkan. Silakan lakukan Approval untuk memperbarui data aset.');
        } catch (Throwable $e) {
            Log::error('[AssetTransferController@store] Error:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal membuat draft mutasi: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $transfer = AssetTransfer::findOrFail($id);

        if ($transfer->status === 'completed') {
            return redirect()->back()->with('error', 'Data yang sudah disetujui / selesai tidak dapat diubah.');
        }

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
            DB::transaction(function () use ($request, $transfer) {
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

                $dataToUpdate = [
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
                    'entry_method'         => $request->entry_method ?? $transfer->entry_method,
                    'attachment'           => $request->attachment ?? $transfer->attachment,
                ];

                if (\Schema::hasColumn('asset_transfers', 'from_department_id')) {
                    $dataToUpdate['from_department_id'] = $asset->department_id ?? ($asset->location ? $asset->location->department_id ?? null : null);
                }
                if (\Schema::hasColumn('asset_transfers', 'from_user_id')) {
                    $dataToUpdate['from_user_id'] = $asset->user_id ?? $asset->assigned_to ?? null;
                }
                if (\Schema::hasColumn('asset_transfers', 'to_department_id')) {
                    $dataToUpdate['to_department_id'] = $targetDepartment ? $targetDepartment->id : null;
                }
                if (\Schema::hasColumn('asset_transfers', 'to_user_id')) {
                    $dataToUpdate['to_user_id'] = $targetUser ? $targetUser->id : null;
                }

                $transfer->update($dataToUpdate);
            });

            return redirect()->back()->with('success', 'Data mutasi berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[AssetTransferController@update] Error:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal memperbarui mutasi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $transfer = AssetTransfer::findOrFail($id);

        if ($transfer->status === 'completed') {
            return redirect()->back()->with('error', 'Data mutasi yang sudah selesai tidak boleh dihapus.');
        }

        $transfer->delete();
        return redirect()->back()->with('success', 'Data mutasi berhasil dihapus.');
    }

    public function approve(Request $request, $id)
    {
        // Menggunakan lockForUpdate untuk mengunci baris data dari proses paralel/klik ganda
        $transfer = AssetTransfer::where('id', $id)->lockForUpdate()->firstOrFail();

        if (!in_array($transfer->status, ['draft', 'waiting_approval'])) {
            return redirect()->back()->with('error', 'Status transaksi tidak valid untuk disetujui.');
        }

        try {
            DB::transaction(function () use ($transfer) {
                // 1. Update status transfer menjadi completed
                $transfer->update([
                    'status'      => 'completed',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                $asset = Asset::findOrFail($transfer->asset_id);

                // 2. Update data utama aset berdasarkan nilai tujuan pada transfer
                $assetUpdateData = [];
                if ($transfer->to_location_id) {
                    $assetUpdateData['location_id'] = $transfer->to_location_id;
                }
                if ($transfer->to_department_id) {
                    $assetUpdateData['department_id'] = $transfer->to_department_id;
                }
                if ($transfer->to_user_id) {
                    // Menyesuaikan kolom penanggung jawab di tabel assets (bisa user_id / assigned_to)
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

                // 3. Catat log riwayat aset
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

            // Kirim notifikasi mutasi disetujui
            $this->sendTransferNotification($transfer, 'approved');

            if ($request->routeIs('mobile.*')) {
                return redirect()->route('mobile.asset_transfers.index')->with('success', 'Mutasi disetujui & data aset berhasil diperbarui.');
            }

            return redirect()->back()->with('success', 'Mutasi disetujui & data aset utama berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[AssetTransferController@approve] Error:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menyetujui mutasi: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        // Menggunakan lockForUpdate untuk keamanan transaksi data
        $transfer = AssetTransfer::where('id', $id)->lockForUpdate()->firstOrFail();

        if ($transfer->status === 'completed') {
            return redirect()->back()->with('error', 'Mutasi yang sudah selesai tidak dapat ditolak.');
        }

        $rejectionReason = $request->input('rejection_reason');

        try {
            DB::transaction(function () use ($transfer, $rejectionReason) {
                $transfer->update([
                    'status'           => 'rejected',
                    'rejection_reason' => $rejectionReason
                ]);

                // Catat log penolakan mutasi
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

            // Kirim notifikasi mutasi ditolak
            $this->sendTransferNotification($transfer, 'rejected');

            if ($request->routeIs('mobile.*')) {
                return redirect()->route('mobile.asset_transfers.index')->with('success', 'Mutasi berhasil ditolak.');
            }

            return redirect()->back()->with('success', 'Mutasi berhasil ditolak.');
        } catch (Throwable $e) {
            Log::error('[AssetTransferController@reject] Error:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menolak mutasi: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail mutasi aset.
     */
    public function show(Request $request, AssetTransfer $assetTransfer)
    {
        // Eager Loading Relasi Komprehensif
        $assetTransfer->load([
            'asset.category',
            'fromLocation',
            'toLocation',
            'fromDepartment',
            'toDepartment',
            'fromUser',
            'toUser',
            'creator',
            'approver'
        ]);

        if ($request->routeIs('mobile.*') || $request->wantsJson()) {
            return view('mobile.asset_transfers.show', compact('assetTransfer'));
        }

        return view('admin.asset_transfers.show', compact('assetTransfer'));
    }

    /**
     * Kirim notifikasi terkait transaksi mutasi aset.
     */
    private function sendTransferNotification(AssetTransfer $transfer, string $action): void
    {
        try {
            $recipients = collect();

            // 1. User Pembuat Draft / Pengaju (created_by)
            if ($transfer->created_by) {
                $creator = User::find($transfer->created_by);
                if ($creator) {
                    $recipients->push($creator);
                }
            }

            // 2. User/Penanggung jawab Asal & Tujuan
            if ($transfer->from_user_id) {
                $fromUser = User::find($transfer->from_user_id);
                if ($fromUser) $recipients->push($fromUser);
            }
            if ($transfer->to_user_id) {
                $toUser = User::find($transfer->to_user_id);
                if ($toUser) $recipients->push($toUser);
            }

            // 3. User Login saat ini
            if (auth()->check()) {
                $recipients->push(auth()->user());
            }

            // 4. Jika status 'created', kirim juga ke Admin / Supervisor
            if ($action === 'created') {
                $admins = User::role(['Super Admin', 'Admin Asset', 'Supervisor'], 'web')->get();
                foreach ($admins as $admin) {
                    $recipients->push($admin);
                }
            }

            // 5. Approver
            if ($transfer->approved_by) {
                $approver = User::find($transfer->approved_by);
                if ($approver) {
                    $recipients->push($approver);
                }
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
            Log::error('[AssetTransferController@sendTransferNotification] Gagal mengirim notifikasi:', [
                'transfer_id' => $transfer->id,
                'error'       => $e->getMessage()
            ]);
        }
    }
}
