<?php
// create UserHomePageService class to add the methods "getPopularCourses, getNewCourses, 

namespace App\Services;

use App\Models\CoursesModel;
use Illuminate\Support\Facades\DB;

class UserHomePageService
{ 
    // get the popular courses by checking frequency of enrollments of each course
    // and get with them how many lessons are related to each course
    // and the total duration of all these lessons 
    // and how many user is enrolled in each course
    // notice that course have many sections, sections have many units, units have many lessons
    // and no need to return details of sections, units and lessons
    public function getPopularCourses()
    {
        return CoursesModel::withCount('enrollments')
            ->with(['sections.units.lessons']) // Eager load nested relationships
            ->orderByDesc('enrollments_count')
            ->take(3) // Adjust as needed for the number of popular courses
            ->get()
            ->map(function ($course) {
                $totalLessons = 0;
                $totalDuration = 0;

                foreach ($course->sections as $section) {
                    foreach ($section->units as $unit) {
                        $totalLessons += $unit->lessons->count();
                        $totalDuration += $unit->lessons->sum('duration');
                    }
                }

                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'level' => $course->level,
                    'topics' => json_decode($course->topics),
                    'image_url' => $course->image_url ? asset('/storage/' . $course->image_url) : null,
                    'language' => $course->language,
                    'enrollments_count' => $course->enrollments_count,
                    'total_lessons' => $totalLessons,
                    'total_duration' => $totalDuration, // Duration in minutes
                ];
            });
    }
    
    

    // get latest courses ordered by the popularity
    public function getNewCourses()
    {
        return CoursesModel::withCount('enrollments')
            ->with(['sections.units.lessons']) // Eager load nested relationships
            ->latest() // Order by created_at in descending order
            ->take(3) // Adjust as needed for the number of new courses
            ->get()
            ->map(function ($course) {
                $totalLessons = 0;
                $totalDuration = 0;

                foreach ($course->sections as $section) {
                    foreach ($section->units as $unit) {
                        $totalLessons += $unit->lessons->count();
                        $totalDuration += $unit->lessons->sum('duration');
                    }
                }

                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'level' => $course->level,
                    'topics' => json_decode($course->topics),
                    'image_url' => $course->image_url ? asset('/storage/' . $course->image_url) : null,
                    'language' => $course->language,
                    'enrollments_count' => $course->enrollments_count,
                    'total_lessons' => $totalLessons,
                    'total_duration' => $totalDuration, // Duration in minutes
                ];
            });
    }

    // get stats: activeLearners, coursesAvailable, lessonsCompleted
    public function getStats()
    {
        $activeLearners = DB::table('users')
            ->whereNotNull('last_activity_date')
            ->where('last_activity_date', '>=', now()->subDays(30)) // Active in the last 30 days
            ->count();

        $coursesAvailable = CoursesModel::count();

        $lessonsCompleted = DB::table('user_progress')
            ->where('is_completed', true)
            ->distinct('lesson_id')
            ->count();

        return [
            'active_learners' => $activeLearners,
            'courses_available' => $coursesAvailable,
            'lessons_completed' => $lessonsCompleted,
        ];
    }
    
}