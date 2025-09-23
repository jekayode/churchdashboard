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

        return $this->buildMailMessage($this->resetUrl($notifiable), $notifiable);
    }

    /**
     * Get the reset password notification mail message for the given URL.
     */
    protected function buildMailMessage($url, $notifiable): MailMessage
    {
        $expiration = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        
        return (new MailMessage)
            ->subject('Reset Your LifePointe Church Password')
            ->view('emails.password-reset', [
                'actionUrl' => $url,
                'expiration' => $expiration,
                'user' => $notifiable
            ]);
    }
} 