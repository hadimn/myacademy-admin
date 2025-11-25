<?php

namespace App\Services;

use App\Models\User;
use App\Models\BadgesModel;
use App\Models\UserBadgesModel;

class BadgeService
{
    public function checkAndAwardBadges(User $user): array
    {
        $awardedBadges = [];

        // Check course completion badges
        $awardedBadges = array_merge($awardedBadges, $this->checkCourseCompletionBadges($user));
        
        // Check streak badges
        $awardedBadges = array_merge($awardedBadges, $this->checkStreakBadges($user));
        
        // Check points badges
        $awardedBadges = array_merge($awardedBadges, $this->checkPointsBadges($user));
        
        // Check question badges
        $awardedBadges = array_merge($awardedBadges, $this->checkQuestionBadges($user));

        return $awardedBadges;
    }

    private function checkCourseCompletionBadges(User $user): array
    {
        $completedCourses = $user->enrollments()
            ->whereNotNull('completed_at')
            ->count();

        $badges = BadgesModel::where('type', 'course_completion')
            ->where('criteria->courses_required', '<=', $completedCourses)
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return $this->awardBadges($user, $badges);
    }

    private function checkStreakBadges(User $user): array
    {
        $badges = BadgesModel::where('type', 'streak')
            ->where('criteria->days_required', '<=', $user->current_streak)
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return $this->awardBadges($user, $badges);
    }

    private function checkPointsBadges(User $user): array
    {
        $totalPoints = $user->answeredQuestion()->sum('earned_points') + 
                      $user->enrollments()->sum('amount_paid'); // If you track points from payments

        $badges = BadgesModel::where('type', 'points')
            ->where('criteria->points_required', '<=', $totalPoints)
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return $this->awardBadges($user, $badges);
    }

    private function checkQuestionBadges(User $user): array
    {
        $answeredQuestions = $user->answeredQuestion()->count();
        $correctAnswers = $user->answeredQuestion()->where('is_passed', true)->count();

        $badges = BadgesModel::whereIn('type', ['questions_answered', 'correct_answers'])
            ->where(function ($query) use ($answeredQuestions, $correctAnswers) {
                $query->where(function ($q) use ($answeredQuestions) {
                    $q->where('type', 'questions_answered')
                      ->where('criteria->questions_required', '<=', $answeredQuestions);
                })->orWhere(function ($q) use ($correctAnswers) {
                    $q->where('type', 'correct_answers')
                      ->where('criteria->correct_answers_required', '<=', $correctAnswers);
                });
            })
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return $this->awardBadges($user, $badges);
    }

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
        }
        return $awarded;
    }

    public function getUserBadges(User $user)
    {
        return $user->badges()->with('badge')->get();
    }

    public function getBadgeProgress(User $user, string $badgeType): array
    {
        switch ($badgeType) {
            case 'course_completion':
                $completed = $user->enrollments()->whereNotNull('completed_at')->count();
                $nextBadge = BadgesModel::where('type', $badgeType)
                    ->where('criteria->courses_required', '>', $completed)
                    ->orderBy('criteria->courses_required')
                    ->first();
                $required = $nextBadge ? $nextBadge->criteria['courses_required'] : $completed;
                break;

            case 'streak':
                $current = $user->current_streak;
                $nextBadge = BadgesModel::where('type', $badgeType)
                    ->where('criteria->days_required', '>', $current)
                    ->orderBy('criteria->days_required')
                    ->first();
                $required = $nextBadge ? $nextBadge->criteria['days_required'] : $current;
                break;

            // Add other badge types as needed
            default:
                $current = 0;
                $required = 1;
        }

        return [
            'current' => $current,
            'required' => $required,
            'progress_percentage' => $required > 0 ? min(100, round(($current / $required) * 100)) : 100,
        ];
    }
}