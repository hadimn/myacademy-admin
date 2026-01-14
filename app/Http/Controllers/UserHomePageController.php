<?php

namespace App\Http\Controllers;

use App\Services\UserHomePageService;
use App\Traits\ApiResponseTrait;
use App\Services\CourseRatingsService;
use Symfony\Component\HttpFoundation\Response;

class UserHomePageController extends Controller
{
    use ApiResponseTrait;

    protected UserHomePageService $userHomePageService;
    protected CourseRatingsService $courseRatingsService;

    public function __construct(UserHomePageService $userHomePageService, CourseRatingsService $courseRatingsService)
    {
        $this->userHomePageService = $userHomePageService;
        $this->courseRatingsService = $courseRatingsService;
    }
    // create method to retrieve the popular courses according to the enrollment frequecy
    public function popularCourses()
    {
        try {
            $popularCourses = $this->userHomePageService->getPopularCourses();
            // add the average rating for each popular course
            $popularCourses = $popularCourses->map(function ($course) {
                $averageRating = $this->courseRatingsService->getAverageRating($course['course_id']);
                $course['average_rating'] = $averageRating;
                return $course;
            });
            return $this->successResponse(
                $popularCourses,
                "Popular courses retrieved successfully!",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve popular courses due to an error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
        
    }

    // create stats method
    public function stats()
    {
        try {
            $stats = $this->userHomePageService->getStats(); 
            return $this->successResponse(
                $stats,
                "Stats retrieved successfully!",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve stats due to an error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }
    

    // create method to retrieve the newly added courses ordered by the popularity
    public function newCourses()
    {
        try {
            $newCourses = $this->userHomePageService->getNewCourses();
            // average rating for each new course
            $newCourses = $newCourses->map(function ($course) {
                $averageRating = $this->courseRatingsService->getAverageRating($course['course_id']);
                $course['average_rating'] = $averageRating;
                return $course;
            });
            return $this->successResponse(
                $newCourses,
                "New courses retrieved successfully!",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve new courses due to an error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    // get recommendation
    public function recommendations()
    {
        try {
            $user = auth()->guard('sanctum')->user(); // Get the authenticated user
            if (!$user) {
                return $this->errorResponse(
                    "Unauthenticated. Please log in to get recommendations.",
                    Response::HTTP_UNAUTHORIZED,
                );
            }
            $recommendations = $this->userHomePageService->getRecommendations($user);
            // get course ratings for each recommended course using courseratingsmodel:
            $recommendations = $recommendations->map(function ($course) {
                $averageRating = $this->courseRatingsService->getAverageRating($course['course_id']);
                $course['average_rating'] = $averageRating;
                return $course;
            });
            
            return $this->successResponse(
                $recommendations,
                "Recommendations retrieved successfully!",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve recommendations due to an error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }
    
    
}
