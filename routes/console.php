<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule recurring event instance generation
Schedule::command('events:generate-recurring --weeks=12')
    ->weekly()
    ->sundays()
    ->at('06:00')
    ->description('Generate recurring event instances for the next 12 weeks');

// Schedule email campaign processing (synchronous)
Schedule::command('campaigns:process')
    ->everyFiveMinutes()
    ->description('Process due email campaign steps');

// Schedule email campaign processing (asynchronous - recommended for production)
Schedule::command('campaigns:process-async')
    ->everyFiveMinutes()
    ->description('Process due email campaign steps asynchronously');

// Schedule birthday and anniversary messages
Schedule::command('messages:send-birthday-anniversary')
    ->daily()
    ->at('09:00')
    ->description('Send birthday and anniversary messages to members');

// Schedule personalized guest follow-up emails (runs every Monday)
Schedule::command('guests:send-personalized-emails')
    ->weekly()
    ->mondays()
    ->at('08:00')
    ->description('Send personalized follow-up emails to guests who registered in the previous week');
