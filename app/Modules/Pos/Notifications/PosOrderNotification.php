<?php

declare(strict_types=1);

namespace App\Modules\Pos\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PosOrderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly string $reference)
    {
    }

    /**
     * Get the notification channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return match (true) {
            method_exists($notifiable, 'routeNotificationForMail') => ['mail'],
            default => ['database'],
        };
    }

    /**
     * Get the mail representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('PosOrder Update')
            ->line('Reference: ' . $this->reference);
    }

    /**
     * Get the array representation.
     *
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return ['reference' => $this->reference];
    }
}

