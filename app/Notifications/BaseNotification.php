<?php

namespace App\Notifications;

use App\Mail\AppMail;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
//use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

abstract class BaseNotification extends Notification implements ShouldQueue 
{
    use Queueable;

    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (!empty($notifiable->email) && filter_var($notifiable->email, FILTER_VALIDATE_EMAIL)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    abstract public function toDatabase(object $notifiable): array;

    
    public function toMail(object $notifiable): AppMail
    {
        $data = $this->toDatabase($notifiable);

        return (new AppMail(
            mailSubject: $data['title'] ?? config('app.name') . ' Notification',
            title: $data['title'] ?? 'Notification',
            lines: isset($data['body']) ? [$data['body']] : [],
            action: $data['action_url'] ?? null
                ? ['text' => $data['action_text'] ?? 'View', 'url' => $data['action_url']]
                : null,
            type: $data['type'] ?? 'info',
            footer: 'If you did not expect this email, no action is required.',
        ))->to($notifiable->email);   // <-- ye line add ki
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("notifications.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NotificationSent';
    }
}