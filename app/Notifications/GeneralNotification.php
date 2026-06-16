<?php

namespace App\Notifications;

class GeneralNotification extends BaseNotification
{
    public function __construct(
        public string  $message,
        public string  $type = 'info',
        public ?string $url = null,
        public ?string $title = null,
    ) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => $this->title ?? ucfirst($this->type),
            'message' => $this->message,
            'type'    => $this->type,
            'url'     => $this->url,
        ];
    }
    // toBroadcast BaseNotification se inherit hogi ✅
}