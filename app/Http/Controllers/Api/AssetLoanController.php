<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\AssetLocation;
use App\Notifications\AssetLoanNotification;
use App\Http\Controllers\AssetLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class AssetLoanController extends Controller
{
    /**
     * Menampilkan daftar peminjaman aset (API)
     */
    public function index(Request $request)
    {
        Log::info('[AssetLoanApiController@index] Akses API index peminjaman', [
            'user_id' => auth()->id(),
            'filter_status' => $request->query('status', 'ALL')
        ]);

        try {
            $query = AssetLoan::with(['asset', 'location', 'user', 'creator', 'approver', 'department'])
                ->latest();

            if ($request->filled('status')) {
                $query->whereRaw('LOWER(status) = ?', [strtolower($request->status)]);
            }

            $loans = $query->paginate($request->query('per_page', 10));

            return response()->json([
                'success' => true,
                'message' => 'Daftar peminjaman aset berhasil dimuat.',
                'data'    => $loans
            ], 200);

        } catch (Throwable $e) {
            Log::error('[AssetLoanApiController@index] Exception terjadi:', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan transaksi peminjaman baru (API)
     */
    public function store(Request $request)
    {
        Log::info('[AssetLoanApiController@store] Inisiasi pembuatan pengajuan loan baru via API', [
            'user_id' => auth()->id(),
            'payload' => $request->all()
        ]);

        $validated = $request->validate([
            'user_id'              => ['nullable', 'exists:users,id'],
            'department_id'        => ['nullable', 'exists:departments,id'],
            'location_id'          => ['required', 'exists:asset_locations,id'],
            'asset_id'             => ['required', 'exists:assets,id'],
            'request_date'         => ['required', 'date'],
            'start_date'           => ['required', 'date'],
            'expected_return_date' => ['required', 'date', 'after_or_equal:start_date'],
            'condition_before'     => ['required', Rule::in(['good', 'minor_damage', 'heavy_damage'])],
            'reason'               => ['nullable', 'string'],
            'notes'                => ['nullable', 'string'],
        ]);

        // Cek konflik peminjaman aktif
        $isAssetBusy = AssetLoan::where('asset_id', $validated['asset_id'])
            ->whereIn(DB::raw('LOWER(status)'), ['approved', 'borrowed'])
            ->exists();

        if ($isAssetBusy) {
            Log::warning('[AssetLoanApiController@store] Validasi Gagal: Aset sedang aktif dipinjam', [
                'user_id'  => auth()->id(),
                'asset_id' => $validated['asset_id']
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Aset sedang dipinjam atau dalam proses peminjaman aktif.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $todayCode  = 'LN-' . date('Ymd');
            $lastRecord = AssetLoan::where('loan_number', 'LIKE', "{$todayCode}%")
                ->latest('id')
                ->first();

            $nextSequence = $lastRecord
                ? ((int) substr($lastRecord->loan_number, -4)) + 1
                : 1;

            $loanNumber = $todayCode . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

            $userId = $validated['user_id'] ?? auth()->id();
            $targetUser = User::find($userId);
            $departmentId = $validated['department_id'] ?? ($targetUser->department_id ?? null);

            $loan = AssetLoan::create([
                'loan_number'          => $loanNumber,
                'user_id'              => $userId,
                'department_id'        => $departmentId,
                'location_id'          => $validated['location_id'],
                'asset_id'             => $validated['asset_id'],
                'request_date'         => $validated['request_date'],
                'start_date'           => $validated['start_date'],
                'expected_return_date' => $validated['expected_return_date'],
                'condition_before'     => $validated['condition_before'],
                'reason'               => $validated['reason'] ?? null,
                'notes'                => $validated['notes'] ?? null,
                'status'               => 'pending',
            ]);

            // Catat Log Pengajuan Peminjaman
            if (class_exists(AssetLogController::class)) {
                AssetLogController::log(
                    $validated['asset_id'],
                    'loan_requested',
                    "Pengajuan peminjaman #{$loanNumber} dibuat dengan status [PENDING]",
                    [
                        'loan_number'          => $loanNumber,
                        'borrower_id'          => $userId,
                        'start_date'           => $validated['start_date'],
                        'expected_return_date' => $validated['expected_return_date'],
                        'reason'               => $validated['reason'] ?? null,
                    ]
                );
            }

            DB::commit();

            // Kirim Notifikasi Pengajuan jika method tersedia
            if (method_exists($this, 'sendLoanNotification')) {
                $this->sendLoanNotification($loan, 'created');
            }

            Log::info('[AssetLoanApiController@store] SUKSES: Loan berhasil dibuat', [
                'loan_id'     => $loan->id,
                'loan_number' => $loanNumber,
                'user_id'     => auth()->id(),
                'asset_id'    => $loan->asset_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman berhasil dibuat.',
                'data'    => $loan->load(['asset', 'location', 'user', 'department'])
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('[AssetLoanApiController@store] FAILED: Transaction Rollback pada peminjaman:', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'input'   => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * TAHAP 1: Menyetujui Pengajuan Peminjaman (API)
     */
    public function approve(Request $request, AssetLoan $assetLoan)
    {
        Log::info("[AssetLoanApiController@approve] Inisiasi Persetujuan Loan ID: {$assetLoan->id}", [
            'approver_id'    => auth()->id(),
            'loan_number'    => $assetLoan->loan_number,
            'current_status' => $assetLoan->status
        ]);

        if (strtolower(trim($assetLoan->status)) !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan berstatus pending yang dapat disetujui. Status saat ini: ' . $assetLoan->status
            ], 422);
        }

        try {
            DB::transaction(function () use ($assetLoan) {
                $assetLoan->update([
                    'status'      => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                AssetLogController::log(
                    $assetLoan->asset_id,
                    'loan_approved',
                    "Pengajuan peminjaman #{$assetLoan->loan_number} telah disetujui",
                    [
                        'loan_number' => $assetLoan->loan_number,
                        'approved_by' => auth()->id(),
                        'approved_at' => now()->toDateTimeString(),
                    ]
                );
            });

            $this->sendLoanNotification($assetLoan, 'approved');

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman aset berhasil disetujui.',
                'data'    => $assetLoan
            ], 200);

        } catch (Throwable $e) {
            Log::error("[AssetLoanApiController@approve] ERROR: Gagal menyetujui loan ID: {$assetLoan->id}", [
                'approver_id' => auth()->id(),
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui pengajuan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * TAHAP 1.b: Menolak Pengajuan Peminjaman (API)
     */
    public function reject(Request $request, AssetLoan $assetLoan)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string'],
        ]);

        if (strtolower(trim($assetLoan->status)) !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan berstatus pending yang dapat ditolak. Status saat ini: ' . $assetLoan->status
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $assetLoan) {
                $assetLoan->update([
                    'status'           => 'rejected',
                    'approved_by'      => auth()->id(),
                    'approved_at'      => now(),
                    'rejection_reason' => $request->rejection_reason,
                ]);

                AssetLogController::log(
                    $assetLoan->asset_id,
                    'loan_rejected',
                    "Pengajuan peminjaman #{$assetLoan->loan_number} ditolak. Alasan: {$request->rejection_reason}",
                    [
                        'loan_number'      => $assetLoan->loan_number,
                        'rejected_by'      => auth()->id(),
                        'rejection_reason' => $request->rejection_reason,
                    ]
                );
            });

            $this->sendLoanNotification($assetLoan, 'rejected');

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman aset telah ditolak.',
                'data'    => $assetLoan
            ], 200);

        } catch (Throwable $e) {
            Log::error("[AssetLoanApiController@reject] ERROR: Gagal menolak loan ID: {$assetLoan->id}", [
                'rejector_id' => auth()->id(),
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak pengajuan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * TAHAP 2: Serah Terima Barang ke Peminjam (API)
     */
    public function handover(Request $request, AssetLoan $assetLoan)
    {
        if (strtolower(trim($assetLoan->status)) !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Aset harus disetujui terlebih dahulu sebelum diserahkan.'
            ], 422);
        }

        try {
            DB::transaction(function () use ($assetLoan) {
                $assetLoan->update([
                    'status' => 'borrowed',
                ]);

                $assetLoan->asset()->update(['status' => 'borrowed']);

                AssetLogController::log(
                    $assetLoan->asset_id,
                    'loan_borrowed',
                    "Aset telah diserahkan kepada peminjam via Peminjaman #{$assetLoan->loan_number}. Status aset kini [BORROWED]",
                    [
                        'loan_number' => $assetLoan->loan_number,
                        'borrower_id' => $assetLoan->user_id,
                        'handed_by'   => auth()->id(),
                    ]
                );
            });

            $this->sendLoanNotification($assetLoan, 'handover');

            return response()->json([
                'success' => true,
                'message' => 'Aset berhasil diserahkan kepada peminjam.',
                'data'    => $assetLoan
            ], 200);

        } catch (Throwable $e) {
            Log::error("[AssetLoanApiController@handover] ERROR: Gagal memproses handover Loan ID: {$assetLoan->id}", [
                'actor_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyerahkan aset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * TAHAP 3: Pengembalian Aset (API)
     */
    public function returnAsset(Request $request, AssetLoan $assetLoan)
    {
        $validated = $request->validate([
            'actual_return_date' => ['required', 'date'],
            'condition_after'    => ['required', Rule::in(['good', 'minor_damage', 'heavy_damage', 'lost'])],
            'notes'              => ['nullable', 'string'],
        ]);

        $currentStatus = strtolower(trim($assetLoan->status));
        if (!in_array($currentStatus, ['borrowed', 'overdue'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya aset yang sedang dipinjam atau overdue yang dapat dikembalikan.'
            ], 422);
        }

        try {
            DB::transaction(function () use ($validated, $assetLoan) {
                $assetLoan->update([
                    'actual_return_date' => $validated['actual_return_date'],
                    'condition_after'    => $validated['condition_after'],
                    'status'             => 'returned',
                    'notes'              => $validated['notes'] ?? $assetLoan->notes,
                ]);

                $assetStatus = ($validated['condition_after'] === 'lost') ? 'lost' : 'active';

                $assetLoan->asset()->update([
                    'status'    => $assetStatus,
                ]);

                $conditionLabel = strtoupper(str_replace('_', ' ', $validated['condition_after']));
                $statusLabel    = strtoupper($assetStatus);

                AssetLogController::log(
                    $assetLoan->asset_id,
                    'loan_returned',
                    "Aset dari Peminjaman #{$assetLoan->loan_number} telah dikembalikan. Kondisi akhir: [{$conditionLabel}], Status aset: [{$statusLabel}]",
                    [
                        'loan_number'        => $assetLoan->loan_number,
                        'actual_return_date' => $validated['actual_return_date'],
                        'condition_after'    => $validated['condition_after'],
                        'asset_status'       => $assetStatus,
                        'returned_by_user'   => $assetLoan->user_id,
                        'received_by'        => auth()->id(),
                    ]
                );
            });

            $this->sendLoanNotification($assetLoan, 'returned');

            return response()->json([
                'success' => true,
                'message' => 'Aset telah berhasil dikembalikan.',
                'data'    => $assetLoan
            ], 200);

        } catch (Throwable $e) {
            Log::error("[AssetLoanApiController@returnAsset] ERROR: Gagal memproses pengembalian Loan ID: {$assetLoan->id}", [
                'actor_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pengembalian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detail peminjaman (API)
     */
    public function show(AssetLoan $assetLoan)
    {
        Log::info("[AssetLoanApiController@show] Melihat detail Loan ID: {$assetLoan->id}", ['user_id' => auth()->id()]);

        $assetLoan->load(['asset', 'location', 'user', 'creator', 'approver', 'department']);

        return response()->json([
            'success' => true,
            'message' => 'Detail peminjaman aset.',
            'data'    => $assetLoan
        ], 200);
    }

    /**
     * Update data peminjaman (API)
     */
    public function update(Request $request, AssetLoan $assetLoan)
    {
        $validated = $request->validate([
            'user_id'              => ['nullable', 'exists:users,id'],
            'department_id'        => ['nullable', 'exists:departments,id'],
            'location_id'          => ['required', 'exists:asset_locations,id'],
            'asset_id'             => ['required', 'exists:assets,id'],
            'start_date'           => ['required', 'date'],
            'expected_return_date' => ['required', 'date', 'after_or_equal:start_date'],
            'condition_before'     => ['required', Rule::in(['good', 'bagus', 'minor_damage', 'heavy_damage', 'slightly_damaged', 'severely_damaged'])],
            'status'               => ['required', Rule::in(['pending', 'approved', 'borrowed', 'active', 'returned', 'rejected', 'cancelled', 'overdue'])],
            'condition_after'      => ['nullable', Rule::in(['good', 'slightly_damaged', 'severely_damaged', 'lost', 'minor_damage', 'heavy_damage'])],
            'reason'               => ['nullable', 'string'],
            'notes'                => ['nullable', 'string'],
        ]);

        $oldStatus         = $assetLoan->status;
        $validated['status'] = strtolower($validated['status']);

        if ($validated['status'] === 'returned' && !$assetLoan->actual_return_date) {
            $validated['actual_return_date'] = now();
        }

        try {
            DB::transaction(function () use ($assetLoan, $validated, $oldStatus) {
                $assetLoan->update($validated);

                if ($oldStatus !== $validated['status']) {
                    $oldLabel = strtoupper($oldStatus);
                    $newLabel = strtoupper($validated['status']);

                    if (class_exists(AssetLogController::class)) {
                        AssetLogController::log(
                            $assetLoan->asset_id,
                            'loan_updated',
                            "Peminjaman #{$assetLoan->loan_number} diperbarui: status berubah [{$oldLabel}] -> [{$newLabel}]",
                            [
                                'loan_number' => $assetLoan->loan_number,
                                'old_status'  => $oldStatus,
                                'new_status'  => $validated['status'],
                            ]
                        );
                    }
                }
            });

            if ($oldStatus !== $validated['status'] && method_exists($this, 'sendLoanNotification')) {
                $this->sendLoanNotification($assetLoan, 'updated', $oldStatus);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data peminjaman berhasil diperbarui.',
                'data'    => $assetLoan
            ], 200);

        } catch (\Throwable $e) {
            Log::error("[AssetLoanApiController@update] ERROR: Gagal mengupdate Loan ID: {$assetLoan->id}", [
                'actor_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus Peminjaman (API)
     */
    public function destroy(AssetLoan $assetLoan)
    {
        try {
            DB::transaction(function () use ($assetLoan) {
                AssetLogController::log(
                    $assetLoan->asset_id,
                    'loan_deleted',
                    "Data Peminjaman #{$assetLoan->loan_number} telah dihapus",
                    [
                        'loan_number' => $assetLoan->loan_number,
                        'deleted_by'  => auth()->id(),
                    ]
                );

                $assetLoan->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil dihapus.'
            ], 200);

        } catch (Throwable $e) {
            Log::error("[AssetLoanApiController@destroy] ERROR: Gagal menghapus Loan ID: {$assetLoan->id}", [
                'actor_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper privat untuk mengirimkan notifikasi peminjaman
     */
    private function sendLoanNotification(AssetLoan $loan, string $action, ?string $previousStatus = null): void
    {
        try {
            $recipients = collect();

            if ($loan->user_id) {
                $borrower = User::find($loan->user_id);
                if ($borrower) {
                    $recipients->push($borrower);
                }
            }

            if (auth()->check()) {
                $recipients->push(auth()->user());
            }

            if ($loan->approved_by) {
                $approver = User::find($loan->approved_by);
                if ($approver) {
                    $recipients->push($approver);
                }
            }

            $recipients = $recipients->unique('id');
            $assetName  = $loan->asset ? $loan->asset->name : 'Aset';
            $url        = '#'; // Sesuaikan endpoint frontend jika ada

            switch ($action) {
                case 'created':
                    $title   = "Pengajuan Peminjaman Baru (#{$loan->loan_number})";
                    $message = "Pengajuan peminjaman untuk aset '{$assetName}' telah berhasil dibuat.";
                    break;
                case 'approved':
                    $title   = "Peminjaman Disetujui (#{$loan->loan_number})";
                    $message = "Pengajuan peminjaman untuk aset '{$assetName}' telah DISETUJUI.";
                    break;
                case 'rejected':
                    $title   = "Peminjaman Ditolak (#{$loan->loan_number})";
                    $reason  = $loan->rejection_reason ? " Alasan: {$loan->rejection_reason}" : '';
                    $message = "Pengajuan peminjaman untuk aset '{$assetName}' DITOLAK.{$reason}";
                    break;
                case 'handover':
                    $title   = "Penyerahan Aset (#{$loan->loan_number})";
                    $message = "Aset '{$assetName}' telah diserahkan kepada peminjam.";
                    break;
                case 'returned':
                    $title   = "Pengembalian Aset (#{$loan->loan_number})";
                    $message = "Aset '{$assetName}' telah dikembalikan ke inventaris.";
                    break;
                default:
                    $title   = "Update Peminjaman (#{$loan->loan_number})";
                    $message = "Status peminjaman aset '{$assetName}' diubah dari " . strtoupper($previousStatus ?? '-') . " menjadi " . strtoupper($loan->status) . ".";
                    break;
            }

            foreach ($recipients as $user) {
                $user->notify(new AssetLoanNotification(
                    $title,
                    $message,
                    $loan->loan_number,
                    $loan->asset_id,
                    $url
                ));
            }
        } catch (Throwable $e) {
            Log::error('[AssetLoanApiController@sendLoanNotification] Gagal mengirim notifikasi:', [
                'loan_id' => $loan->id,
                'error'   => $e->getMessage()
            ]);
        }
    }
}