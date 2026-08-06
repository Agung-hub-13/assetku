<?php

namespace App\Observers;

use App\Models\AssetTransfer;
use App\Models\User;
use App\Notifications\AssetNotification;
use Illuminate\Support\Facades\Notification;

class AssetTransferObserver
{
    public function created(AssetTransfer $transfer): void
    {
        // PERBAIKAN: Menggunakan relasi Spatie Roles
        $recipients = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Super Admin', 'Admin Asset', 'Supervisor']);
        })->get();

        $transfer->loadMissing(['asset', 'toLocation']);

        $assetName  = $transfer->asset->name ?? 'Aset';
        $toLocation = $transfer->toLocation->name ?? ($transfer->to_location_name ?? '-');

        $title   = 'Pengajuan Mutasi Aset';
        $message = "Pengajuan mutasi untuk '{$assetName}' ke {$toLocation} membutuhkan persetujuan.";
        $icon    = 'arrow-left-right';
        $url     = route('admin.asset_transfers.show', $transfer->id);

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new AssetNotification($title, $message, $icon, $url));
        }
    }
}