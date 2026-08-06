<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssetMaintenance;
use App\Notifications\MaintenanceDueSoonNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendMaintenanceReminder extends Command
{
    protected $signature = 'maintenance:send-reminders';
    protected $description = 'Mengecek dan mengirimkan notifikasi reminder maintenance yang mendekati due date';

    public function handle()
    {
        // Ambil kandidat (aktif reminder-nya, belum lewat, belum diingatkan hari ini)
        // lalu saring per H-berapa masing-masing tiket di PHP
        $maintenances = AssetMaintenance::needsReminder()
            ->with(['asset', 'technician', 'reporter'])
            ->get()
            ->filter(function (AssetMaintenance $item) {
                $target = now()->addDays($item->reminder_days_before)->toDateString();
                return $item->due_date->toDateString() <= $target;
            });

        if ($maintenances->isEmpty()) {
            $this->info('Tidak ada reminder maintenance yang perlu dikirim hari ini.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($maintenances as $item) {
            // Notif ke teknisi internal (kalau ada)
            if ($item->technician) {
                $item->technician->notify(new MaintenanceDueSoonNotification($item));
            }

            // Notif tambahan ke email custom (kalau diisi, berbeda dari email login teknisi)
            if ($item->reminder_email) {
                Notification::route('mail', $item->reminder_email)
                    ->notify(new MaintenanceDueSoonNotification($item));
            }

            // Kalau tidak ada teknisi maupun reminder_email, fallback ke pelapor
            if (!$item->technician && !$item->reminder_email && $item->reporter) {
                $item->reporter->notify(new MaintenanceDueSoonNotification($item));
            }

            // Tandai timestamp agar tidak terkirim ganda di hari yang sama
            $item->update([
                'reminder_sent_at' => now(),
            ]);

            $count++;
        }

        $this->info("Berhasil memproses dan mengirim {$count} reminder maintenance.");
        Log::info("Cron Maintenance Reminder: {$count} reminder terkirim.");

        return Command::SUCCESS;
    }
}