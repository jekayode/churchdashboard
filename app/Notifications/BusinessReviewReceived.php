<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\BusinessReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class BusinessReviewReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BusinessReview $review
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New review received: '.$this->review->business->name)
            ->line('You received a new review for "'.$this->review->business->name.'".')
            ->line('Rating: '.$this->review->rating.' star(s).')
            ->action('View businesses', url('/biz/owner'));
    }
}
