<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class BuilderPackReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Business Starter Pack is ready')
            ->line('Thank you for registering for the Lifepointe GLK Builders community.')
            ->line('Your free Business Starter Pack is ready to download.')
            ->action('Access your pack', route('builders.account'))
            ->line('You can also join our WhatsApp community from your account page.');
    }
}
