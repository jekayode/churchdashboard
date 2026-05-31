<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\BusinessMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class BusinessMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BusinessMessage $message) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $business = $this->message->business;

        return (new MailMessage)
            ->subject('New message for '.$business->name)
            ->line('You have received a new message regarding '.$business->name.'.')
            ->line(\Illuminate\Support\Str::limit($this->message->body, 200))
            ->action('View inbox', url('/biz/messages'));
    }
}
