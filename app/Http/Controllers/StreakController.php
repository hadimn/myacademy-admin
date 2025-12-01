<?php

namespace App\Http\Controllers;

use App\Services\StreakService;
use App\Services\StreakReminderService;
use Illuminate\Http\Request;

class StreakController extends Controller
{
    public function __construct(
        private StreakService $streakService,
        private StreakReminderService $reminderService
    ) {}

    public function getStreakInfo(Request $request)
    {
        $user = $request->user();
        
        $streakInfo = $this->streakService->getStreakInfo($user);

        return response()->json([
            'status' => 'success',
            'data' => $streakInfo,
        ]);
    }

    /**
     * Manual method to test/send reminder
     */
    public function sendTestReminder(Request $request)
    {
        $user = $request->user();
        
        $success = $this->reminderService->sendReminderToSpecificUser($user);
        
        if ($success) {
            return response()->json([
                'status' => 'success',
                'message' => 'Streak reminder sent to your email!',
                'data' => [
                    'current_streak' => $user->current_streak,
                    'longest_streak' => $user->longest_streak,
                ]
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send streak reminder. Please try again.',
        ], 500);
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
        
        return response()->json([
            'status' => 'success',
            'message' => 'Forgiveness scenario tested. Check your email for forgiveness notification.',
            'data' => $this->streakService->getStreakInfo($user)
        ]);
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
        
        return response()->json([
            'status' => 'success',
            'message' => 'Broken streak scenario tested. Check your email for streak reset notification.',
            'data' => $this->streakService->getStreakInfo($user)
        ]);
    }
}