<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

final class ChurchPasswordResetNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return $this->buildMailMessage($this->resetUrl($notifiable));
    }

    /**
     * Get the reset password notification mail message for the given URL.
     */
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Your Church Dashboard Password')
            ->greeting('Hello from ' . config('app.name') . '!')
            ->line('You are receiving this email because we received a password reset request for your church dashboard account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' minutes.')
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('Blessings,')
            ->salutation(config('app.name') . ' Team')
            ->salutation('*Serving our church community with love and dedication*');
    }
} 