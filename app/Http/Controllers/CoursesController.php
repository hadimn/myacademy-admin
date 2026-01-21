<?php

namespace App\Http\Controllers;

use App\Http\Resources\CoursePricingResource;
use App\Http\Resources\CourseResource;
use App\Models\CoursesModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoursesController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = CoursesModel::class;
        $this->resourceName = "Course";
        $this->resourceClass = CourseResource::class;
        $this->validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:26',
            'level' => 'required|string|in:beginner,intermediate,advanced',
            'topics' => 'required|json',
            'video_url' => 'nullable|mimes:mp4,mov,avi,wmv|max:250600',
            'image_url' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:50048',
            'language' => 'required|string|min:2',
            'order' => 'required|integer|min:0',
        ];
        $this->editValidationRules = [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|min:26',
            'level' => 'sometimes|required|string|in:beginner,intermediate,advanced',
            'topics' => 'sometimes|required|json',
            'video_url' => 'nullable|mimes:mp4,mov,avi,wmv|max:250600',
            'image_url' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:50048',
            'language' => 'sometimes|required|string|min:2',
            'order' => 'sometimes|required|integer|min:0',
        ];
        $this->fileFields = [
            'video_url',
            'image_url',
        ];
        $this->searchableFields = [
            'title',
            'description',
            'language',
        ];
    }

    public function getUserCourses(Request $request)
    {
        try {
            // Using auth('sanctum')->user() to detect user on public routes
            $user = auth('sanctum')->user();
            $enrolledCourseIds = [];

            // Only get enrolled courses if user is authenticated
            if ($user) {
                $enrolledCourseIds = $user->enrollments()->pluck('course_id')->toArray();
            }

            $courses = CoursesModel::with(['pricing'])->get();

            $coursesData = $courses->map(function ($course) use ($enrolledCourseIds, $user) {
                $isEnrolled = \in_array($course->course_id, $enrolledCourseIds);
                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'level' => $course->level,
                    'topics' => json_decode($course->topics),
                    'video_url' => $course->video_url ? asset('storage/' . $course->video_url) : null,
                    'image_url' => $course->image_url ? asset('storage/' . $course->image_url) : null,
                    'language' => $course->language,
                    'order' => $course->order,
                    'created_at' => $course->created_at,
                    'is_enrolled' => $user ? $isEnrolled : null, // Only show enrollment status if authenticated
                    'pricing' => $course->relationLoaded('pricing') && $course->pricing ? new CoursePricingResource($course->pricing) : null,
                ];
            });

            return $this->successResponse(
                $coursesData,
                "Courses retrieved successfully",
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve courses",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    // getUserCourseById
    public function getUserCourseById(Request $request, $courseId)
    {
        try {
            // Using auth('sanctum')->user() to detect user on public routes
            $user = auth('sanctum')->user();
            $isEnrolled = false;

            // Check enrollment status if user is authenticated
            if ($user) {
                $isEnrolled = $user->enrollments()->where('course_id', $courseId)->exists();
            }

            // Load course with or without sections based on enrollment
            if ($user && $isEnrolled) {
                // Enrolled users get full course structure
                $course = CoursesModel::with(['sections.units.lessons', 'pricing'])->find($courseId);
            } else {
                // Unauthenticated or non-enrolled users get basic info only
                $course = CoursesModel::with(['pricing'])->find($courseId);
            }

            if (!$course) {
                return $this->errorResponse(
                    "Course not found",
                    Response::HTTP_NOT_FOUND
                );
            }

            $courseData = [
                'course_id' => $course->course_id,
                'title' => $course->title,
                'description' => $course->description,
                'level' => $course->level,
                'topics' => json_decode($course->topics),
                'video_url' => $course->video_url ? asset('storage/' . $course->video_url) : null,
                'image_url' => $course->image_url ? asset('storage/' . $course->image_url) : null,
                'language' => $course->language,
                'order' => $course->order,
                'created_at' => $course->created_at,
                'is_enrolled' => $user ? $isEnrolled : null, // Only show enrollment status if authenticated
                'pricing' => $course->pricing
                    ? new CoursePricingResource($course->pricing)
                    : null,
            ];

            // Only include sections if user is enrolled
            if ($user && $isEnrolled && $course->relationLoaded('sections')) {
                $courseData['sections'] = $course->sections->map(function ($section) {
                    return [
                        'section_id' => $section->section_id,
                        'title' => $section->title,
                        'description' => $section->description,
                        'order' => $section->order,
                        'units' => $section->units->map(function ($unit) {
                            return [
                                'unit_id' => $unit->unit_id,
                                'title' => $unit->title,
                                'order' => $unit->order,
                                'lessons' => $unit->lessons->map(function ($lesson) {
                                    return [
                                        'lesson_id' => $lesson->lesson_id,
                                        'title' => $lesson->title,
                                        'order' => $lesson->order,
                                    ];
                                }),
                            ];
                        }),
                    ];
                });
            } else {
                // For non-enrolled users, show a message instead of sections
                $courseData['sections'] = null;
                $courseData['message'] = $user
                    ? "Enroll in this course to access the full content"
                    : "Please log in and enroll to access the course content";
            }

            return $this->successResponse(
                $courseData,
                "Course retrieved successfully",
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve course",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }



    // create a search method to search for a specific course by searchable fields no need for pagination just get the result
    public function search(Request $request)
    {
        try {
            $query = $this->model::query();

            if ($request->has('search') && !empty($request->search) && !empty($this->searchableFields)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    foreach ($this->searchableFields as $field) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                });
            } else {
                return $this->errorResponse(
                    "Please provide a search query.",
                    Response::HTTP_BAD_REQUEST
                );
            }

            $data = $query->get();

            if ($data->isEmpty()) {
                return $this->successResponse(
                    [],
                    "No {$this->resourceName}s found matching your search.",
                    Response::HTTP_OK
                );
            }

            return $this->successResponse(
                $this->wrapResource($data),
                "{$this->resourceName}s retrieved successfully",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to search {$this->resourceName}s",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }
}
