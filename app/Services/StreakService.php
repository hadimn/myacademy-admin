<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use App\Notifications\StreakWarningNotification;
use App\Notifications\StreakBrokenNotification;
use App\Notifications\StreakMilestoneNotification;
use Illuminate\Support\Facades\Log;

class StreakService
{
    /**
     * Update user's streak when they complete a lesson
     */
    public function updateStreak(User $user): array
    {
        $today = Carbon::today();
        $lastActivity = $user->last_activity_date ? Carbon::parse($user->last_activity_date) : null;

        $notificationsSent = [];
        $previousStreak = $user->current_streak;

        // If no previous activity → start streak at 1
        if (!$lastActivity) {
            $user->current_streak = 1;
        }
        // If last activity was today → do nothing (already updated today)
        elseif ($lastActivity->equalTo($today)) {
            return [];
        }
        // If last activity was yesterday → increment streak (perfect!)
        elseif ($lastActivity->equalTo($today->copy()->subDay())) {
            $user->current_streak += 1;

            // Check for milestone achievements
            $milestoneNotification = $this->checkMilestones($user, $previousStreak);
            if ($milestoneNotification) {
                $notificationsSent[] = $milestoneNotification;
            }
        }
        // FORGIVING: If last activity was 2 days ago → maintain streak but send warning
        elseif ($lastActivity->equalTo($today->copy()->subDays(2))) {
            // Streak remains the same (no increment, no reset)
            // Send warning notification
            $user->notify(new StreakWarningNotification(
                $user,
                $user->current_streak,
                $lastActivity->format('Y-m-d')
            ));
            $notificationsSent[] = 'streak_warning';
        }
        // If gap is more than 2 days → reset streak and send broken notification
        else {
            // Only send broken notification if they had an actual streak
            if ($user->current_streak > 1) {
                $user->notify(new StreakBrokenNotification(
                    $user,
                    $user->current_streak,
                    $user->longest_streak
                ));
                $notificationsSent[] = 'streak_broken';
            }

            $user->current_streak = 1;
        }

        // Update longest streak if current is higher
        if ($user->current_streak > $user->longest_streak) {
            $user->longest_streak = $user->current_streak;
        }

        $user->last_activity_date = $today;
        $user->save();

        return $notificationsSent;
    }

