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
                $itemArray = $item->toArray();
                $itemArray['order'] = $index + 1;
                return $itemArray;
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
                $itemArray = $item->toArray();
                $itemArray['order'] = $index + 1;
                return $itemArray;
            });

        return $answeredQuestions->toArray();
    }
}
