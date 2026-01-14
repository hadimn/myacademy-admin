<?php

namespace App\Services;

use App\Models\CourseRatingsModel;

class CourseRatingsService{
    private $model = CourseRatingsModel::class;

    public function getAverageRating($courseId)
    {
        $averageRating = $this->model::query()->where('course_id', $courseId)->avg('rating');
        if ($averageRating === null) {
            return 0; // Or handle as appropriate for your application
        }
        $averageRating = round($averageRating, 2);
        return $averageRating;
    }
}