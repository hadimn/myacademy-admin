<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserShopResource;
use App\Models\CoursesModel;
use App\Models\EnrollmentsModel;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserShopController extends Controller
{
    use ApiResponseTrait;
    // this page is to make the user enroll for the courses available,
    // show the all courses, and the courses that he already enrolled, should return with it, that is_enrolled=true
    public function getCoursesForShop(Request $request)
    {
        try {
            $user = $request->user();
            $enrolledCourseIds = $user->enrollments()->pluck('course_id')->toArray();

            $courses = CoursesModel::with('pricing')->get();

            $coursesWithEnrollmentStatus = $courses->map(function ($course) use ($enrolledCourseIds) {
                $course->is_enrolled = in_array($course->course_id, $enrolledCourseIds);
                return $course;
            });

            return $this->successResponse(
                UserShopResource::collection($coursesWithEnrollmentStatus),
                "Courses for shop retrieved successfully",
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve courses for shop",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    // enrollInCourse without condition, just enroll
    public function enrollInCourse(Request $request, $courseId)
    {
        try {
            $user = $request->user();

            // Check if the course exists
            $course = CoursesModel::with('pricing')->find($courseId);
            if (!$course) {
                return $this->errorResponse(
                    "Course not found",
                    Response::HTTP_NOT_FOUND
                );
            }

            // Check if the user is already enrolled
            $existingEnrollment = EnrollmentsModel::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->first();

            if ($existingEnrollment) {
                return $this->errorResponse(
                    "You are already enrolled in this course",
                    Response::HTTP_CONFLICT
                );
            }

            // Determine amount paid and payment status based on pricing
            $amountPaid = 0;
            $paymentStatus = 'paid'; // Default for free courses or direct enrollment
            $paymentMethod = 'N/A';

            if ($course->pricing) {
                if ($course->pricing->is_free) {
                    $amountPaid = 0;
                    $paymentStatus = 'paid';
                    $paymentMethod = 'Free';
                } else {
                    // For paid courses, assume a successful payment for now.
                    // In a real application, this would involve a payment gateway.
                    $amountPaid = $course->pricing->discount_price ?? $course->pricing->price;
                    $paymentStatus = 'paid'; // Or 'pending' if payment gateway is involved
                    $paymentMethod = 'Simulated Payment'; // Replace with actual payment method
                }
            } else {
                // If no pricing is set, assume it's free
                $amountPaid = 0;
                $paymentStatus = 'paid';
                $paymentMethod = 'Free (No Pricing Set)';
            }


            // Create the enrollment
            $enrollment = EnrollmentsModel::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'amount_paid' => $amountPaid,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
            ]);

            return $this->successResponse(
                $enrollment,
                "Enrollment successful",
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to enroll in course",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    // unenrollcourse
    public function unenrollCourse(Request $request, $courseId)
    {
        try {
            $user = $request->user();

            $enrollment = EnrollmentsModel::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->first();

            if (!$enrollment) {
                return $this->errorResponse(
                    "You are not enrolled in this course",
                    Response::HTTP_NOT_FOUND
                );
            }

            $enrollment->delete();

            return $this->successResponse(
                null,
                "Unenrollment successful",
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to unenroll from course",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
        
    }
    
}
