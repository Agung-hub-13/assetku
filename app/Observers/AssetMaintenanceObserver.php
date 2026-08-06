<?php

namespace App\Observers;

use App\Models\AssetMaintenance;
use App\Models\User;
use App\Notifications\AssetNotification;
use Illuminate\Support\Facades\Notification;

class AssetMaintenanceObserver
{
    public function created(AssetMaintenance $maintenance): void
    {
        // PERBAIKAN: Menggunakan relasi Spatie Roles
        $recipients = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Super Admin', 'Admin Asset', 'Supervisor']);
        })->get();

        $title   = 'Maintenance Baru: ' . $maintenance->ticket_number;
        $message = 'Pengajuan perbaikan baru untuk aset ' . ($maintenance->asset->name ?? 'Aset');
        $icon    = 'wrench';
        $url     = route('admin.asset_maintenances.show', $maintenance->id);

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new AssetNotification($title, $message, $icon, $url));
        }
    }
}