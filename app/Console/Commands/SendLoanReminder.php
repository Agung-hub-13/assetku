<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssetLoan;
use App\Notifications\LoanDueSoonNotification;
use App\Notifications\LoanOverdueNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendLoanReminder extends Command
{
    protected $signature = 'loan:send-reminders';
    protected $description = 'Mengecek dan mengirimkan notifikasi reminder peminjaman yang mendekati/melewati batas kembali';

    public function handle()
    {
        $startTime = microtime(true);
        Log::info('[Cron][LoanReminder] === START: Prosedur Pengiriman Reminder Peminjaman ===');

        try {
            $dueSoonCount = $this->sendDueSoon();
            $overdueCount = $this->markAndSendOverdue();

            $executionTime = round(microtime(true) - $startTime, 2);

            $this->info("Reminder H-3: {$dueSoonCount} terkirim. Overdue: {$overdueCount} diproses.");
            
            Log::info('[Cron][LoanReminder] === END: Prosedur Selesai ===', [
                'due_soon_sent'   => $dueSoonCount,
                'overdue_processed' => $overdueCount,
                'execution_time_sec' => $executionTime
            ]);

            return Command::SUCCESS;
        } catch (Throwable $e) {
            Log::emergency('[Cron][LoanReminder] Critical error pada saat pengoperasian Cron Reminder:', [
                'error_message' => $e->getMessage(),
                'file'          => $e->getFile(),
                'line'          => $e->getLine(),
                'trace'         => $e->getTraceAsString()
            ]);

            $this->error('Terjadi kesalahan sistem saat menjalankan reminder loan: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function sendDueSoon(): int
    {
        $days   = config('reminder.loan_due_soon_days', 3);
        $target = now()->addDays($days)->toDateString();

        Log::info("[Cron][LoanReminder@sendDueSoon] Memulai kalkulasi target jatuh tempo H-{$days}", [
            'target_date'  => $target,
            'current_date' => now()->toDateString()
        ]);

        $loans = AssetLoan::with(['user', 'asset'])
            ->where('status', 'borrowed')
            ->whereDate('expected_return_date', '<=', $target)
            ->whereDate('expected_return_date', '>=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('reminder_sent_at')
                  ->orWhereDate('reminder_sent_at', '<', now()->toDateString());
            })
            ->get();

        Log::info("[Cron][LoanReminder@sendDueSoon] Ditemukan {$loans->count()} peminjaman yang perlu diingatkan.");

        $sentCount = 0;

        foreach ($loans as $loan) {
            try {
                if (!$loan->user) {
                    Log::warning("[Cron][LoanReminder@sendDueSoon] User tidak ditemukan pada Loan ID: {$loan->id}, lewati pengiriman.");
                    continue;
                }

                $loan->user->notify(new LoanDueSoonNotification($loan));
                $loan->update(['reminder_sent_at' => now()]);

                $sentCount++;

                Log::info("[Cron][LoanReminder@sendDueSoon] Notification Due-Soon terkirim", [
                    'loan_id'              => $loan->id,
                    'loan_number'          => $loan->loan_number,
                    'user_id'              => $loan->user_id,
                    'user_email'           => $loan->user->email ?? 'N/A',
                    'expected_return_date' => $loan->expected_return_date
                ]);
            } catch (Throwable $e) {
                Log::error("[Cron][LoanReminder@sendDueSoon] Gagal mengirim notifikasi pada Loan ID: {$loan->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $sentCount;
    }

    private function markAndSendOverdue(): int
    {
        Log::info('[Cron][LoanReminder@markAndSendOverdue] Memulai pengecekan peminjaman yang terlambat (Overdue)');

        $loans = AssetLoan::with(['user', 'asset'])
            ->where('status', 'borrowed')
            ->whereDate('expected_return_date', '<', now()->toDateString())
            ->get();

        Log::info("[Cron][LoanReminder@markAndSendOverdue] Ditemukan {$loans->count()} peminjaman yang melewati tanggal kembali.");

        $processedCount = 0;

        foreach ($loans as $loan) {
            try {
                $loan->update(['status' => 'overdue']);

                Log::info("[Cron][LoanReminder@markAndSendOverdue] Status Loan ID: {$loan->id} diubah menjadi 'overdue'", [
                    'loan_id'              => $loan->id,
                    'loan_number'          => $loan->loan_number,
                    'expected_return_date' => $loan->expected_return_date
                ]);

                if ($loan->user) {
                    $loan->user->notify(new LoanOverdueNotification($loan));
                    Log::info("[Cron][LoanReminder@markAndSendOverdue] Notification Overdue terkirim ke User ID: {$loan->user_id}");
                } else {
                    Log::warning("[Cron][LoanReminder@markAndSendOverdue] User tidak terikat pada Loan ID: {$loan->id}, notifikasi terlewati.");
                }

                $processedCount++;
            } catch (Throwable $e) {
                Log::error("[Cron][LoanReminder@markAndSendOverdue] Gagal memproses Overdue pada Loan ID: {$loan->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $processedCount;
    }
}