<?php

namespace App\Services;

use App\Models\AnsweredQuestionsModel;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    public function getTop10UsersByPoints(): array
    {
        $answeredQuestions = AnsweredQuestionsModel::with('users')
            ->select('user_id', DB::raw('SUM(earned_points) as total_points'))
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit(10)
            ->get()
            ->map(function ($item, $index) {
                $userName = $item->users ? $item->users[0]->name : null;
                return [
                    'Rank' => $index + 1, // Key is placed first
                    'user_id' => $item->user_id,
                    'username' => $userName,
                    // 'user' => $item->users, // Include the loaded user relationship
                    'total_points' => (int) $item->total_points, // Ensure total_points is cast to int
                ];
            });

        return $answeredQuestions->toArray();
    }

    public function getAllTopUsersByPoint()
    {
        $answeredQuestions = AnsweredQuestionsModel::with('users')
            ->select('user_id', DB::raw('SUM(earned_points) as total_points'))
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->get()
            ->map(function ($item, $index) {
                $userName = $item->users ? $item->users[0]->name : null;
                return [
                    'Rank' => $index + 1, // Key is placed first
                    'user_id' => $item->user_id,
                    'username' => $userName,
                    // 'user' => $item->users, // Include the loaded user relationship
                    'total_points' => (int) $item->total_points, // Ensure total_points is cast to int
                ];
            });

        return $answeredQuestions->toArray();
    }
}
