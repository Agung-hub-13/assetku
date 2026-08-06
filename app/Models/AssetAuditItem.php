<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAuditItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_audit_id',
        'asset_id',
        'physical_status',
        'scanned_location_name',
        'notes',
    ];

    public function audit()
    {
        return $this->belongsTo(AssetAudit::class, 'asset_audit_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}