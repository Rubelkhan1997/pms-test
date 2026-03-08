<?php

declare(strict_types=1);

namespace App\Modules\Reports\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportReadyNotification extends Notification
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
            ->subject('ReportSnapshot Update')
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

