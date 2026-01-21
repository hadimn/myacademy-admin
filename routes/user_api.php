<?php

// File: routes/user_api.php

// 1. PUBLIC USER AUTH ROUTES (No token needed yet)

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BadgesController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\StreakController;
use App\Http\Controllers\SuggestionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserHomePageController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserProgressController;
use App\Http\Controllers\UserShopController;
use App\Http\Middleware\CoursePaidMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stripe\PaymentIntent;
use Stripe\Stripe;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('auth/google', [GoogleController::class, 'loginWithGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// 2. EMAIL VERIFICATION (Uses signed URLs/Tokens, not Sanctum ability)
// {hash} ==> otp code
Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmailWithOtp'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    // Requires standard 'auth' middleware for session or Sanctum default
    $request->user()->sendEmailVerificationNotification();
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

Route::prefix('home')->group(function () {
    Route::get('popular-paths', [UserHomePageController::class, 'popularCourses']);
    Route::get('stats', [UserHomePageController::class, 'stats']);
    Route::get('new-courses', [UserHomePageController::class, 'newCourses']);
    Route::get('recommendations', [UserHomePageController::class, 'recommendations']);
});

// Shop courses - accessible to all, controller checks auth if present
Route::get('/shop/courses', [UserShopController::class, 'getCoursesForShop']);

Route::prefix('courses')->group(function () {
    Route::get('/', [CoursesController::class, 'getUserCourses']);
    Route::get('/{courseId}', [CoursesController::class, 'getUserCourseById']);
});

Route::get('/search', [CoursesController::class, 'search']);

// 3. AUTHENTICATED USER ROUTES (Requires 'user-access' ability)
Route::middleware(['auth:sanctum', 'ability:user-access'])->group(function () {
    // isAuthinticated
    Route::get('/is-authenticated', [AuthController::class, 'isAuthinticated']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/delete', [AuthController::class, 'deleteMyAccount']);

    // Profile Endpoints
    Route::prefix("profile")->group(function () {
        Route::get('/details', [UserController::class, 'getUserDetails']);
        Route::put('/details/edit', [UserController::class, 'editUserDetails']); // Should probably be PUT/PATCH
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
        Route::get('rank', [LeaderboardController::class, 'GetMyRank']);
        Route::get('stats', [LeaderboardController::class, 'getStats']);
    });

    Route::prefix('suggested')->group(function () {
        Route::get('courses', [SuggestionsController::class, 'getCoursesSuggestion']);
        Route::get('lessons', [SuggestionsController::class, 'getLessonsSuggestion']);
    });


    Route::prefix('users')->group(function () {
        // Get user profile by username
        Route::get('/{username}', [UserProfileController::class, 'getUserByUsername']);

        // Get user badges/achievements
        Route::get('/{username}/badges', [UserProfileController::class, 'getUserBadgesByUsername']);

        // Check if user exists
        Route::get('/check/{username}', [UserProfileController::class, 'checkUserExists']);
    });

    // Learning routes
    Route::prefix('learning')->group(function () {
        // Get all enrolled courses
        Route::get('/courses', [LearningController::class, 'getEnrolledCourses']);

        // Get course structure (sections, units, lessons)
        Route::get('/courses/{courseId}/structure', [LearningController::class, 'getCourseStructure']);

        // Get course progress
        Route::get('/courses/{courseId}/progress', [LearningController::class, 'getCourseProgress']);

        // Get lesson details with questions
        Route::get('/lessons/{lessonId}', [LearningController::class, 'getLesson']);

        // Submit lesson answers
        Route::post('/lessons/{lessonId}/submit', [LearningController::class, 'submitLessonAnswers']);

        // Get next lesson
        Route::get('/lessons/{lessonId}/next', [LearningController::class, 'getNextLesson']);
    });

    // shop endpoints
    Route::prefix('shop')->group(function () {
        Route::post('/courses/{courseId}/enroll', [UserShopController::class, 'enrollInCourse']);
        Route::post('/courses/{courseId}/unenroll', [UserShopController::class, 'unenrollCourse']);
    });

    // Strip payment route
    Route::post('/create-payment-intent', function (Request $request) {
        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::create([
            'amount' => $request->amount,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);


        return response()->json([
            'clientSecret' => $intent->client_secret
        ]);
    });
});
