<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\UserController;
use App\Models\OtpCodes;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::delete('/users/{id}', [AuthController::class, 'deleteUserById']); // just for test, later maybe can be used



Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmailWithOtp'])->name('verification.verify');


Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/login', function () {
    return response()->json([
        "status" => "success",
        "message" => "hello bro",
    ]);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

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

    Route::prefix('section')->group(function(){
        //section routes
    });

    Route::prefix('units')->group(function(){
        //units routes
    });

    Route::prefix('lesson')->group(function () {
        // all accounts:
        Route::get('/all', [LessonsController::class, 'showLessons']);
        Route::get('/{lesson_id}', [LessonsController::class, 'getLessonById'])->where('lesson_id', '[0-9]+');
        // only admin accounts:
        Route::post('/create', [LessonsController::class, 'newLesson']);
        Route::post('/{lesson_id}/edit', [LessonsController::class, 'editLesson'])->where('lesson_id', '[0-9]+');
        Route::delete('/{lesson_id}/delete', [LessonsController::class, 'deleteLesson'])->where('lesson_id', '[0-9]+');
    });

    Route::prefix('questions')->group(function(){
        //questions routes
    });
});
