<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\BusinessReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class BusinessReviewModerated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BusinessReview $review) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your review was '.$this->review->status->value)
            ->line('Your review for "'.$this->review->business->name.'" is now '.$this->review->status->value.'.')
            ->action('View business', url('/biz/'.$this->review->business->slug));
    }
}
