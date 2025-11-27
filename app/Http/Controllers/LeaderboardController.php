<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;

class LeaderboardController extends BaseCrudController
{
    protected LeaderboardService $leaderboardService;
    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    public function getTopUsersByPoints()
    {
        try {
            $topusers = $this->leaderboardService->getTop10UsersByPoints();
            return response()->json([
                "status" => "success",
                "message" => "retrieved successfuly top 10 users",
                "data" => $topusers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failed",
                "message" => "faied due to an error!",
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function getAllUsersByPoints()
    {
        try {
            $allUsersPoints = $this->leaderboardService->getAllTopUsersByPoint();
            return response()->json([
                "status" => "success",
                "message" => "all top users retrieved successfully!",
                "data" => $allUsersPoints,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failed",
                "message" => "faied due to an error!",
                "error" => $e->getMessage(),
            ]);
        }
    }
}
