<?php

namespace App\Services;

use App\Models\AnsweredQuestionsModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    /**
     * Get top 10 users by total points
     */
    public function getTop10UsersByPoints(?int $authUserId = null): array
    {
        $topUsers = DB::table('answered_questions')
            ->join('users', 'answered_questions.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.username',
                'users.name',
                'users.profile_image',
                'users.current_streak',
                DB::raw('SUM(answered_questions.earned_points) as total_points')
            )
            ->groupBy('users.id', 'users.username', 'users.name', 'users.profile_image', 'users.current_streak')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get()
            ->map(fn($item, $index) => $this->mapLeaderboardRow($item, $index, $authUserId))
            ->toArray();

        return $topUsers;
    }

    /**
     * Get all users by total points
     */
    public function getAllTopUsersByPoint(?int $authUserId = null): array
    {
        $limit = 15; // Set your desired limit here
        $offset = 0; // Set your desired offset here
        
        $allUsers = DB::table('answered_questions')
            ->join('users', 'answered_questions.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.username',
                'users.name',
                'users.profile_image',
                'users.current_streak',
                DB::raw('SUM(answered_questions.earned_points) as total_points')
            )
            ->groupBy('users.id', 'users.username', 'users.name', 'users.profile_image', 'users.current_streak')
            ->orderByDesc('total_points')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(fn($item, $index) => $this->mapLeaderboardRow($item, $index, $authUserId))
            ->toArray();

        return $allUsers;
    }

    /**
     * Get the rank of the authenticated user
     */
    public function getMyRank(User $user): array
    {
        // Get total points for the user
        $userPoints = DB::table('answered_questions')
            ->where('user_id', $user->id)
            ->sum('earned_points');

        // Calculate rank (count users with more points + 1)
        $rank = DB::table('answered_questions')
            ->select('user_id', DB::raw('SUM(earned_points) as total_points'))
            ->groupBy('user_id')
            ->havingRaw('SUM(earned_points) > ?', [$userPoints])
            ->distinct('user_id')
            ->count('user_id') + 1;

        return [
            'user_id' => $user->id,
            'username' => $user->username ?? $user->name,
            'total_points' => (int) $userPoints,
            'rank' => $rank,
            'current_streak' => $user->current_streak ?? 0,
            'longest_streak' => $user->longest_streak ?? 0,
        ];
    }

    /**
     * Get leaderboard statistics
     */
    public function getStats(): array
    {
        $topPointsUser = DB::table('answered_questions')
            ->select('user_id', DB::raw('SUM(earned_points) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->first();

        return [
            'total_learners' => User::count(),
            'top_current_streak_days' => User::max('current_streak') ?? 0,
            'top_total_points_earned' => $topPointsUser?->total ?? 0,
        ];
    }

    /**
     * Map database row to leaderboard format
     */
    private function mapLeaderboardRow($item, int $index, ?int $authUserId): array
    {
        $username = $item->username ?? $item->name ?? 'Unknown User';

        return [
            'rank' => $index + 1,
            'user_id' => $item->id,
            'username' => $username,
            'profile_image' => $item->profile_image? asset('storage/' . $item->profile_image) : null,
            'current_streak' => $item->current_streak ?? 0,
            'total_points' => (int) $item->total_points,
            'is_me' => $authUserId === $item->id,
        ];
    }
}