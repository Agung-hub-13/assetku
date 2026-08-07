<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\AssetLocation;
use App\Models\Department; // Pastikan model Department di-import jika ada, atau gunakan relasi
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
     * Menampilkan daftar peminjaman aset[cite: 4]
     */
    public function index(Request $request)
    {
        Log::info('[AssetLoanController@index] Akses halaman index peminjaman', [
            'user_id' => auth()->id(),
            'filter_status' => $request->query('status', 'ALL')
        ]);

        try {
            $query = AssetLoan::with(['asset', 'location', 'user', 'creator', 'approver', 'department'])
                ->latest();

            if ($request->filled('status')) {
                $query->whereRaw('LOWER(status) = ?', [strtolower($request->status)]);
            }

            $loans = $query->paginate(10);
            $locations = AssetLocation::where('status', 'active')->get();

            // BATASI pengambilan aset agar tidak meload belasan ribu data sekaligus ke RAM
            $assets = Asset::whereRaw('LOWER(status) = ?', ['available'])->limit(100)->get();

            if ($assets->isEmpty()) {
                Log::warning('[AssetLoanController@index] Tidak ada aset dengan status available. Mengambil sebagian daftar aset.');
                $assets = Asset::limit(100)->get();
            }

            // Batasi juga user jika jumlahnya banyak, atau biarkan jika tabel user masih sedikit
            $users = User::limit(100)->get();
            $departments = class_exists(\App\Models\Department::class) ? \App\Models\Department::all() : collect();

            return view('admin.asset_loans.index', compact('loans', 'locations', 'assets', 'users', 'departments'));
        } catch (Throwable $e) {
            Log::error('[AssetLoanController@index] Exception terjadi:', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal memuat halaman: ' . $e->getMessage());
        }
    }

    /**
     * Form tambah peminjaman[cite: 4]
     */
    public function create()
    {
        Log::info('[AssetLoanController@create] Membuka form buat peminjaman baru', ['user_id' => auth()->id()]);

        $assets      = Asset::whereRaw('LOWER(status) = ?', ['available'])->get();
        $locations   = AssetLocation::where('status', 'active')->get();

        // Pemanggilan data tambahan untuk user_id, department_id, dan location_id ke form blade
        $users       = User::all();
        $departments = class_exists(\App\Models\Department::class) ? \App\Models\Department::all() : collect();

        return view('admin.asset_loans.create', compact('assets', 'locations', 'users', 'departments'));
    }

    /**
     * Menyimpan transaksi peminjaman baru[cite: 4]
     */
    public function store(Request $request)
    {
        Log::info('[AssetLoanController@store] Inisiasi pembuatan pengajuan loan baru', [
            'user_id' => auth()->id(),
            'payload' => $request->except(['_token'])
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

        // Cek konflik peminjaman aktif[cite: 4]
        $isAssetBusy = AssetLoan::where('asset_id', $validated['asset_id'])
            ->whereIn(DB::raw('LOWER(status)'), ['approved', 'borrowed'])
            ->exists();

        if ($isAssetBusy) {
            Log::warning('[AssetLoanController@store] Validasi Gagal: Aset sedang aktif dipinjam', [
                'user_id'  => auth()->id(),
                'asset_id' => $validated['asset_id']
            ]);

            return back()->withInput()->with('error', 'Aset sedang dipinjam atau dalam proses peminjaman aktif.');
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

            // Gunakan user input jika ada (misal diinput admin dari form), jika tidak gunakan auth()->user()
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

            // Catat Log Pengajuan Peminjaman[cite: 4]
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

            // Kirim Notifikasi Pengajuan jika method tersedia[cite: 4]
            if (method_exists($this, 'sendLoanNotification')) {
                $this->sendLoanNotification($loan, 'created');
            }

            Log::info('[AssetLoanController@store] SUKSES: Loan berhasil dibuat', [
                'loan_id'     => $loan->id,
                'loan_number' => $loanNumber,
                'user_id'     => auth()->id(),
                'asset_id'    => $loan->asset_id,
            ]);

            return redirect()->route('admin.asset_loans.index')
                ->with('success', 'Pengajuan peminjaman berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('[AssetLoanController@store] FAILED: Transaction Rollback pada peminjaman:', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'input'   => $request->except(['_token'])
            ]);

            return back()->withInput()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * TAHAP 1: Menyetujui Pengajuan Peminjaman[cite: 4]
     */
    public function approve(Request $request, AssetLoan $assetLoan)
    {
        Log::info("[AssetLoanController@approve] Inisiasi Persetujuan Loan ID: {$assetLoan->id}", [
            'approver_id'    => auth()->id(),
            'loan_number'    => $assetLoan->loan_number,
            'current_status' => $assetLoan->status
        ]);

        if (strtolower(trim($assetLoan->status)) !== 'pending') {
            Log::warning("[AssetLoanController@approve] APPROVAL CANCELLED: Status loan bukan pending.", [
                'loan_id' => $assetLoan->id,
                'status'  => $assetLoan->status
            ]);
            return back()->with('error', 'Hanya pengajuan berstatus pending yang dapat disetujui. Status saat ini: ' . $assetLoan->status);
        }

        try {
            DB::transaction(function () use ($assetLoan) {
                $assetLoan->update([
                    'status'      => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // Catat Log Persetujuan[cite: 4]
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

            // Kirim Notifikasi Disetujui[cite: 4]
            $this->sendLoanNotification($assetLoan, 'approved');

            Log::info("[AssetLoanController@approve] SUKSES: Loan ID: {$assetLoan->id} disetujui.", [
                'approver_id' => auth()->id(),
                'loan_number' => $assetLoan->loan_number
            ]);

            return back()->with('success', 'Pengajuan peminjaman aset berhasil disetujui.');
        } catch (Throwable $e) {
            Log::error("[AssetLoanController@approve] ERROR: Gagal menyetujui loan ID: {$assetLoan->id}", [
                'approver_id' => auth()->id(),
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal menyetujui pengajuan: ' . $e->getMessage());
        }
    }

    /**
     * TAHAP 1.b: Menolak Pengajuan Peminjaman[cite: 4]
     */
    public function reject(Request $request, AssetLoan $assetLoan)
    {
        Log::info("[AssetLoanController@reject] Inisiasi Penolakan Loan ID: {$assetLoan->id}", [
            'rejector_id' => auth()->id(),
            'loan_number' => $assetLoan->loan_number,
            'reason'      => $request->input('rejection_reason')
        ]);

        $request->validate([
            'rejection_reason' => ['required', 'string'],
        ]);

        if (strtolower(trim($assetLoan->status)) !== 'pending') {
            Log::warning("[AssetLoanController@reject] REJECTION CANCELLED: Status bukan pending.", [
                'loan_id' => $assetLoan->id,
                'status'  => $assetLoan->status
            ]);
            return back()->with('error', 'Hanya pengajuan berstatus pending yang dapat ditolak. Status saat ini: ' . $assetLoan->status);
        }

        try {
            DB::transaction(function () use ($request, $assetLoan) {
                $assetLoan->update([
                    'status'           => 'rejected',
                    'approved_by'      => auth()->id(),
                    'approved_at'      => now(),
                    'rejection_reason' => $request->rejection_reason,
                ]);

                // Catat Log Penolakan[cite: 4]
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

            // Kirim Notifikasi Penolakan[cite: 4]
            $this->sendLoanNotification($assetLoan, 'rejected');

            Log::info("[AssetLoanController@reject] SUKSES: Loan ID: {$assetLoan->id} ditolak.", [
                'rejector_id' => auth()->id(),
                'loan_number' => $assetLoan->loan_number
            ]);

            return back()->with('success', 'Pengajuan peminjaman aset telah ditolak.');
        } catch (Throwable $e) {
            Log::error("[AssetLoanController@reject] ERROR: Gagal menolak loan ID: {$assetLoan->id}", [
                'rejector_id' => auth()->id(),
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal menolak pengajuan: ' . $e->getMessage());
        }
    }

    /**
     * TAHAP 2: Serah Terima Barang ke Peminjam (Status -> borrowed)[cite: 4]
     */
    public function handover(Request $request, AssetLoan $assetLoan)
    {
        Log::info("[AssetLoanController@handover] Inisiasi Handover Barang untuk Loan ID: {$assetLoan->id}", [
            'actor_id'    => auth()->id(),
            'asset_id'    => $assetLoan->asset_id,
            'loan_number' => $assetLoan->loan_number,
            'status'      => $assetLoan->status
        ]);

        if (strtolower(trim($assetLoan->status)) !== 'approved') {
            Log::warning("[AssetLoanController@handover] HANDOVER CANCELLED: Status loan bukan 'approved'.", [
                'loan_id' => $assetLoan->id,
                'status'  => $assetLoan->status
            ]);
            return back()->with('error', 'Aset harus disetujui terlebih dahulu sebelum diserahkan.');
        }

        try {
            DB::transaction(function () use ($assetLoan) {
                $assetLoan->update([
                    'status' => 'borrowed',
                ]);

                // Update status master aset menjadi borrowed[cite: 4]
                $assetLoan->asset()->update(['status' => 'borrowed']);

                // Catat Log Penyerahan Aset[cite: 4]
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

            // Kirim Notifikasi Penyerahan Barang[cite: 4]
            $this->sendLoanNotification($assetLoan, 'handover');

            Log::info("[AssetLoanController@handover] SUKSES: Handover selesai.", [
                'actor_id'     => auth()->id(),
                'loan_id'      => $assetLoan->id,
                'asset_id'     => $assetLoan->asset_id,
                'asset_status' => 'borrowed'
            ]);

            return back()->with('success', 'Aset berhasil diserahkan kepada peminjam.');
        } catch (Throwable $e) {
            Log::error("[AssetLoanController@handover] ERROR: Gagal meproses handover Loan ID: {$assetLoan->id}", [
                'actor_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal menyerahkan aset: ' . $e->getMessage());
        }
    }

    /**
     * TAHAP 3: Pengembalian Aset (Status -> returned)[cite: 4]
     */
    public function returnAsset(Request $request, AssetLoan $assetLoan)
    {
        Log::info("[AssetLoanController@returnAsset] Inisiasi pengembalian aset Loan ID: {$assetLoan->id}", [
            'actor_id' => auth()->id(),
            'payload'  => $request->except(['_token'])
        ]);

        $validated = $request->validate([
            'actual_return_date' => ['required', 'date'],
            'condition_after'    => ['required', Rule::in(['good', 'minor_damage', 'heavy_damage', 'lost'])],
            'notes'              => ['nullable', 'string'],
        ]);

        $currentStatus = strtolower(trim($assetLoan->status));
        if (!in_array($currentStatus, ['borrowed', 'overdue'])) {
            Log::warning("[AssetLoanController@returnAsset] RETURN CANCELLED: Status loan tidak valid untuk dikembalikan.", [
                'loan_id' => $assetLoan->id,
                'status'  => $assetLoan->status
            ]);
            return back()->with('error', 'Hanya aset yang sedang dipinjam atau overdue yang dapat dikembalikan.');
        }

        try {
            DB::transaction(function () use ($validated, $assetLoan) {
                // 1. Update data peminjaman[cite: 4]
                $assetLoan->update([
                    'actual_return_date' => $validated['actual_return_date'],
                    'condition_after'    => $validated['condition_after'],
                    'status'             => 'returned',
                    'notes'              => $validated['notes'] ?? $assetLoan->notes,
                ]);

                // 2. Tentukan enum status aset PostgreSQL[cite: 4]
                $assetStatus = ($validated['condition_after'] === 'lost') ? 'lost' : 'active';

                // 3. Update master aset[cite: 4]
                $assetLoan->asset()->update([
                    'status'    => $assetStatus,
                ]);

                // Catat Log Pengembalian Aset[cite: 4]
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

            // Kirim Notifikasi Pengembalian[cite: 4]
            $this->sendLoanNotification($assetLoan, 'returned');

            Log::info("[AssetLoanController@returnAsset] SUKSES: Pengembalian Aset Berhasil.", [
                'actor_id'            => auth()->id(),
                'loan_id'             => $assetLoan->id,
                'asset_id'            => $assetLoan->asset_id,
                'actual_return_date'  => $validated['actual_return_date'],
                'new_asset_status'    => $validated['condition_after'] === 'lost' ? 'lost' : 'active',
                'new_asset_condition' => $validated['condition_after']
            ]);

            return back()->with('success', 'Aset telah berhasil dikembalikan.');
        } catch (Throwable $e) {
            Log::error("[AssetLoanController@returnAsset] ERROR: Gagal memproses pengembalian Loan ID: {$assetLoan->id}", [
                'actor_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    /**
     * Detail peminjaman[cite: 4]
     */
    public function show(AssetLoan $assetLoan)
    {
        Log::info("[AssetLoanController@show] Melihat detail Loan ID: {$assetLoan->id}", ['user_id' => auth()->id()]);

        $assetLoan->load(['asset', 'location', 'user', 'creator', 'approver', 'department']);
        return view('admin.asset_loans.show', compact('assetLoan'));
    }

    /**
     * Form edit peminjaman[cite: 4]
     */
    public function edit(AssetLoan $assetLoan)
    {
        Log::info("[AssetLoanController@edit] Membuka form edit Loan ID: {$assetLoan->id}", ['user_id' => auth()->id()]);

        $assets      = Asset::all();
        $locations   = AssetLocation::all();
        $users       = User::all();
        $departments = class_exists(\App\Models\Department::class) ? \App\Models\Department::all() : collect();

        return view('admin.asset_loans.edit', compact('assetLoan', 'assets', 'locations', 'users', 'departments'));
    }

    /**
     * Update data peminjaman[cite: 4]
     */
    public function update(Request $request, AssetLoan $assetLoan)
    {
        Log::info("[AssetLoanController@update] Memperbarui data Loan ID: {$assetLoan->id}", [
            'actor_id' => auth()->id(),
            'payload'  => $request->except(['_token', '_method'])
        ]);

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

        $oldStatus           = $assetLoan->status;
        $validated['status'] = strtolower($validated['status']);

        // Jika dikembalikan, set tanggal pengembalian aktual jika belum ada[cite: 4]
        if ($validated['status'] === 'returned' && !$assetLoan->actual_return_date) {
            $validated['actual_return_date'] = now();
        }

        try {
            DB::transaction(function () use ($assetLoan, $validated, $oldStatus) {
                $assetLoan->update($validated);

                // Logging aktivitas jika ada perubahan status[cite: 4]
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

            // Kirim notifikasi jika status berubah dan method tersedia[cite: 4]
            if ($oldStatus !== $validated['status'] && method_exists($this, 'sendLoanNotification')) {
                $this->sendLoanNotification($assetLoan, 'updated', $oldStatus);
            }

            Log::info("[AssetLoanController@update] SUKSES: Loan ID: {$assetLoan->id} berhasil diperbarui.");

            return redirect()->route('admin.asset_loans.index')
                ->with('success', 'Data peminjaman berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error("[AssetLoanController@update] ERROR: Gagal mengupdate Loan ID: {$assetLoan->id}", [
                'actor_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Peminjaman[cite: 4]
     */
    public function destroy(AssetLoan $assetLoan)
    {
        Log::warning("[AssetLoanController@destroy] Percobaan HAPUS Loan ID: {$assetLoan->id}", [
            'actor_id'    => auth()->id(),
            'loan_number' => $assetLoan->loan_number
        ]);

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

            Log::info("[AssetLoanController@destroy] SUKSES: Loan ID: {$assetLoan->id} berhasil dihapus (Soft Delete).");

            return redirect()->route('admin.asset_loans.index')
                ->with('success', 'Peminjaman berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error("[AssetLoanController@destroy] ERROR: Gagal menghapus Loan ID: {$assetLoan->id}", [
                'actor_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Helper privat untuk mengirimkan notifikasi peminjaman[cite: 4]
     */
    private function sendLoanNotification(AssetLoan $loan, string $action, ?string $previousStatus = null): void
    {
        try {
            $recipients = collect();

            // 1. User Pembuat/Peminjam[cite: 4]
            if ($loan->user_id) {
                $borrower = User::find($loan->user_id);
                if ($borrower) {
                    $recipients->push($borrower);
                }
            }

            // 2. User yang Sedang Login (Admin/Staff yang melakukan tindakan)[cite: 4]
            if (auth()->check()) {
                $recipients->push(auth()->user());
            }

            // 3. Approver (jika ada)[cite: 4]
            if ($loan->approved_by) {
                $approver = User::find($loan->approved_by);
                if ($approver) {
                    $recipients->push($approver);
                }
            }

            $recipients = $recipients->unique('id');
            $assetName  = $loan->asset ? $loan->asset->name : 'Aset';
            $url        = route('admin.asset_loans.show', $loan->id);

            // Tentukan Judul dan Pesan Notifikasi berdasarkan aksi[cite: 4]
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

            // Kirim notifikasi menggunakan class AssetLoanNotification[cite: 4]
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
            // Log error agar pengiriman notifikasi tidak membatalkan transaksi utama[cite: 4]
            Log::error('[AssetLoanController@sendLoanNotification] Gagal mengirim notifikasi:', [
                'loan_id' => $loan->id,
                'error'   => $e->getMessage()
            ]);
        }
    }
}
