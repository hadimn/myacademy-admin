<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseRatingsResource;
use App\Models\CourseRatingsModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseRatingsController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = CourseRatingsModel::class;
        $this->resourceName = 'Course Rating';
        $this->resourceClass = CourseRatingsResource::class;
        $this->validationRules = [
            'course_id' => 'required|integer|exists:courses,course_id',
            'user_id' => 'required|integer|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
        ];
        $this->editValidationRules = [
            'course_id' => 'sometimes|required|integer|exists:courses,course_id',
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'rating' => 'sometimes|required|integer|min:1|max:5',
        ];
        $this->searchableFields = [
            'course_id',
            'user_id',
            'rating',
        ];
    }

    // get the average rating of a specific course
    public function getAverageRating(Request $request)
    {
        $courseId = $request->param('course_id');
        $averageRating = $this->model::query()->where('course_id', $courseId)->avg('rating');
        return $this->successResponse(
            $averageRating,
            'Average rating retrieved successfully!',
            Response::HTTP_OK,
        );
    }
}
