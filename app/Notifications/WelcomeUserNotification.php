<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class WelcomeUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly string $eventName,
        private readonly string $password,
        private readonly bool $isNewUser = true
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'Church Dashboard');
        
        return (new MailMessage)
            ->subject($this->isNewUser ? "Welcome to {$appName} - Your Account Details" : "Event Registration Confirmation - {$appName}")
            ->greeting($this->isNewUser ? "Welcome to {$appName}!" : "Hello {$notifiable->name}!")
            ->line("Thank you for registering for **{$this->eventName}**.")
            ->when($this->isNewUser, function ($message) use ($appName) {
                $message->line("We've created an account for you to access additional church features and manage your event registrations.");
            })
            ->line('**Your Login Credentials:**')
            ->line("**Email:** {$notifiable->email}")
            ->line("**Password:** {$this->password}")
            ->line('')
            ->line('**Important Security Notice:**')
            ->line('• Please log in and change your password immediately after your first login')
            ->line('• Keep your login credentials secure and do not share them with others')
            ->line('• If you suspect unauthorized access, contact us immediately')
            ->line('')
            ->action('Login to Your Account', url('/login'))
            ->line('Once logged in, you can:')
            ->line('• View and manage your event registrations')
            ->line('• Update your profile and contact information')
            ->line('• Explore upcoming church events and programs')
            ->line('• Connect with small groups and ministries')
            ->line('')
            ->line('If you have any questions or need assistance, please don\'t hesitate to contact our support team.')
            ->salutation("Blessings,\nThe {$appName} Team");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_name' => $this->eventName,
            'is_new_user' => $this->isNewUser,
            'action' => 'event_registration_welcome',
        ];
    }
} 