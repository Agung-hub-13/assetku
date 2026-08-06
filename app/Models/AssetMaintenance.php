<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'asset_id',
        'type',
        'priority',
        'title',
        'description',
        'technician_id',
        'vendor_name',
        'frequency',
        'due_date',
        'start_date',
        'completion_date',
        'reminder_sent_at',
        'is_reminder_active',    // BARU
        'reminder_days_before',  // BARU
        'reminder_email',        // BARU
        'cost_sparepart',
        'cost_service',
        'total_cost',
        'status',
        'reported_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'start_date' => 'date',
        'completion_date' => 'date',
        'reminder_sent_at' => 'datetime',
        'is_reminder_active' => 'boolean', // BARU
        'reminder_days_before' => 'integer', // BARU
        'cost_sparepart' => 'decimal:2',
        'cost_service' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // Relasi
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function scopeNeedsReminder($query)
    {
        return $query->whereIn('status', ['scheduled', 'reported'])
            ->where('is_reminder_active', true)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('reminder_sent_at')
                    ->orWhereDate('reminder_sent_at', '<', now()->toDateString());
            });
    }
}
