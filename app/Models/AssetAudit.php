<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_code',
        'title',
        'location_id',
        'auditor_id',
        'start_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    /**
     * Tentukan key route model binding menggunakan 'audit_code' 
     * alih-alih 'id' default.
     */
    public function getRouteKeyName()
    {
        return 'audit_code';
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function items()
    {
        return $this->hasMany(AssetAuditItem::class, 'asset_audit_id');
    }
}