<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BadgesController;
use App\Http\Controllers\CoursePricingController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\EnrollmentsController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\QuestionsAnsweredController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\StreakController;
use App\Http\Controllers\UnitsController;
use App\Http\Controllers\UserBadgesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProgressController;
use App\Models\OtpCodes;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


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

    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show'])->where('id', '[0-9]+');
    Route::post('/create', [UserController::class, 'store']);
    Route::post('/{id}/edit', [UserController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/{id}/delete', [UserController::class, 'destroy'])->where('id', '[0-9]+');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('question')->group(function () {
            Route::post('{question_id}/answer/correct/', [UserProgressController::class, 'addPointsForCorrectAnswers']);
        });

        // Streak routes
        Route::get('/streak', [StreakController::class, 'getStreakInfo']);
        Route::post('/streak/update', [StreakController::class, 'updateStreak']);

        // Badge routes
        Route::get('/badges', [BadgeController::class, 'getUserBadges']);
        Route::post('/badges/check', [BadgeController::class, 'checkForNewBadges']);
        Route::get('/badges/progress/{type}', [BadgeController::class, 'getBadgeProgress']);
    });
});

// authinticated users and admins routes
Route::middleware(['auth:sanctum'])->group(function () {
    // profile endpoints
    Route::prefix("profile")->group(function () {
        Route::get('/details', [UserController::class, 'getUserDetails']);
        Route::get('/details/edit', [userController::class, 'editUserDetails']);
    });

    Route::prefix('course')->group(function () {
        // all accounts:
        Route::get('/all', [CoursesController::class, 'showCourses']);
        Route::get('/{course_id}', [CoursesController::class, 'getCourseById'])->where('course_id', '[0-9]+');
        // only admin accounts:
        Route::post('/create', [CoursesController::class, 'newCourse']);
        Route::post('/{course_id}/edit', [CoursesController::class, 'editCourse']);
        Route::delete('/{course_id}/delete', [CoursesController::class, 'deleteCourse']);
    });

    // base crud controllers routes
    Route::prefix('section')->group(function () {
        //section routes
        Route::get('/', [SectionsController::class, 'index']);
        Route::get('/{id}', [SectionsController::class, 'show']);
        Route::post('/create', [SectionsController::class, 'store']);
        Route::post('/{id}/edit', [SectionsController::class, 'update']);
        Route::delete('/{id}/delete', [SectionsController::class, 'destroy']);
    });

    Route::prefix('unit')->group(function () {
        //units routes
        Route::get('/', [UnitsController::class, 'index']);
        Route::get('/{id}', [UnitsController::class, 'show']);
        Route::post('/create', [UnitsController::class, 'store']);
        Route::post('/{id}/edit', [UnitsController::class, 'update']);
        Route::delete('/{id}/delete', [UnitsController::class, 'destroy']);
    });

    Route::prefix('lesson')->group(function () {
        // lessons routes
        Route::get('/', [LessonsController::class, 'index']);
        Route::get('/{id}', [LessonsController::class, 'show']);
        Route::post('/create', [LessonsController::class, 'store']);
        Route::post('/{id}/edit', [LessonsController::class, 'update']);
        Route::delete('/{id}/delete', [LessonsController::class, 'destroy']);
    });

    Route::prefix('question')->group(function () {
        //questions routes
        Route::get('/', [QuestionsController::class, 'index']);
        Route::get('/{id}', [QuestionsController::class, 'show']);
        Route::post('/create', [QuestionsController::class, 'store']);
        Route::post('/{id}/edit', [QuestionsController::class, 'update']);
        Route::delete('/{id}/delete', [QuestionsController::class, 'destroy']);
    });

    Route::prefix('enrollments')->group(function () {
        // enrollments routes
        Route::get('/', [EnrollmentsController::class, 'index']);
        Route::get('/{id}', [EnrollmentsController::class, 'show']);
        Route::post('/create', [EnrollmentsController::class, 'store']);
        Route::post('/{id}/edit', [EnrollmentsController::class, 'update']);
        Route::delete('/{id}/delete', [EnrollmentsController::class, 'destroy']);
    });

    Route::prefix('userbadge')->group(function () {
        Route::get('/', [UserBadgesController::class, 'index']);
        Route::get('/{id}', [UserBadgesController::class, 'show']);
        Route::post('/create', [UserBadgesController::class, 'store']);
        Route::post('/{id}/edit', [UserBadgesController::class, 'update']);
        Route::delete('/{id}/delete', [UserBadgesController::class, 'destroy']);
    });

    Route::prefix('badge')->group(function () {
        Route::get('/', [BadgesController::class, 'index']);
        Route::get('/{id}', [BadgesController::class, 'show']);
        Route::post('/create', [BadgesController::class, 'store']);
        Route::post('/{id}/edit', [BadgesController::class, 'update']);
        Route::delete('/{id}/delete', [BadgesController::class, 'destroy']);
    });

    Route::prefix('coursepricing')->group(function () {
        Route::get('/', [CoursePricingController::class, 'index']);
        Route::get('/{id}', [CoursePricingController::class, 'show'])->where('id', '[0-9]+');
        Route::post('/create', [CoursePricingController::class, 'store']);
        Route::post('/{id}/edit', [CoursePricingController::class, 'update']);
        Route::delete('/{id}/delete', [CoursePricingController::class, 'destroy']);
    });

    Route::prefix('userprogress')->group(function () {
        Route::get('/', [UserProgressController::class, 'index']);
        Route::get('/{id}', [UserProgressController::class, 'show'])->where('id', '[0-9]+');
        Route::post('/create', [UserProgressController::class, 'store']);
        Route::post('/{id}/edit', [UserProgressController::class, 'update']);
        Route::delete('/{id}/delete', [UserProgressController::class, 'destroy']);
    });

    Route::prefix('answeredquestion')->group(function () {
        Route::get('/', [QuestionsAnsweredController::class, 'index']);
        Route::get('/{id}', [QuestionsAnsweredController::class, 'show'])->where('id', '[0-9]+');
        Route::post('/create', [QuestionsAnsweredController::class, 'store']);
        Route::post('/{id}/edit', [QuestionsAnsweredController::class, 'update']);
        Route::delete('/{id}/delete', [QuestionsAnsweredController::class, 'destroy']);
    });
});
