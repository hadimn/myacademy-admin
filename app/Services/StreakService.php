<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use App\Mail\StreakForgivenessMail;
use App\Mail\StreakBrokenMail;
use Illuminate\Support\Facades\Mail;

class StreakService
{
    public function updateStreak(User $user): void
    {
        $today = Carbon::today();
        $lastActivity = $user->last_activity_date ? Carbon::parse($user->last_activity_date) : null;
        $previousStreak = $user->current_streak;

        if (!$lastActivity) {
            // First activity ever
            $user->current_streak = 1;
            $this->sendFirstStreakEmail($user);
        } elseif ($lastActivity->equalTo($today)) {
            // Already active today
            return;
        } elseif ($lastActivity->equalTo($today->copy()->subDay())) {
            // Active yesterday - perfect streak continuation
            $user->current_streak += 1;
            $this->sendStreakContinuedEmail($user);
        } elseif ($lastActivity->equalTo($today->copy()->subDays(2))) {
            // Missed one day - forgiveness period
            $this->handleForgivenessDay($user);
        } else {
            // Missed 2 or more days - streak broken
            $this->handleBrokenStreak($user, $previousStreak);
        }

        // Update longest streak record
        if ($user->current_streak > $user->longest_streak) {
            $user->longest_streak = $user->current_streak;
        }

        // Award badges if streak increased
        if ($user->current_streak > $previousStreak) {
            app(BadgeService::class)->checkAndAwardBadges($user);
        }

        // Update last activity date
        $user->last_activity_date = $today;
        $user->save();
    }

    private function handleForgivenessDay(User $user): void
    {
        $previousStreak = $user->current_streak;
        
        // Keep the same streak (no addition), don't reset
        // Streak remains the same, we just don't increment it
        
        // Send forgiveness reminder email
        Mail::to($user->email)->send(new StreakForgivenessMail(
            $user,
            $user->current_streak,
            $user->longest_streak
        ));

        logger()->info("Forgiveness day applied for user: {$user->email}. Streak maintained at: {$user->current_streak}");
    }

    private function handleBrokenStreak(User $user, int $previousStreak): void
    {
        // Reset streak to 1
        $user->current_streak = 1;

        // Send streak broken email
        Mail::to($user->email)->send(new StreakBrokenMail(
            $user,
            $previousStreak,
            $user->longest_streak
        ));

        logger()->info("Streak broken for user: {$user->email}. Previous streak: {$previousStreak}, reset to: 1");
    }

    private function sendFirstStreakEmail(User $user): void
    {
        // Optional: Send welcome email for first streak
        logger()->info("First streak started for user: {$user->email}");
    }

    private function sendStreakContinuedEmail(User $user): void
    {
        // Optional: Send celebration email for streak continuation
        // You can implement this if you want to celebrate milestones
        if ($user->current_streak % 7 === 0) {
            // Celebrate weekly milestones
            logger()->info("User {$user->email} reached {$user->current_streak} days streak!");
        }
    }

    public function getStreakInfo(User $user): array
    {
        $today = Carbon::today();
        $lastActivity = $user->last_activity_date ? Carbon::parse($user->last_activity_date) : null;
        
        $daysSinceLastActivity = $lastActivity ? $lastActivity->diffInDays($today) : null;
        $isInForgivenessPeriod = $daysSinceLastActivity === 1;
        $isStreakBroken = $daysSinceLastActivity >= 2;

        return [
            'current_streak' => $user->current_streak,
            'longest_streak' => $user->longest_streak,
            'last_activity_date' => $user->last_activity_date,
            'days_since_last_activity' => $daysSinceLastActivity,
            'is_in_forgiveness_period' => $isInForgivenessPeriod,
            'is_streak_broken' => $isStreakBroken,
            'message' => $this->getStreakMessage($isInForgivenessPeriod, $isStreakBroken, $user->current_streak),
        ];
    }

    private function getStreakMessage(bool $isInForgiveness, bool $isBroken, int $currentStreak): string
    {
        if ($isInForgiveness) {
            return "You missed one day, but don't worry! Your streak is safe. Continue today to keep your {$currentStreak}-day streak alive! 🛡️";
        } elseif ($isBroken) {
            return "Your streak has been reset. Start a new one today! 💪";
        } else {
            return "You Can Miss One Day Without Breaking Your Streak!";
        }
    }
}