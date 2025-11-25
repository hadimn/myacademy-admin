<?php

use App\Services\StreakService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the streak warning command daily at 6:00 PM
Schedule::call(function () {
    $streakService = app(StreakService::class);
    $notifiedUsers = $streakService->checkAndSendStreakWarnings();
})->dailyAt('15:24')->name('send_streak_warnings');

// Optional: Add a morning reminder
Schedule::call(function () {
    $streakService = app(StreakService::class);
    $notifiedUsers = $streakService->checkAndSendStreakWarnings();
})->dailyAt('09:00')->name('morning_streak_reminders');