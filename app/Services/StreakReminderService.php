<?php

namespace App\Services;

use App\Models\User;
use App\Mail\StreakReminderMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StreakReminderService
{
    /**
     * Send daily reminders to users with active streaks.
     */
    public function sendDailyReminders(): int
    {
        $usersCount = 0;

        User::query()
            ->whereNotNull('email_verified_at')
            ->where('current_streak', '>', 0)
            ->where(function ($query) {
                $query->where('last_activity_date', '>=', Carbon::now()->subDays(2))
                      ->orWhere('current_streak', '>', 1);
            })
            ->chunkById(100, function (Collection $users) use (&$usersCount) {
                $users->each(function (User $user) use (&$usersCount) {
                    if ($this->sendReminderToUser($user)) {
                        $usersCount++;
                    }
                });
            });

        logger()->info("Daily streak reminders sent to {$usersCount} users.");
        return $usersCount;
    }

    /**
     * Send reminder to a specific user.
     */
    private function sendReminderToUser(User $user): bool
    {
        try {
            // Only send if user has a meaningful streak
            if ($user->current_streak > 0) {
                Mail::to($user->email)
                    ->send(new StreakReminderMail(
                        $user, 
                        $user->current_streak, 
                        $user->longest_streak
                    ));
                
                logger()->info("Streak reminder sent to user: {$user->email} (Streak: {$user->current_streak})");
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            logger()->error("Failed to send streak reminder to {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send reminder to specific user (public method for manual triggering).
     */
    public function sendReminderToSpecificUser(User $user): bool
    {
        return $this->sendReminderToUser($user);
    }
}