<?php
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the streak reminder command to run daily at 7:00 PM
Schedule::command('streaks:send-reminders')->dailyAt('19:00');


/* 
If you want to run it...	Use this method
Every hour	->hourly()
Daily at 9:00 AM	->dailyAt('9:00')
Twice daily at 1:00 & 13:00	->twiceDaily(1, 13)
Weekly on Monday at 8:00	->weeklyOn(1, '8:00')
*/