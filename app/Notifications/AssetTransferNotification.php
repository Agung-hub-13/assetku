<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssetTransferNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $transferNumber;
    public $assetId;
    public $url;

    public function __construct($title, $message, $transferNumber, $assetId, $url)
    {
        $this->title          = $title;
        $this->message        = $message;
        $this->transferNumber = $transferNumber;
        $this->assetId        = $assetId;
        $this->url            = $url;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type'            => 'transfer', // Digunakan oleh UI Blade untuk menentukan icon (misal: icon arrow-left-right / exchange)
            'title'           => $this->title,
            'message'         => $this->message,
            'transfer_number' => $this->transferNumber,
            'asset_id'        => $this->assetId,
            'url'             => $this->url,
            'icon'            => 'repeat', // Atau pasang class SVG/Heroicon yang digunakan UI Anda
        ];
    }
}