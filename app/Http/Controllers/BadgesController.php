<?php

namespace App\Http\Controllers;

use App\Http\Resources\BadgeResource;
use App\Models\BadgesModel;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BadgesController extends BaseCrudController
{
    private BadgeService $badgeService;

    public function __construct()
    {
        $this->badgeService = app(BadgeService::class);

        $this->model = BadgesModel::class;
        $this->resourceName = "Badge";
        $this->resourceClass = BadgeResource::class;
        $this->searchableFields = [
            'name',
            'description',
            'type',
        ];
        $this->validationRules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:6',
            'icon' => 'nullable|mimes:jpg,jpeg,png|max:60000',
            'type' => 'required|string|in:streak,course_completion,points,lesson_completion,time_spent',
            'criteria' => 'required|json',
            'points' => 'required|integer|min:1',
        ];
        $this->editValidationRules = [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|min:6',
            'icon' => 'nullable|mimes:jpg,jpeg,png|max:60000',
            'type' => 'sometimes|required|string|in:streak,course_completion,points,lesson_completion,time_spent',
            'criteria' => 'sometimes|required|json',
            'points' => 'sometimes|required|integer|min:1',
        ];
        $this->fileFields = [
            'icon',
        ];
    }

    /**
     * Get current user's earned badges
     * URL: GET /api/user/badges/earned
     */
    public function getUserBadges(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $badges = $this->badgeService->getUserBadges($user);

            return $this->successResponse(
                [
                    "badges" => $badges,
                    "badges_count" => $badges->count(),
                ],
                "User badges rettieved successfuly",
                Response::HTTP_OK,
                true,
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e, "Failed to retrieve user badges");
        }
    }

    /**
     * Manually check for new badges for current user
     * URL: POST /api/user/badges/check
     */
    public function checkForNewBadges(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $newBadges = $this->badgeService->checkAndAwardBadges($user);

            $message = count($newBadges) > 0
                ? count($newBadges) . ' new badge' . (count($newBadges) > 1 ? 's' : '') . ' awarded!'
                : 'No new badges at this time. Keep learning!';

            return $this->successResponse(
                [
                    "new_badges" => $newBadges,
                    "count" => count($newBadges),
                ],
                $message,
                Response::HTTP_OK,
                true,
            );
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $newBadges,
                'count' => count($newBadges)
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, "Failed to check for new badges");
        }
    }

    /**
     * Get progress towards next badges for current user
     * URL: GET /api/user/badges/progress/{type}
     */
    public function getBadgeProgress(Request $request, string $type): JsonResponse
    {
        try {
            $user = $request->user();

            // Validate type
            $validTypes = ['streak', 'course_completion', 'points', 'lesson_completion', 'time_spent'];
            if (!in_array($type, $validTypes)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid badge type. Use: ' . implode(', ', $validTypes)
                ], 400);
            }

            $progress = $this->badgeService->getBadgeProgress($user, $type);

            return response()->json([
                'status' => 'success',
                'message' => "Badge progress for {$type} retrieved successfully",
                'data' => $progress
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, "Failed to retrieve badge progress");
        }
    }

    /**
     * Get progress for ALL badge types for current user
     * URL: GET /api/user/badges/progress
     */
    public function getAllBadgeProgress(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $types = ['streak', 'course_completion', 'points', 'lesson_completion', 'time_spent'];

            $progress = [];
            foreach ($types as $type) {
                $progress[$type] = $this->badgeService->getBadgeProgress($user, $type);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'All badge progress retrieved successfully',
                'data' => $progress
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, "Failed to retrieve all badge progress");
        }
    }

    /**
     * Get available badges and user's completion status
     * URL: GET /api/user/badges/available
     */
    public function getAvailableBadges(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get all badges from the system
            $allBadges = BadgesModel::all();

            // Get badges user has already earned
            $earnedBadgeIds = $user->badges()->pluck('user_badges.badge_id')->toArray();

            // Categorize badges
            $categorizedBadges = [
                'earned' => $user->badges()->get(),
                'available' => $allBadges->whereNotIn('badge_id', $earnedBadgeIds)->values(),
                'all' => $allBadges
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Available badges retrieved successfully',
                'data' => $categorizedBadges,
                'stats' => [
                    'total_badges' => $allBadges->count(),
                    'earned_badges' => count($earnedBadgeIds),
                    'completion_percentage' => $allBadges->count() > 0
                        ? round((count($earnedBadgeIds) / $allBadges->count()) * 100, 2)
                        : 0
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, "Failed to retrieve available badges");
        }
    }

    /**
     * Get badges by type (admin or public endpoint)
     * URL: GET /api/badges/type/{type}
     */
    public function getBadgesByType(string $type): JsonResponse
    {
        try {
            $validTypes = ['streak', 'course_completion', 'points', 'lesson_completion', 'time_spent'];
            if (!in_array($type, $validTypes)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid badge type. Use: ' . implode(', ', $validTypes)
                ], 400);
            }

            $badges = BadgesModel::where('type', $type)->get();

            return response()->json([
                'status' => 'success',
                'message' => "Badges of type {$type} retrieved successfully",
                'data' => $badges,
                'count' => $badges->count()
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e, "Failed to retrieve badges by type");
        }
    }
}
