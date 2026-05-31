<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\BuilderRegistration;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class BuilderRegistrationReceivedNotification extends Notification
{
    public function __construct(public BuilderRegistration $registration) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Builders Community registration: '.$this->registration->full_name)
            ->line('A new person registered for the Business Starter Pack.')
            ->line('Name: '.$this->registration->full_name)
            ->line('Email: '.$this->registration->email)
            ->line('Business: '.$this->registration->business_name)
            ->action('View registration', route('admin.builders.registrations.show', $this->registration));
    }
}
