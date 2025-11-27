<?php

namespace App\Services;

use App\Models\coursesModel;
use App\Models\User;

class SuggestionsService
{
    public function continueCourseSuggestion(User $user): array
    {
        $coursesToSuggest = $user->enrollments()
            ->with('course')
            ->where('completed_at', null)
            ->orderByDesc('enrolled_at')
            ->get()
            ->map(function ($enrollments) use ($user){

            });
        return $coursesToSuggest->toArray();
    }

    public function continueLessonSuggestion(User $user): array
    {
        $lessonsToSuggest = $user->userProgress()
            ->with('lesson')
            ->where('is_completed', 0)
            ->orderByDesc('started_at')
            ->get();
        return $lessonsToSuggest->toArray();
    }

    public function calculateCourseProgress(User $user, coursesModel $course){
        $totalLessons = $course->section()->count();
        return $totalLessons;
    }
}
