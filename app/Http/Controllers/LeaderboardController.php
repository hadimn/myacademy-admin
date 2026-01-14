<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;

class LeaderboardController extends Controller
{
    use ApiResponseTrait;

    protected LeaderboardService $leaderboardService;
    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    public function getTopUsersByPoints()
    {
        try {
            $topusers = $this->leaderboardService->getTop10UsersByPoints(Auth::user()->id);
            return $this->successResponse(
                $topusers,
                "retrieved successfuly top 10 users",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "failed due to an error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    public function getAllUsersByPoints()
    {
        try {
            $allUsersPoints = $this->leaderboardService->getAllTopUsersByPoint(Auth::user()->id);
            return $this->successResponse(
                $allUsersPoints,
                "all top users retrieved successfully!",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "faied due to an error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    // get authinticated user rank:
    public function GetMyRank()
    {
        try {
            $user = Auth::user();
            $rank = $this->leaderboardService->getMyRank($user);
            return $this->successResponse(
                $rank,
                "retrieved successfuly my rank",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {

            return $this->errorResponse(
                "failed due to an error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    // getStats
    public function getStats()
    {
        try {
            $stats = $this->leaderboardService->getStats();
            return $this->successResponse(
                $stats,
                "retrieved successfuly stats",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "failed due to an error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }
}
