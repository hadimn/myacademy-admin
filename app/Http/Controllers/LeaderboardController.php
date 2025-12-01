<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use Symfony\Component\HttpFoundation\Response;

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
            $allUsersPoints = $this->leaderboardService->getAllTopUsersByPoint();
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
}
