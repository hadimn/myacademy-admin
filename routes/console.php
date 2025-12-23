<?php

use App\Console\Commands\SendStreakReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Main streak processing - runs daily at 7:00 PM
Schedule::command('streaks:send-reminders')
    ->dailyAt('15:21')
    ->timezone('Africa/Cairo') // Adjust to your timezone
    ->description('Process streaks and send daily reminders');

// to run schedules in local you should run the command:
// php artisan schedule:work

// Optional: Test command that can be run manually
// Schedule::command('streaks:test')
//     ->dailyAt('06:00')
//     ->environments(['local', 'staging']) // Only run in local/staging
//     ->description('Test streak system with sample data');

// // Optional: Backup reminder at 9:00 PM for users in different timezones
// Schedule::command('streaks:send-reminders')
//     ->dailyAt('21:00')
//     ->timezone('Africa/Cairo')
//     ->description('Backup streak reminder for late timezones');

/* 
If you want to run it...	Use this method
Every hour	->hourly()
Daily at 9:00 AM	->dailyAt('9:00')
Twice daily at 1:00 & 13:00	->twiceDaily(1, 13)
Weekly on Monday at 8:00	->weeklyOn(1, '8:00')
*/