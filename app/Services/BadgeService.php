<?php

namespace App\Services;

use App\Models\BadgesModel;
use App\Models\User;
use App\Models\UserBadgesModel;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    public function checkAndAwardBadges(User $user): array
    {
        $newlyAwardedBadges = [];

        $newlyAwardedBadges = array_merge(
            $newlyAwardedBadges,
            $this->checkStreakBadges($user)        // For learning streaks
        );

        $newlyAwardedBadges = array_merge(
            $newlyAwardedBadges,
            $this->checkCourseCompletionBadges($user) // For completed courses
        );

        $newlyAwardedBadges = array_merge(
            $newlyAwardedBadges,
            $this->checkPointsBadges($user)        // For earned points
        );

        $newlyAwardedBadges = array_merge(
            $newlyAwardedBadges,
            $this->checkLessonCompletionBadges($user) // For completed lessons
        );

        $newlyAwardedBadges = array_merge(
            $newlyAwardedBadges,
            $this->checkTimeSpentBadges($user)     // For time spent learning
        );

        if (!empty($newlyAwardedBadges)) {
            Log::info('New badges awarded', [
                'user_id' => $user->id,
                'badges_count' => count($newlyAwardedBadges),
                'badge_names' => collect($newlyAwardedBadges)->pluck('name')->toArray()
            ]);
        }

        return $newlyAwardedBadges;
    }

    /**
     * Check for STREAK badges based on user's current streak
     */
    private function checkStreakBadges(User $user): array
    {
        $eligibleBadges = BadgesModel::where('type', 'streak')
            ->where('criteria->days_required', '<=', $user->current_streak)
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return $this->awardBadges($user, $eligibleBadges);
    }

    /**
     * Check for COURSE COMPLETION badges
     */
    private function checkCourseCompletionBadges(User $user): array
    {
        // FIXED: Use enrollments instead of courses relationship
        $completedCourses = $user->enrollments()
            ->whereNotNull('completed_at')
            ->count();

        // FIXED: Changed 'completion' to 'course_completion'
        $eligibleBadges = BadgesModel::where('type', 'course_completion')
            ->where('criteria->courses_required', '<=', $completedCourses)
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return $this->awardBadges($user, $eligibleBadges);
    }

    /**
     * Check for POINTS badges based on total earned points
     */
    private function checkPointsBadges(User $user): array
    {
        $totalEarnedPoints = $user->answeredQuestion()->sum('earned_points');

        // FIXED: Changed whereDoesnt to whereDoesntHave and added ->get()
        $eligibleBadges = BadgesModel::where('type', 'points')
            ->where('criteria->points_required', '<=', $totalEarnedPoints)
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return $this->awardBadges($user, $eligibleBadges);
    }

    /**
     * Check for LESSON COMPLETION badges
     */
    private function checkLessonCompletionBadges(User $user): array
    {
        // Count completed lessons from user_progress table
        $completedLessons = $user->userProgress()
            ->where('is_completed', true)
            ->count();

        $eligibleBadges = BadgesModel::where('type', 'lesson_completion')
            ->where('criteria->lessons_required', '<=', $completedLessons)
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return $this->awardBadges($user, $eligibleBadges);
    }

    /**
     * Check for TIME SPENT badges
     */
    private function checkTimeSpentBadges(User $user): array
    {
        // Calculate total time spent from user_progress table
        $totalTimeSpentMinutes = $user->userProgress()
            ->sum('time_spent') / 60; // Convert seconds to minutes

        $eligibleBadges = BadgesModel::where('type', 'time_spent')
            ->where('criteria->minutes_required', '<=', $totalTimeSpentMinutes)
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return $this->awardBadges($user, $eligibleBadges);
    }

    /**
     * Actually AWARD badges to user by creating user_badges records
     */
    private function awardBadges(User $user, $badges): array
    {
        $awarded = [];

        foreach ($badges as $badge) {
            UserBadgesModel::create([
                'user_id' => $user->id,
                'badge_id' => $badge->badge_id,
                'earned_at' => now(),
            ]);

            $awarded[] = $badge;

            Log::info('Badge awarded to user', [
                'user_id' => $user->id,
                'badge_id' => $badge->badge_id,
                'badge_name' => $badge->name,
            ]);
        }

        return $awarded; // FIXED: Return $awarded instead of empty array
    }

    /**
     * Get all badges earned by a user
     */
    public function getUserBadges(User $user)
    {
        // FIXED: Added return statement
        return $user->badges()->get();
    }

    /**
     * Get badge progress for a specific type
     */
    public function getBadgeProgress(User $user, string $badgeType): array
    {
        switch ($badgeType) {
            case 'streak':
                $current = $user->current_streak;
                $nextBadge = BadgesModel::where('type', 'streak')
                    ->where('criteria->days_required', '>', $current)
                    ->orderBy('criteria->days_required')
                    ->first();
                $required = $nextBadge ? $nextBadge->criteria['days_required'] : $current;
                break;

            case 'course_completion':
                $current = $user->enrollments()->whereNotNull('completed_at')->count();
                $nextBadge = BadgesModel::where('type', 'course_completion')
                    ->where('criteria->courses_required', '>', $current)
                    ->orderBy('criteria->courses_required')
                    ->first();
                $required = $nextBadge ? $nextBadge->criteria['courses_required'] : $current;
                break;

            case 'points':
                $current = $user->answeredQuestion()->sum('earned_points');
                $nextBadge = BadgesModel::where('type', 'points')
                    ->where('criteria->points_required', '>', $current)
                    ->orderBy('criteria->points_required')
                    ->first();
                $required = $nextBadge ? $nextBadge->criteria['points_required'] : $current;
                break;

            case 'lesson_completion':
                $current = $user->userProgress()->where('is_completed', true)->count();
                $nextBadge = BadgesModel::where('type', 'lesson_completion')
                    ->where('criteria->lessons_required', '>', $current)
                    ->orderBy('criteria->lessons_required')
                    ->first();
                $required = $nextBadge ? $nextBadge->criteria['lessons_required'] : $current;
                break;

            case 'time_spent':
                $current = $user->userProgress()->sum('time_spent') / 60; // Convert to minutes
                $nextBadge = BadgesModel::where('type', 'time_spent')
                    ->where('criteria->minutes_required', '>', $current)
                    ->orderBy('criteria->minutes_required')
                    ->first();
                $required = $nextBadge ? $nextBadge->criteria['minutes_required'] : $current;
                break;

            default:
                $current = 0;
                $required = 1;
        }

        return [
            'current' => $current,
            'required' => $required,
            'progress_percentage' => $required > 0 ? min(100, round(($current / $required) * 100)) : 100,
            'next_badge' => $nextBadge ? $nextBadge->only(['name', 'description', 'icon']) : null,
        ];
    }
}