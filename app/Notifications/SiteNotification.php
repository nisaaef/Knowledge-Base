<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notifikasi di dalam dasbor (lonceng di bar atas). Disimpan di database, tidak dikirim lewat email.
 */
class SiteNotification extends Notification
{
    public function __construct(
        public string $title,
        public ?string $body,
        public string $url,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
        ];
    }
}
