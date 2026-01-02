<?php
// create UserHomePageService class to add the methods "getPopularCourses, getNewCourses, 

namespace App\Services;

use App\Models\CoursesModel;
use App\Models\User;
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

    // TODO (AI): a smart suggestion system. method name:getRecommendations
    // Consider user interests, skill level, enrolled/completed courses, popularity,
    // and course-topic relevance to generate personalized recommendations.
    // and let if no value, then get 3 random courses 
    public function getRecommendations(User $user)
    {
        $LIMIT = 3;

        /*
    |--------------------------------------------------------------------------
    | 1. User course history
    |--------------------------------------------------------------------------
    */
        $enrolledCourseIds = $user->enrollments()
            ->pluck('course_id')
            ->toArray();

        $completedCourseIds = DB::table('user_progress')
            ->join('lessons', 'user_progress.lesson_id', '=', 'lessons.lesson_id')
            ->join('units', 'lessons.unit_id', '=', 'units.unit_id')
            ->join('sections', 'units.section_id', '=', 'sections.section_id')
            ->where('user_progress.user_id', $user->id)
            ->where('user_progress.is_completed', true)
            ->distinct()
            ->pluck('sections.course_id')
            ->toArray();

        $excludedCourseIds = array_unique(array_merge(
            $enrolledCourseIds,
            $completedCourseIds
        ));

        /*
    |--------------------------------------------------------------------------
    | 2. Extract user topics
    |--------------------------------------------------------------------------
    */
        $userTopics = CoursesModel::whereIn('course_id', $excludedCourseIds)
            ->pluck('topics')
            ->flatMap(fn($topics) => json_decode($topics, true) ?? [])
            ->unique()
            ->values()
            ->toArray();

        /*
    |--------------------------------------------------------------------------
    | 3. Base query (strict exclusion)
    |--------------------------------------------------------------------------
    */
        $baseQuery = CoursesModel::with('sections.units.lessons')
            ->withCount('enrollments')
            ->whereNotIn('course_id', $excludedCourseIds);

        $recommendations = collect();

        /*
    |--------------------------------------------------------------------------
    | 4. Topic-based (highest priority)
    |--------------------------------------------------------------------------
    */
        if (!empty($userTopics)) {
            $recommendations = (clone $baseQuery)
                ->where(function ($query) use ($userTopics) {
                    foreach ($userTopics as $topic) {
                        $query->orWhereJsonContains('topics', $topic);
                    }
                })
                ->orderByDesc('enrollments_count')
                ->take($LIMIT)
                ->get();
        }

        /*
    |--------------------------------------------------------------------------
    | 5. Popular fallback
    |--------------------------------------------------------------------------
    */
        if ($recommendations->count() < $LIMIT) {
            $needed = $LIMIT - $recommendations->count();

            $popular = (clone $baseQuery)
                ->whereNotIn(
                    'course_id',
                    $recommendations->pluck('course_id')->toArray()
                )
                ->orderByDesc('enrollments_count')
                ->take($needed)
                ->get();

            $recommendations = $recommendations->merge($popular);
        }

        /*
    |--------------------------------------------------------------------------
    | 6. Random fallback (still excluding history)
    |--------------------------------------------------------------------------
    */
        if ($recommendations->count() < $LIMIT) {
            $needed = $LIMIT - $recommendations->count();

            $random = (clone $baseQuery)
                ->whereNotIn(
                    'course_id',
                    $recommendations->pluck('course_id')->toArray()
                )
                ->inRandomOrder()
                ->take($needed)
                ->get();

            $recommendations = $recommendations->merge($random);
        }

        /*
    |--------------------------------------------------------------------------
    | 7. FINAL fallback (relax history exclusion)
    |--------------------------------------------------------------------------
    | This guarantees exactly 3 courses
    |--------------------------------------------------------------------------
    */
        if ($recommendations->count() < $LIMIT) {
            $needed = $LIMIT - $recommendations->count();

            $fallback = CoursesModel::with('sections.units.lessons')
                ->withCount('enrollments')
                ->whereNotIn(
                    'course_id',
                    $recommendations->pluck('course_id')->toArray()
                )
                ->inRandomOrder()
                ->take($needed)
                ->get();

            $recommendations = $recommendations->merge($fallback);
        }

        /*
    |--------------------------------------------------------------------------
    | 8. Format response
    |--------------------------------------------------------------------------
    */
        return $recommendations->take($LIMIT)->map(function ($course) {
            $totalLessons = 0;
            $totalDuration = 0;

            foreach ($course->sections as $section) {
                foreach ($section->units as $unit) {
                    $totalLessons += $unit->lessons->count();
                    $totalDuration += $unit->lessons->sum('duration');
                }
            }

            return [
                'course_id'         => $course->course_id,
                'title'             => $course->title,
                'description'       => $course->description,
                'level'             => $course->level,
                'topics'            => json_decode($course->topics),
                'image_url'         => $course->image_url
                    ? asset('/storage/' . $course->image_url)
                    : null,
                'language'          => $course->language,
                'enrollments_count' => $course->enrollments_count,
                'total_lessons'     => $totalLessons,
                'total_duration'    => $totalDuration,
            ];
        })->values();
    }
}
