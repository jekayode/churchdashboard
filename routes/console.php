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
