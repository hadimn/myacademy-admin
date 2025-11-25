<?php

namespace App\Http\Controllers;

use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BadgeController extends Controller
{
    public function __construct(private BadgeService $badgeService)
    {
    }

    public function getUserBadges(Request $request): JsonResponse
    {
        $user = $request->user();
        $badges = $this->badgeService->getUserBadges($user);

        return response()->json([
            'success' => true,
            'data' => $badges
        ]);
    }

    public function checkForNewBadges(Request $request): JsonResponse
    {
        $user = $request->user();
        $awardedBadges = $this->badgeService->checkAndAwardBadges($user);

        return response()->json([
            'success' => true,
            'message' => count($awardedBadges) . ' new badges awarded',
            'data' => $awardedBadges
        ]);
    }

    public function getBadgeProgress(Request $request, string $type): JsonResponse
    {
        $user = $request->user();
        $progress = $this->badgeService->getBadgeProgress($user, $type);

        return response()->json([
            'success' => true,
            'data' => $progress
        ]);
    }
}