<?php

namespace App\Observers;

use App\Models\AssetLoan;
use App\Models\User;
use App\Notifications\AssetNotification;
use Illuminate\Support\Facades\Notification;

class AssetLoanObserver
{
    public function created(AssetLoan $loan): void
    {
        // PERBAIKAN: Menggunakan relasi Spatie Roles
        $recipients = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Super Admin', 'Admin Asset', 'Supervisor']);
        })->get();

        $title   = 'Pengajuan Peminjaman Aset';
        $message = ($loan->borrower_name ?? 'User') . ' mengajukan peminjaman aset ' . ($loan->asset->name ?? '');
        $icon    = 'arrow-left-right';
        $url     = route('admin.asset_loans.show', $loan->id);

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new AssetNotification($title, $message, $icon, $url));
        }
    }
}