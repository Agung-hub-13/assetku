<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssetTransfer;
use App\Notifications\AssetTransferNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendAssetTransferReminder extends Command
{
    protected $signature = 'transfer:send-reminders';
    protected $description = 'Mengecek dan mengirimkan notifikasi reminder mutasi aset yang mendekati tanggal transfer';

    public function handle()
    {
        // Ambil data transfer status pending/approved yang mendekati tanggal pengiriman
        $transfers = AssetTransfer::with(['asset', 'sender', 'receiver', 'fromLocation', 'toLocation'])
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('transfer_date', '<=', now()->addDays(2)->toDateString())
            ->whereNull('reminder_sent_at') // pastikan kolom ini ada jika ingin mencegah duplikasi
            ->get();

        if ($transfers->isEmpty()) {
            $this->info('Tidak ada reminder transfer aset yang perlu dikirim hari ini.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($transfers as $transfer) {
            // Notif ke Penerima (jika diset)
            if ($transfer->receiver) {
                $transfer->receiver->notify(new AssetTransferNotification($transfer, 'reminder'));
            }

            // Notif ke Pengirim / Pemohon
            if ($transfer->sender) {
                $transfer->sender->notify(new AssetTransferNotification($transfer, 'reminder'));
            }

            // Tandai agar tidak terkirim berulang
            $transfer->update([
                'reminder_sent_at' => now(),
            ]);

            $count++;
        }

        $this->info("Berhasil memproses dan mengirim {$count} reminder transfer aset.");
        Log::info("Cron Transfer Reminder: {$count} reminder terkirim.");

        return Command::SUCCESS;
    }
}