<?php

namespace App\Http\Controllers;

use App\Services\StreakService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StreakController extends Controller
{
    public function __construct(private StreakService $streakService)
    {
    }

    public function getStreakInfo(Request $request): JsonResponse
    {
        $user = $request->user();
        $streakInfo = $this->streakService->getStreakInfo($user);

        return response()->json([
            'success' => true,
            'data' => $streakInfo
        ]);
    }

    public function updateStreak(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->streakService->updateStreak($user);

        return response()->json([
            'success' => true,
            'message' => 'Streak updated successfully',
            'data' => $this->streakService->getStreakInfo($user)
        ]);
    }
}