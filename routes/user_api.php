<?php

// File: routes/user_api.php

// 1. PUBLIC USER AUTH ROUTES (No token needed yet)

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BadgesController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\StreakController;
use App\Http\Controllers\SuggestionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProgressController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// 3. AUTHENTICATED USER ROUTES (Requires 'user-access' ability)
Route::middleware(['auth:sanctum', 'ability:user-access'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/delete', [AuthController::class, 'deleteMyAccount']);

    // Profile Endpoints
    Route::prefix("profile")->group(function () {
        Route::get('/details', [UserController::class, 'getUserDetails']);
        Route::get('/details/edit', [UserController::class, 'editUserDetails']); // Should probably be PUT/PATCH
    });

    // Answerin question route
    Route::post('/question/{question_id}/answer/correct', [UserProgressController::class, 'answerQuestion']);
    
    // Core User Actions (Streaks, Badges, Progress)
    // streak routes 
    Route::prefix('streak')->group(function () {
        // get streak info for auth users
        Route::get('/', [StreakController::class, 'getStreakInfo']);
        Route::post('send-test-reminder', [StreakController::class, 'sendTestReminder']);

        // Test routes for streak scenarios (remove in production)
        Route::post('test-forgiveness', [StreakController::class, 'testForgiveness']);
        Route::post('test-broken', [StreakController::class, 'testBrokenStreak']);
    });

    Route::prefix('badges')->group(function () {
        // User badge endpoints (using BadgesController)
        Route::get('earned', [BadgesController::class, 'getUserBadges']);
        Route::post('check', [BadgesController::class, 'checkForNewBadges']);
        Route::get('progress', [BadgesController::class, 'getAllBadgeProgress']);
        Route::get('progress/{type}', [BadgesController::class, 'getBadgeProgress']);
        Route::get('available', [BadgesController::class, 'getAvailableBadges']);

        // Public/Admin badge endpoints
        Route::get('type/{type}', [BadgesController::class, 'getBadgesByType']);
    });

    Route::prefix('leaderboard')->group(function () {
        Route::get('topusers', [LeaderboardController::class, 'getTopUsersByPoints']);
        Route::get('all', [LeaderboardController::class, 'getAllUsersByPoints']);
    });

    Route::prefix('suggested')->group(function () {
        Route::get('courses', [SuggestionsController::class, 'getCoursesSuggestion']);
        Route::get('lessons', [SuggestionsController::class, 'getLessonsSuggestion']);
    });
});
