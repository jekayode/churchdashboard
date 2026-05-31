<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class BusinessStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Business $business,
        public string $action,
        public ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reasonLine = $this->reason
            ? "\nReason: {$this->reason}"
            : '';

        return (new MailMessage)
            ->subject('Business listing update: '.$this->business->name)
            ->line('Your business "'.$this->business->name.'" has been '.$this->action.'.'.$reasonLine)
            ->action('View your businesses', url('/biz/owner'));
    }
}
