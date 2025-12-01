<?php

namespace App\Http\Controllers;

use App\Services\StreakService;
use App\Services\StreakReminderService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StreakController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private StreakService $streakService,
        private StreakReminderService $reminderService
    ) {}

    public function getStreakInfo(Request $request)
    {
        $user = $request->user();
        
        $streakInfo = $this->streakService->getStreakInfo($user);

        return $this->successResponse(
            $streakInfo,
            "successfully retreived" . $user->name . "'s streak info!",
            Response::HTTP_OK,
        );
    }

    /**
     * Manual method to test/send reminder
     */
    public function sendTestReminder(Request $request)
    {
        $user = $request->user();
        
        $success = $this->reminderService->sendReminderToSpecificUser($user);
        
        if ($success) {
            return $this->successResponse(
                [
                    'current_streak' => $user->current_streak,
                    'longest_streak' => $user->longest_streak,
                ],
                "Streak reminder has been sent to your email!",
                Response::HTTP_OK,
            );
        }
        
        return $this->errorResponse(
            'Failed to send streak reminder. Please try again.',
            Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    /**
     * Test streak forgiveness scenario
     */
    public function testForgiveness(Request $request)
    {
        $user = $request->user();
        
        // Simulate missing one day by setting last activity to 2 days ago
        $user->last_activity_date = now()->subDays(2);
        $user->save();
        
        // Update streak to trigger forgiveness logic
        $this->streakService->updateStreak($user);

        return $this->successResponse(
            $this->streakService->getStreakInfo($user),
            'Forgiveness scenario tested. Check your email for forgiveness notification.',
            Response::HTTP_OK,
        );
    }

    /**
     * Test streak broken scenario
     */
    public function testBrokenStreak(Request $request)
    {
        $user = $request->user();
        
        // Simulate missing two days by setting last activity to 3 days ago
        $user->last_activity_date = now()->subDays(3);
        $user->save();
        
        // Update streak to trigger broken streak logic
        $this->streakService->updateStreak($user);

        $this->successResponse(
            $this->streakService->getStreakInfo($user),
            'Broken streak scenario tested. Check your email for streak reset notification.',
            Response::HTTP_OK,
        );
    }
}