<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BadgesController;
use App\Http\Controllers\CoursePricingController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\EnrollmentsController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\QuestionsAnsweredController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\StreakController;
use App\Http\Controllers\SuggestionsController;
use App\Http\Controllers\UnitsController;
use App\Http\Controllers\UserBadgesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProgressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('user')->group(function () {
    // authintications routes
    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::delete('/{id}/delete', [AuthController::class, 'deleteUserById']); // just for test, later maybe can be used

    //verify email routes
    Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmailWithOtp'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);


    // correcting users answers route and adding points if answer is correct for each question.
    Route::middleware(['auth:sanctum', 'ability:user-access'])->group(function () {
        // profile endpoints
        Route::prefix("profile")->group(function () {
            Route::get('/details', [UserController::class, 'getUserDetails']);
            Route::get('/details/edit', [userController::class, 'editUserDetails']);
        });

        Route::prefix('question')->group(function () {
            Route::post('{question_id}/answer/correct/', [UserProgressController::class, 'addPointsForCorrectAnswers']);
        });

        Route::prefix('streak')->group(function () {
            // get streak info for auth users
            Route::get('/', [StreakController::class, 'getStreakInfo']);
            Route::post('send-test-reminder', [StreakController::class, 'sendTestReminder']);

            // Test routes for streak scenarios (remove in production)
            Route::post('test-forgiveness', [StreakController::class, 'testForgiveness']);
            Route::post('test-broken', [StreakController::class, 'testBrokenStreak']);
        });


        // User badge endpoints (using BadgesController)
        Route::get('badges/earned', [BadgesController::class, 'getUserBadges']);
        Route::post('badges/check', [BadgesController::class, 'checkForNewBadges']);
        Route::get('badges/progress', [BadgesController::class, 'getAllBadgeProgress']);
        Route::get('badges/progress/{type}', [BadgesController::class, 'getBadgeProgress']);
        Route::get('badges/available', [BadgesController::class, 'getAvailableBadges']);

        // Public/Admin badge endpoints
        Route::get('/badges/type/{type}', [BadgesController::class, 'getBadgesByType']);
    });
});

Route::middleware(['auth:sanctum', ['ability:admin-access', 'ability:user-access']])->group(function () {
    Route::prefix('leaderboard')->group(function () {
        Route::get('topusers', [LeaderboardController::class, 'getTopUsersByPoints']);
        Route::get('all', [LeaderboardController::class, 'getAllUsersByPoints']);
    });

    Route::prefix('suggested')->group(function () {
        Route::get('courses', [SuggestionsController::class, 'getCoursesSuggestion']);
        Route::get('lessons', [SuggestionsController::class, 'getLessonsSuggestion']);
    });
});

// authinticated admins routes
Route::prefix('admin')->group(function () {
    // --- 1. Admin Authentication Routes (Public Access) ---
    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'ability:admin-access'])->group(function () {
        Route::apiResource('users', UserController::class);

        Route::apiResource('course', CoursesController::class);

        Route::apiResource('section', SectionsController::class);

        Route::apiResource('unit', UnitsController::class);

        Route::apiResource('lesson', LessonsController::class);

        Route::apiResource('question', QuestionsController::class);

        Route::apiResource('enrollments', EnrollmentsController::class);

        Route::apiResource('userbadge', UserBadgesController::class);

        Route::apiResource('badge', BadgesController::class);

        Route::apiResource('coursepricing', CoursePricingController::class);

        Route::apiResource('userprogress', UserProgressController::class);

        Route::apiResource('answeredquestion', QuestionsAnsweredController::class);
    });
});
