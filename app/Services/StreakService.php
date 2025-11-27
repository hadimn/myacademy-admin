<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class StreakService
{
    public function updateStreak(User $user): void
    {
        $today = Carbon::today();

        $lastActivity = $user->last_activity_date ? Carbon::parse($user->last_activity_date) : null;

        $previousStreak = $user->current_streak;

        if (!$lastActivity) {
            $user->current_streak = 1;
        } elseif ($lastActivity->equalTo($today)) {
            return;
        } // active 1 day ago "yesterday" => then add streak
        elseif ($lastActivity->equalTo($today->copy()->subDay())) {
            $user->current_streak += 1;
        } // active 2 days ago => keep streak as it is "forgiveness"
        elseif ($lastActivity->equalTo($today->copy()->subDay(2))) {
        } // active before more than 2 days => reset current streak
        else {
            $user->current_streak = 1;
        }

        // update longest_streak record
        if($user->current_streak > $user->longest_streak){
            $user->longest_streak = $user->current_streak;
        }

        if($user->current_streak > $previousStreak){
            app(BadgeService::class)->checkAndAwardBadges($user);
        }

        // update last activity date
        $user->last_activity_date = $today;

        // save changes
        $user->save();
    }

    public function getStreakInfo(User $user) : array {
        return [
            'current_streak' => $user->current_streak,
            'longest_streak' => $user->longest_streak,
            'last_activity_date' => $user->last_activity_date,
            'message' => 'You Can Miss One Day Without Breaking Your Streak!',
        ];
    }
}
