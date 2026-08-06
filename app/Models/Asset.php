<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\AssetLogController;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        /*
        |-------------------------
        | IDENTITAS
        |-------------------------
        */
        'asset_number',
        'asset_code',
        'name',
        'serial_number',
        'description',
        'qr_token',

        /*
        |-------------------------
        | RELASI MASTER
        |-------------------------
        */
        'category_id',
        'location_id',
        'department_id',
        'user_id',

        /*
        |-------------------------
        | KEUANGAN & PEMBELIAN
        |-------------------------
        */
        'purchase_date',
        'quantity',
        'purchase_price',
        'total_price',

        /*
        |-------------------------
        | DEPRESIASI
        |-------------------------
        */
        'depreciation_method',
        'book_value',
        'residual_value',
        'accumulated_depreciation',
        'useful_life_month',

        /*
        |-------------------------
        | MAINTENANCE SCHEDULE
        |-------------------------
        */
        'last_maintenance_date',
        'next_maintenance_date',

        /*
        |-------------------------
        | STATUS & KONDISI
        |-------------------------
        */
        'condition',
        'status',

        /*
        |-------------------------
        | ACCURATE INTEGRATION
        |-------------------------
        */
        'accurate_item_id',
        'accurate_fixed_asset_id',
        'accurate_purchase_id',
        'accurate_db_id',
        'accurate_session',
        'accurate_host',
        'accurate_no',
        'accurate_name',
        'accurate_item_type',

        /*
        |-------------------------
        | SYNC CONTROL
        |-------------------------
        */
        'is_synced',
        'from_accurate',
        'auto_sync',
        'last_synced_at',
        'accurate_last_update',
        'accurate_last_pull_at',
        'accurate_last_push_at',
        'sync_error',
        'accurate_raw_json',
        'accurate_sync_hash',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'last_synced_at' => 'datetime',
        'accurate_last_update' => 'datetime',
        'accurate_last_pull_at' => 'datetime',
        'accurate_last_push_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_synced' => 'boolean',
        'from_accurate' => 'boolean',
        'auto_sync' => 'boolean',
    ];

    /*
    |--------------------------------------
    | RELATIONS
    |--------------------------------------
    */

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transfers()
    {
        return $this->hasMany(AssetTransfer::class, 'asset_id');
    }

    public function transfer()
    {
        return $this->hasOne(AssetTransfer::class, 'asset_id')->latestOfMany();
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function getIsFullyDepreciatedAttribute()
    {
        return $this->useful_life_month <= 0 && $this->book_value <= 0;
    }

    protected static function booted()
    {
        static::created(function ($asset) {
            AssetLogController::log(
                $asset->id,
                'create',
                "Aset '{$asset->name}' berhasil ditambahkan.",
                ['attributes' => $asset->getAttributes()]
            );
        });

        static::updated(function ($asset) {
            $changes = $asset->getChanges();
            $original = array_intersect_key($asset->getOriginal(), $changes);

            AssetLogController::log(
                $asset->id,
                'update',
                "Aset '{$asset->name}' diperbarui.",
                ['old' => $original, 'new' => $changes]
            );
        });

        static::deleted(function ($asset) {
            AssetLogController::log(
                $asset->id,
                'delete',
                "Aset '{$asset->name}' dihapus.",
                ['attributes' => $asset->getAttributes()]
            );
        });
    }
}