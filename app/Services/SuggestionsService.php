<?php

namespace App\Services;

use App\Models\coursesModel;
use App\Models\LessonsModel;
use App\Models\User;
use App\Models\UserProgressModel;

use function Laravel\Prompts\select;

class SuggestionsService
{
    public function continueCourseSuggestion(User $user): array
    {
        $coursesToSuggest = $user->enrollments()
            ->with('course')
            ->where('completed_at', null)
            ->orderByDesc('enrolled_at')
            ->get()
            ->map(function ($enrollment) use ($user) {
                $course = $enrollment->course;
                $progress = $this->calculateCourseProgress($user, $course);
                return [
                    'user' => $user,
                    'course_id' => $course->course_id,
                    'progress' => $progress,
                ];
            });

        return $coursesToSuggest->toArray();
    }

    public function continueLessonSuggestion(User $user): array
    {
        $lessonsToSuggest = $user->userProgress()
            ->where('is_completed', 0)
            ->orderByDesc('started_at')
            ->get()
            ->map(function ($userProgress) use ($user){
                $lesson = $userProgress->lesson;
                $progress = $this->calculateLessonProgress($user, $lesson);
                return [
                    "userProgress" => $userProgress,
                    "Lesson_progress" => $progress
                ];
            });
        return $lessonsToSuggest->toArray();
    }

    protected function calculateLessonProgress(User $user, LessonsModel $lesson)
    {
        $questions = $lesson->questions()->get();

        $answeredQuestions = [];

        foreach ($questions as $question) {
            if ($user->answeredQuestion()->where('questions_id', $question->questions_id)->where('is_passed', 1)->first()) {
                $answeredQuestions[] = $user->answeredQuestion()->where('questions_id', $question->questions_id)->where('is_passed', 1)->first();
            }
        }

        $answeredCount = count($answeredQuestions);
        $questionsCount = count($questions->toArray());

        $progress =  ($answeredCount/$questionsCount)*100;

        return $progress;
    }


    protected function calculateCourseProgress(User $user, coursesModel $course)
    {
        $userProgress = UserProgressModel::where('course_id', $course->course_id)
            ->where('user_id', $user->id)
            ->where('is_completed', 1)
            ->get();
        $userlessons = [];
        foreach ($userProgress as $progress) {
            $lesson = $progress->lesson()->get()->toArray();
            $userlessons = array_merge($userlessons, $lesson);
        }

        // variable that have the count of the lessons that user had already started or completed
        $userLessonsCount = count($userlessons);

        // Or access structured
        $lessons = [];
        foreach ($course->sections as $section) {
            foreach ($section->units as $unit) {
                foreach ($unit->lessons as $lesson) {
                    $lessons[] = $lesson->toArray();
                    // Do something with each lesson
                }
            }
        }

        $allLessonsCount = count($lessons);

        $courseProgressPercentage = ($userLessonsCount / $allLessonsCount) * 100;

        return $courseProgressPercentage;
    }
}