    /**
     * Check and send warnings to users in forgiving period (for scheduled job)
     */
    public function checkAndSendStreakWarnings(): array
    {
        // Get users who last had activity 2 days ago (in forgiving period)
        $usersNeedingWarning = User::where('current_streak', '>', 0)
            ->whereDate('last_activity_date', Carbon::today()->subDays(2))
            ->get();

        $notificationsSent = [];

        foreach ($usersNeedingWarning as $user) {
            try {
                $user->notify(new StreakWarningNotification(
                    $user,
                    $user->current_streak,
                    $user->last_activity_date
                ));
                $notificationsSent[] = $user->id;

                Log::info('Streak warning sent to user', [
                    'user_id' => $user->id,
                    'current_streak' => $user->current_streak,
                    'last_activity' => $user->last_activity_date
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send streak warning to user: ' . $user->id, [
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Streak warning check completed', [
            'total_users_notified' => count($notificationsSent),
            'time' => now()
        ]);

        return $notificationsSent;
    }

    /**
     * Check if user reached any streak milestones
     */
    private function checkMilestones(User $user, int $previousStreak): ?string
    {
        $milestones = [3, 7, 14, 30, 60, 90];

        foreach ($milestones as $milestone) {
            // Check if user just reached this milestone
            if ($previousStreak < $milestone && $user->current_streak >= $milestone) {
                try {
                    $user->notify(new StreakMilestoneNotification(
                        $user,
                        $user->current_streak,
                        $milestone
                    ));

                    Log::info('Streak milestone notification sent', [
                        'user_id' => $user->id,
                        'milestone' => $milestone,
                        'current_streak' => $user->current_streak
                    ]);

                    return "streak_milestone_{$milestone}";
                } catch (\Exception $e) {
                    Log::error('Failed to send milestone notification', [
                        'user_id' => $user->id,
                        'milestone' => $milestone,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return null;
    }

    /**
     * Get detailed streak information for a user
     */
    public function getStreakInfo(User $user): array
    {
        $lastActivity = $user->last_activity_date ? Carbon::parse($user->last_activity_date) : null;
        $daysSinceLastActivity = $lastActivity ? $lastActivity->diffInDays(Carbon::today()) : null;

        $streakStatus = $this->getStreakStatus($user);

        return [
            'current_streak' => $user->current_streak,
            'longest_streak' => $user->longest_streak,
            'last_activity_date' => $user->last_activity_date,
            'days_since_last_activity' => $daysSinceLastActivity,
            'is_streak_active' => $this->isStreakActive($user),
            'is_in_forgiving_period' => $this->isInForgivingPeriod($user),
            'streak_status' => $streakStatus,
            'next_milestone' => $this->getNextMilestone($user),
            'streak_rules' => 'You can miss one day without breaking your streak!',
        ];
    }

    /**
     * Check if user's streak is still active
     */
    private function isStreakActive(User $user): bool
    {
        if (!$user->last_activity_date) {
            return false;
        }

        $lastActivity = Carbon::parse($user->last_activity_date);
        $twoDaysAgo = Carbon::today()->subDays(2);

        return $lastActivity->greaterThanOrEqualTo($twoDaysAgo);
    }

    /**
     * Check if user is in the 1-day forgiving period
     */
    private function isInForgivingPeriod(User $user): bool
    {
        if (!$user->last_activity_date) {
            return false;
        }

        $lastActivity = Carbon::parse($user->last_activity_date);
        $twoDaysAgo = Carbon::today()->subDays(2);

        return $lastActivity->equalTo($twoDaysAgo);
    }

    /**
     * Get detailed streak status
     */
    private function getStreakStatus(User $user): string
    {
        if (!$user->last_activity_date) {
            return 'no_streak';
        }

        $lastActivity = Carbon::parse($user->last_activity_date);
        $today = Carbon::today();

        if ($lastActivity->equalTo($today)) {
            return 'active_today';
        } elseif ($lastActivity->equalTo($today->copy()->subDay())) {
            return 'active_yesterday';
        } elseif ($lastActivity->equalTo($today->copy()->subDays(2))) {
            return 'forgiving_period';
        } else {
            return 'broken';
        }
    }

    /**
     * Get user's next streak milestone
     */
    private function getNextMilestone(User $user): array
    {
        $milestones = [3, 7, 14, 30, 60, 90, 180, 365];

        foreach ($milestones as $milestone) {
            if ($user->current_streak < $milestone) {
                return [
                    'days_required' => $milestone,
                    'days_remaining' => $milestone - $user->current_streak,
                    'progress_percentage' => min(100, round(($user->current_streak / $milestone) * 100))
                ];
            }
        }

        // If user passed all milestones
        return [
            'days_required' => $user->current_streak + 7, // Suggest next milestone
            'days_remaining' => 7,
            'progress_percentage' => 100
        ];
    }

    /**
     * Reset a user's streak (for admin purposes)
     */
    public function resetStreak(User $user): bool
    {
        try {
            $previousStreak = $user->current_streak;

            $user->current_streak = 0;
            $user->last_activity_date = null;
            $user->save();

            Log::info('User streak reset', [
                'user_id' => $user->id,
                'previous_streak' => $previousStreak
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reset user streak', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get streak statistics for dashboard
     */
    public function getStreakStatistics(): array
    {
        $totalUsers = User::count();
        $usersWithStreak = User::where('current_streak', '>', 0)->count();
        $averageStreak = User::where('current_streak', '>', 0)->avg('current_streak');
        $longestCurrentStreak = User::max('current_streak');

        return [
            'total_users' => $totalUsers,
            'users_with_streak' => $usersWithStreak,
            'percentage_with_streak' => $totalUsers > 0 ? round(($usersWithStreak / $totalUsers) * 100, 2) : 0,
            'average_streak_length' => round($averageStreak ?? 0, 2),
            'longest_current_streak' => $longestCurrentStreak ?? 0,
        ];
    }
}
