<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

final class BuilderAccountActivationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'builders.activate',
            now()->addDays(7),
            ['user' => $notifiable->id]
        );

        return (new MailMessage)
            ->subject('Activate your account and download your Business Starter Pack')
            ->line('Thank you for registering for the Lifepointe GLK Builders community.')
            ->line('Please verify your email and set a password to access your free Business Starter Pack.')
            ->action('Activate account', $url)
            ->line('This link expires in 7 days.');
    }
}
