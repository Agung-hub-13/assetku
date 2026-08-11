<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\AssetLogController;
use Carbon\Carbon;

class AssetMaintenanceController extends Controller
{
    /**
     * Menampilkan daftar data maintenance dengan filter dan pagination.
     */
    public function index(Request $request)
    {
        try {
            $query = AssetMaintenance::with(['asset:id,name,asset_code', 'technician:id,name', 'reporter:id,name']);

            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('asset', function ($aq) use ($search) {
                            $aq->where('name', 'like', "%{$search}%")
                                ->orWhere('asset_code', 'like', "%{$search}%");
                        });
                });
            }

            if ($status = $request->get('status')) {
                $query->where('status', $status);
            }

            if ($type = $request->get('type')) {
                $query->where('type', $type);
            }

            $maintenances = $query->latest()->paginate($request->get('per_page', 10));

            return response()->json([
                'success' => true,
                'message' => 'Daftar data maintenance berhasil diambil',
                'data'    => $maintenances
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan data maintenance baru.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        DB::beginTransaction();
        try {
            $ticketNumber = $this->generateTicketNumber();

            $costSparepart = $validated['cost_sparepart'] ?? 0;
            $costService   = $validated['cost_service'] ?? 0;
            $totalCost     = $costSparepart + $costService;

            $startDate      = $validated['start_date'] ?? ($validated['status'] === 'in_progress' ? now()->toDateString() : null);
            $completionDate = $validated['completion_date'] ?? ($validated['status'] === 'completed' ? now()->toDateString() : null);

            $maintenance = AssetMaintenance::create([
                'ticket_number'        => $ticketNumber,
                'asset_id'             => $validated['asset_id'],
                'type'                 => $validated['type'],
                'priority'             => $validated['priority'],
                'title'                => $validated['title'],
                'description'          => $validated['description'] ?? null,
                'technician_id'        => $validated['technician_id'] ?? null,
                'vendor_name'          => $validated['vendor_name'] ?? null,
                'frequency'            => $validated['frequency'] ?? 'none',
                'due_date'             => $validated['due_date'] ?? null,
                'start_date'           => $startDate,
                'completion_date'      => $completionDate,
                'cost_sparepart'       => $costSparepart,
                'cost_service'         => $costService,
                'total_cost'           => $totalCost,
                'status'               => $validated['status'],
                'reported_by'          => auth()->id(),
                'is_reminder_active'   => $request->boolean('is_reminder_active'),
                'reminder_days_before' => $validated['reminder_days_before'] ?? 3,
                'reminder_email'       => $validated['reminder_email'] ?? null,
            ]);

            // Sinkronisasi status pada tabel assets
            $this->syncAssetStatus($validated['asset_id'], $validated['status']);

            // Jika langsung 'completed', buat jadwal rutin berikutnya
            if ($validated['status'] === 'completed') {
                $this->createNextRecurringSchedule($maintenance);
            }

            // Catat Log Aktivitas
            $logAction = $this->mapStatusToLogAction($validated['status']);
            $statusLabel = strtoupper($validated['status']);

            AssetLogController::log(
                $validated['asset_id'],
                $logAction,
                "Tiket Maintenance #{$ticketNumber} dibuat dengan status [{$statusLabel}]: {$validated['title']}",
                [
                    'ticket_number' => $ticketNumber,
                    'type'          => $validated['type'],
                    'priority'      => $validated['priority'],
                    'status'        => $validated['status'],
                    'total_cost'    => $totalCost
                ]
            );

            // Kirim Notifikasi
            $this->sendMaintenanceNotification($maintenance, 'created');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal maintenance berhasil ditambahkan.',
                'data'    => $maintenance->load(['asset', 'technician', 'reporter'])
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail data maintenance tertentu.
     */
    public function show(AssetMaintenance $assetMaintenance)
    {
        try {
            $assetMaintenance->load(['asset', 'technician', 'reporter']);

            return response()->json([
                'success' => true,
                'message' => 'Detail maintenance berhasil diambil',
                'data'    => $assetMaintenance
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Memperbarui data maintenance.
     */
    public function update(Request $request, AssetMaintenance $assetMaintenance)
    {
        $isQuickUpdate = $request->boolean('quick_update');

        if ($isQuickUpdate) {
            $validated = $request->validate([
                'status' => ['required', Rule::in(['reported', 'scheduled', 'in_progress', 'completed', 'cancelled'])],
            ]);
        } else {
            $validated = $this->validateRequest($request);
        }

        DB::beginTransaction();
        try {
            $previousStatus = $assetMaintenance->status;
            $newStatus      = $validated['status'];

            $startDate      = optional($assetMaintenance->start_date)->toDateString();
            $completionDate = optional($assetMaintenance->completion_date)->toDateString();

            if ($newStatus === 'in_progress' && !$startDate) {
                $startDate = now()->toDateString();
            }

            if ($newStatus === 'completed' && !$completionDate) {
                $completionDate = now()->toDateString();
            }

            if (!$isQuickUpdate) {
                $costSparepart  = $validated['cost_sparepart'] ?? 0;
                $costService    = $validated['cost_service'] ?? 0;
                $totalCost      = $costSparepart + $costService;
                $startDate      = $validated['start_date'] ?? $startDate;
                $completionDate = $validated['completion_date'] ?? $completionDate;

                $assetMaintenance->update([
                    'asset_id'             => $validated['asset_id'],
                    'type'                 => $validated['type'],
                    'priority'             => $validated['priority'],
                    'title'                => $validated['title'],
                    'description'          => $validated['description'] ?? null,
                    'technician_id'        => $validated['technician_id'] ?? null,
                    'vendor_name'          => $validated['vendor_name'] ?? null,
                    'frequency'            => $validated['frequency'] ?? 'none',
                    'due_date'             => $validated['due_date'] ?? null,
                    'start_date'           => $startDate,
                    'completion_date'      => $completionDate,
                    'cost_sparepart'       => $costSparepart,
                    'cost_service'         => $costService,
                    'total_cost'           => $totalCost,
                    'status'               => $newStatus,
                    'is_reminder_active'   => $request->boolean('is_reminder_active'),
                    'reminder_days_before' => $validated['reminder_days_before'] ?? 3,
                    'reminder_email'       => $validated['reminder_email'] ?? null,
                ]);
            } else {
                $assetMaintenance->update([
                    'status'          => $newStatus,
                    'start_date'      => $startDate,
                    'completion_date' => $completionDate,
                ]);
            }

            // Sinkronisasi status aset
            $this->syncAssetStatus($assetMaintenance->asset_id, $newStatus);

            // Generate jadwal rutin berikutnya jika status berubah menjadi completed
            if ($previousStatus !== 'completed' && $newStatus === 'completed') {
                $this->createNextRecurringSchedule($assetMaintenance);
            }

            // Catat Log Aktivitas
            $logAction = $this->mapStatusToLogAction($newStatus);
            $oldLabel  = strtoupper($previousStatus);
            $newLabel  = strtoupper($newStatus);

            AssetLogController::log(
                $assetMaintenance->asset_id,
                $logAction,
                "Status Maintenance #{$assetMaintenance->ticket_number} berubah dari [{$oldLabel}] -> [{$newLabel}]",
                [
                    'ticket_number'   => $assetMaintenance->ticket_number,
                    'title'           => $assetMaintenance->title,
                    'previous_status' => $previousStatus,
                    'new_status'      => $newStatus,
                    'total_cost'      => $assetMaintenance->total_cost
                ]
            );

            if ($previousStatus !== $newStatus) {
                $this->sendMaintenanceNotification($assetMaintenance, 'updated', $previousStatus);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status maintenance berhasil diperbarui.',
                'data'    => $assetMaintenance->load(['asset', 'technician', 'reporter'])
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus data maintenance.
     */
    public function destroy(AssetMaintenance $assetMaintenance)
    {
        DB::beginTransaction();
        try {
            AssetLogController::log(
                $assetMaintenance->asset_id,
                'delete',
                "Maintenance #{$assetMaintenance->ticket_number} ({$assetMaintenance->title}) telah dihapus",
                [
                    'ticket_number' => $assetMaintenance->ticket_number,
                    'title'         => $assetMaintenance->title
                ]
            );

            if ($assetMaintenance->status === 'in_progress') {
                Asset::where('id', $assetMaintenance->asset_id)->update(['status' => 'active']);
            }

            $assetMaintenance->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data maintenance berhasil dihapus.'
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'asset_id'             => ['required', 'exists:assets,id'],
            'type'                 => ['required', Rule::in(['routine', 'repair'])],
            'priority'             => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'technician_id'        => ['nullable', 'exists:users,id'],
            'vendor_name'          => ['nullable', 'string', 'max:255'],
            'frequency'            => ['required', Rule::in(['none', 'monthly', 'quarterly', 'yearly'])],
            'due_date'             => ['nullable', 'date'],
            'start_date'           => ['nullable', 'date'],
            'completion_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
            'cost_sparepart'       => ['nullable', 'numeric', 'min:0'],
            'cost_service'         => ['nullable', 'numeric', 'min:0'],
            'status'               => ['required', Rule::in(['reported', 'scheduled', 'in_progress', 'completed', 'cancelled'])],
            'is_reminder_active'   => ['nullable', 'boolean'],
            'reminder_days_before' => ['nullable', 'integer', 'min:1', 'max:30'],
            'reminder_email'       => ['nullable', 'email', 'max:255'],
        ]);
    }

    private function generateTicketNumber(): string
    {
        $todayCode = 'MTC-' . date('Ymd');
        $prefix    = $todayCode . '-';

        $query = AssetMaintenance::withTrashed()
            ->where('ticket_number', 'LIKE', "{$prefix}%");

        if (DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }

        $maxSeq = $query->get()
            ->map(function ($item) use ($prefix) {
                return (int) str_replace($prefix, '', $item->ticket_number);
            })
            ->max() ?? 0;

        $nextSeq = $maxSeq + 1;
        $ticketNumber = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

        while (AssetMaintenance::withTrashed()->where('ticket_number', $ticketNumber)->exists()) {
            $nextSeq++;
            $ticketNumber = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return $ticketNumber;
    }

    private function syncAssetStatus(int $assetId, string $maintenanceStatus): void
    {
        if ($maintenanceStatus === 'in_progress') {
            Asset::where('id', $assetId)->update(['status' => 'maintenance']);
        } elseif (in_array($maintenanceStatus, ['completed', 'cancelled'])) {
            Asset::where('id', $assetId)->update(['status' => 'active']);
        }
    }

    private function mapStatusToLogAction(string $status): string
    {
        return match ($status) {
            'reported'    => 'reported',
            'scheduled'   => 'scheduled',
            'in_progress' => 'maintenance',
            'completed'   => 'completed',
            'cancelled'   => 'cancelled',
            default       => 'update',
        };
    }

    private function createNextRecurringSchedule(AssetMaintenance $maintenance): void
    {
        if ($maintenance->type !== 'routine' || $maintenance->frequency === 'none') {
            return;
        }

        $baseDate = $maintenance->completion_date ?? now();

        $nextDueDate = match ($maintenance->frequency) {
            'monthly'   => Carbon::parse($baseDate)->addMonth(),
            'quarterly' => Carbon::parse($baseDate)->addMonths(3),
            'yearly'    => Carbon::parse($baseDate)->addYear(),
            default     => null,
        };

        if (!$nextDueDate) {
            return;
        }

        $ticketNumber = $this->generateTicketNumber();

        $nextMaintenance = AssetMaintenance::create([
            'ticket_number'        => $ticketNumber,
            'asset_id'             => $maintenance->asset_id,
            'type'                 => 'routine',
            'priority'             => $maintenance->priority,
            'title'                => $maintenance->title,
            'description'          => $maintenance->description,
            'technician_id'        => $maintenance->technician_id,
            'vendor_name'          => $maintenance->vendor_name,
            'frequency'            => $maintenance->frequency,
            'due_date'             => $nextDueDate->toDateString(),
            'status'               => 'scheduled',
            'reported_by'          => auth()->id(),
            'is_reminder_active'   => $maintenance->is_reminder_active,
            'reminder_days_before' => $maintenance->reminder_days_before,
            'reminder_email'       => $maintenance->reminder_email,
        ]);

        AssetLogController::log(
            $maintenance->asset_id,
            'scheduled',
            "Jadwal pemeliharaan rutin berikutnya dibuat otomatis (#{$ticketNumber}) untuk tanggal " . $nextDueDate->format('d/m/Y'),
            [
                'ticket_number' => $ticketNumber,
                'due_date'      => $nextDueDate->toDateString(),
            ]
        );

        $this->sendMaintenanceNotification($nextMaintenance, 'created');
    }

    private function sendMaintenanceNotification(AssetMaintenance $maintenance, string $action, string $previousStatus = null): void
    {
        $recipients = collect();

        if (auth()->check()) {
            $recipients->push(auth()->user());
        }

        if ($maintenance->technician_id) {
            $technician = User::find($maintenance->technician_id);
            if ($technician) {
                $recipients->push($technician);
            }
        }

        if ($maintenance->reported_by) {
            $reporter = User::find($maintenance->reported_by);
            if ($reporter) {
                $recipients->push($reporter);
            }
        }

        $recipients = $recipients->unique('id');

        $assetName = $maintenance->asset ? $maintenance->asset->name : 'Aset';
        $url       = '#'; // Atau URL web jika diperlukan

        if ($action === 'created') {
            $title   = "Jadwal Maintenance Baru (#{$maintenance->ticket_number})";
            $message = "Maintenance '{$maintenance->title}' untuk aset {$assetName} telah dibuat.";
        } else {
            $title   = "Update Maintenance (#{$maintenance->ticket_number})";
            $message = "Status maintenance '{$maintenance->title}' diubah dari " . strtoupper($previousStatus) . " menjadi " . strtoupper($maintenance->status) . ".";
        }

        foreach ($recipients as $user) {
            $user->notify(new \App\Notifications\MaintenanceNotification(
                $title,
                $message,
                $maintenance->ticket_number,
                $maintenance->asset_id,
                $url
            ));
        }
    }
}