<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected string $ticketNumber;
    protected ?int $assetId;
    protected string $url;

    public function __construct(string $title, string $message, string $ticketNumber, ?int $assetId, string $url)
    {
        $this->title        = $title;
        $this->message      = $message;
        $this->ticketNumber = $ticketNumber;
        $this->assetId      = $assetId;
        $this->url          = $url;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title'         => $this->title,
            'message'       => $this->message,
            'ticket_number' => $this->ticketNumber,
            'asset_id'      => $this->assetId,
            'url'           => $this->url,
            'icon'          => 'wrench', // Menambahkan icon bawaan untuk UI
        ];
    }
}