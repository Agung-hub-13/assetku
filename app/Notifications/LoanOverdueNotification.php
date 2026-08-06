<?php

namespace App\Notifications;

use App\Models\AssetLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $loan;

    /**
     * Create a new notification instance.
     */
    public function __construct(AssetLoan $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $assetName = $this->loan->asset->name ?? 'Aset';
        $loanNumber = $this->loan->loan_number;
        $expectedDate = \Carbon\Carbon::parse($this->loan->expected_return_date)->format('d M Y');

        return (new MailMessage)
            ->error()
            ->subject("[PERINGATAN] Pengembalian Aset Terlambat - {$loanNumber}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Masa peminjaman aset **{$assetName}** (No. Transaksi: **{$loanNumber}**) telah melewati batas tanggal pengembalian.")
            ->line("Batas Tanggal Kembali: **{$expectedDate}**")
            ->line("Mohon untuk segera mengembalikan aset tersebut ke unit pengelola aset atau melakukan perpanjangan izin peminjaman.")
            ->action('Lihat Detail Peminjaman', route('admin.asset_loans.show', $this->loan->id))
            ->line('Terima kasih atas perhatian dan kerja samanya.');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'loan_id'              => $this->loan->id,
            'loan_number'          => $this->loan->loan_number,
            'asset_id'             => $this->loan->asset_id,
            'asset_name'           => $this->loan->asset->name ?? 'Aset',
            'expected_return_date' => $this->loan->expected_return_date,
            'message'              => "Peminjaman aset {$this->loan->loan_number} telah melewati batas waktu pengembalian.",
            'type'                 => 'overdue'
        ];
    }
}