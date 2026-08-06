<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asset_transfers';

    protected $fillable = [
        'asset_id',
        'transfer_type',
        'from_location_id',
        'to_location_id',
        'from_department_id',
        'to_department_id',
        'from_user_id',
        'to_user_id',
        'from_location_name',
        'from_department_name',
        'from_user_name',
        'to_location_name',
        'to_department_name',
        'to_user_name',
        'transfer_date',
        'document_number',
        'reason',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'attachment',
        'entry_method',
        'accurate_transaction_id',
        'accurate_transaction_no',
        'is_synced',
        'last_synced_at',
        'accurate_last_update',
        'sync_error'
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'approved_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'accurate_last_update' => 'datetime',
        'is_synced' => 'boolean'
    ];

    // ==========================================
    // RELASI ELOQUENT
    // ==========================================

    // Relasi ke Aset yang dimutasi
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    // Relasi ke Lokasi Asal
    public function fromLocation()
    {
        return $this->belongsTo(AssetLocation::class, 'from_location_id');
    }

    // Relasi ke Lokasi Tujuan
    public function toLocation()
    {
        return $this->belongsTo(AssetLocation::class, 'to_location_id');
    }

    // Relasi ke Departemen Asal
    public function fromDepartment()
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    // Relasi ke Departemen Tujuan
    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    // Relasi ke User/Penanggung Jawab Asal
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    // Relasi ke User/Penanggung Jawab Tujuan
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // Relasi ke User pembuat mutasi (created_by)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke User yang menyetujui mutasi (approved_by)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}