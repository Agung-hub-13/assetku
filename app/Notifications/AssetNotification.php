<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssetNotification extends Notification

{
    use Queueable;

    protected $title;
    protected $message;
    protected $icon;
    protected $url;

    public function __construct($title, $message, $icon = 'bell', $url = '#')
    {
        $this->title = $title;
        $this->message = $message;
        $this->icon = $icon; // contoh: 'arrow-left-right', 'wrench', 'shuffle'
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['database']; // Simpan ke tabel notifications
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'url' => $this->url,
        ];
    }
} 

