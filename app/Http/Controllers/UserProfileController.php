<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Client\Request;
use Symfony\Component\HttpFoundation\Response;

class UserProfileController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get user profile by username
     */
    public function getUserByUsername($username)
    {
        try {
            $user = User::where('username', $username)
                ->orWhere('email', $username)
                ->firstOrFail();

            return $this->successResponse(
                new UserResource($user),
                "User profile retrieved successfully!",
                Response::HTTP_OK,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                "User not found",
                Response::HTTP_NOT_FOUND,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve user profile",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }
    

    /**
     * Get user's badges/achievements by username
     */
    public function getUserBadgesByUsername($username)
    {
        try {
            $user = User::where('username', $username)
                ->orWhere('email', $username)
                ->firstOrFail();

            $badges = $user->userBadges()->with('badge')->get()->map(function ($userBadge) {
                return [
                    'badge_id' => $userBadge->badge->badge_id,
                    'name' => $userBadge->badge->name,
                    'description' => $userBadge->badge->description,
                    'icon' => $userBadge->badge->icon,
                    'type' => $userBadge->badge->type,
                    'color' => $this->getBadgeColor($userBadge->badge->type),
                    'earned_at' => $userBadge->earned_at,
                ];
            });

            return $this->successResponse(
                $badges,
                "User badges retrieved successfully!",
                Response::HTTP_OK,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                "User not found",
                Response::HTTP_NOT_FOUND,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve user badges",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }
    

    /**
     * Get badge color based on type
     */
    protected function getBadgeColor(string $type): string
    {
        return match ($type) {
            'streak' => '#FFD700', // Gold
            'course_completion' => '#4CAF50', // Green
            'points' => '#2196F3', // Blue
            'lesson_completion' => '#FF9800', // Orange
            'time_spent' => '#9C27B0', // Purple
            default => '#607D8B', // Grey
        };
    }

    /**
     * Check if user exists by username
     */
    public function checkUserExists($username)
    {
        try {
            $exists = User::where('username', $username)
                ->orWhere('email', $username)
                ->exists();

            if ($exists) {
                return $this->successResponse(
                    ['exists' => true],
                    "User exists.",
                    Response::HTTP_OK,
                );
            } else {
                return $this->successResponse(
                    ['exists' => false],
                    "User does not exist.",
                    Response::HTTP_NOT_FOUND,
                );
            }
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to check user existence",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }
    

    /**
     * Search users by name or username
     */
    public function searchUsers(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2',
            ]);

            $searchQuery = $request->input('query');

            $users = User::where('name', 'like', "%{$searchQuery}%")
                ->orWhere('username', 'like', "%{$searchQuery}%")
                ->get();

            if ($users->isEmpty()) {
                return $this->successResponse(
                    [],
                    "No users found matching your query.",
                    Response::HTTP_OK,
                );
            }

            return $this->successResponse(
                UserResource::collection($users),
                "Users retrieved successfully!",
                Response::HTTP_OK,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                "Invalid search query.",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                [$e->getMessage()],
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to search users",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }
    
}