<?php


// authinticated admins routes

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\BadgesController;
use App\Http\Controllers\CoursePricingController;
use App\Http\Controllers\CoursesController;
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

    Route::post('/new/account', [AdminAuthController::class, 'register']);
    Route::post('logout', [AdminAuthController::class, 'logout']);

    Route::prefix('leaderboard')->group(function () {
        Route::get('all', [LeaderboardController::class, 'getAllUsersByPoints']);
    });

    Route::delete('user/{id}/delete', [UserController::class, 'deleteUserById']);

    Route::apiResource('users', UserController::class);

    Route::apiResource('courses', CoursesController::class);

    Route::apiResource('sections', SectionsController::class);

    Route::apiResource('units', UnitsController::class);

    Route::apiResource('lessons', LessonsController::class);

    Route::apiResource('questions', QuestionsController::class);

    Route::apiResource('enrollments', EnrollmentsController::class);

    Route::apiResource('userbadge', UserBadgesController::class);

    Route::apiResource('badge', BadgesController::class);

    Route::apiResource('coursepricing', CoursePricingController::class);

    Route::apiResource('userprogress', UserProgressController::class);

    Route::apiResource('answeredquestion', QuestionsAnsweredController::class);
});
