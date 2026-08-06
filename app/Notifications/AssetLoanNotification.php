<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssetLoanNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected string $loanNumber;
    protected ?int $assetId;
    protected string $url;

    public function __construct(string $title, string $message, string $loanNumber, ?int $assetId, string $url)
    {
        $this->title      = $title;
        $this->message    = $message;
        $this->loanNumber = $loanNumber;
        $this->assetId    = $assetId;
        $this->url        = $url;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title'       => $this->title,
            'message'     => $this->message,
            'loan_number' => $this->loanNumber,
            'asset_id'    => $this->assetId,
            'url'         => $this->url,
            'icon'        => 'handbag', // Icon penanda untuk transaksi loan/peminjaman
        ];
    }
}