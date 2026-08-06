<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\AssetLogController;

class AssetLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asset_loans';

    protected $fillable = [
        'loan_number',
        'user_id',
        'department_id',
        'location_id',
        'asset_id',
        'request_date',
        'start_date',
        'expected_return_date',
        'actual_return_date',
        'condition_before',
        'condition_after',
        'reason',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'reminder_sent_at',
    ];

    protected $casts = [
        'request_date'         => 'date',
        'start_date'           => 'date',
        'expected_return_date' => 'date',
        'actual_return_date'   => 'date',
        'approved_at'          => 'datetime',
        'reminder_sent_at'     => 'datetime',
    ];

    /**
     * Relasi ke Karyawan yang mengajukan peminjaman.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Departemen asal karyawan.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Relasi ke Lokasi Peminjaman.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    /**
     * Relasi ke Aset yang dipinjam.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Relasi ke Admin/Atasan yang menyetujui (approver).
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi ke User yang membuat data peminjaman.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by'); // Sesuaikan 'created_by' dengan nama kolom di database Anda jika berbeda
    }

    protected static function booted()
    {
        static::created(function ($loan) {
            AssetLogController::log(
                $loan->asset_id,
                'create_loan',
                "Pengajuan peminjaman aset dengan nomor {$loan->loan_number} berhasil dibuat.",
                ['attributes' => $loan->getAttributes()]
            );
        });

        static::updated(function ($loan) {
            $changes = $loan->getChanges();
            $original = array_intersect_key($loan->getOriginal(), $changes);

            AssetLogController::log(
                $loan->asset_id,
                'update_loan',
                "Data peminjaman aset ({$loan->loan_number}) diperbarui.",
                ['old' => $original, 'new' => $changes]
            );
        });

        static::deleted(function ($loan) {
            AssetLogController::log(
                $loan->asset_id,
                'delete_loan',
                "Data peminjaman aset ({$loan->loan_number}) dihapus.",
                ['attributes' => $loan->getAttributes()]
            );
        });
    }
}
