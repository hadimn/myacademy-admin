<?php


// authinticated admins routes
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminsController;
use App\Http\Controllers\AiGenerateController;
use App\Http\Controllers\BadgesController;
use App\Http\Controllers\CoursePricingController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentsController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\QuestionsAnsweredController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\UnitsController;
use App\Http\Controllers\UserBadgesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProgressController;
use Illuminate\Support\Facades\Route;

// --- 1. Admin Authentication Routes (Public Access) ---
Route::post('login', [AdminAuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'ability:admin-access'])->group(function () {
    Route::put('/update/profile', [AdminAuthController::class, 'updateProfile']);
    Route::put('/change/password', [AdminAuthController::class, 'changePassword']);
    Route::post('/new/account', [AdminAuthController::class, 'register']);
    Route::post('logout', [AdminAuthController::class, 'logout']);

    // Dashboard analytics routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/counts', [DashboardController::class, 'counts']);
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/revenue', [DashboardController::class, 'revenue']);
        Route::get('/top-courses', [DashboardController::class, 'topCourses']);
        Route::get('/recent-enrollments', [DashboardController::class, 'recentEnrollments']);
        Route::get('/user-growth', [DashboardController::class, 'userGrowth']);
        Route::get('/payment-status-distribution', [DashboardController::class, 'paymentStatusDistribution']);
        Route::get('/course-completion-stats', [DashboardController::class, 'courseCompletionStats']);
    });

    Route::prefix('leaderboard')->group(function () {
        Route::get('topusers', [LeaderboardController::class, 'getTopUsersByPoints']);
        Route::get('all', [LeaderboardController::class, 'getAllUsersByPoints']);
    });

    // should pay for open ai to make it work.
    Route::post('ai/generate', [AiGenerateController::class, 'generate']);

    Route::delete('user/{id}/delete', [UserController::class, 'deleteUserById']);

    Route::get('dashboard/counts', [DashboardController::class, 'counts']);

    Route::apiResource('admins', AdminsController::class);

    Route::apiResource('users', UserController::class);

    Route::apiResource('badges', BadgesController::class);

    Route::apiResource('userprogress', UserProgressController::class);

    Route::apiResource('courses', CoursesController::class);

    Route::apiResource('sections', SectionsController::class);

    Route::apiResource('units', UnitsController::class);

    Route::apiResource('lessons', LessonsController::class);

    Route::apiResource('questions', QuestionsController::class);

    Route::apiResource('enrollments', EnrollmentsController::class);

    Route::apiResource('userbadge', UserBadgesController::class);

    Route::apiResource('coursepricing', CoursePricingController::class);

    Route::apiResource('answeredquestions', QuestionsAnsweredController::class);
});