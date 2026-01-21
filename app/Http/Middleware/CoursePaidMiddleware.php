<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\EnrollmentsModel;
use Illuminate\Support\Facades\Auth;

class CoursePaidMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Get course_id from route parameter
        $courseId = $request->route('courseId');

        if (!$courseId) {
            return response()->json([
                'message' => 'Course not specified'
            ], 400);
        }

        // Check if user has a paid enrollment OR if the course is free
        $enrollment = EnrollmentsModel::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'You are not enrolled in this course'
            ], 403);
        }

        // Check if payment is required and if it's been paid
        if ($enrollment->payment_status !== 'paid') {
            return response()->json([
                'message' => 'You have not purchased this course'
            ], 403);
        }

        return $next($request);
    }
}
