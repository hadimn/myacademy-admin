<?php

namespace App\Listeners\streaks;

use App\Events\streaks\StreakBroken;
use App\Mail\StreakBrokenMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendStreakBrokenEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(StreakBroken $event)
    {
        Mail::to($event->user->email)->send(
            new StreakBrokenMail($event->user, $event->previousStreak, $event->user->longest_streak)
        );
    }
}
