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
            $user = $request->user();
            $enrolledCourseIds = $user->enrollments()->pluck('course_id')->toArray();

            $courses = CoursesModel::with(['sections.units.lessons', 'pricing'])->get();

            $coursesData = $courses->map(function ($course) use ($enrolledCourseIds) {
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
                    'is_enrolled' => $isEnrolled,
                    'pricing' => $course->relationLoaded('pricing') && $course->pricing ? new CoursePricingResource($course->pricing) : null,
                ];
            });

            return $this->successResponse(
                $coursesData,
                "User courses retrieved successfully",
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve user courses",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    // getUserCourseById
    public function getUserCourseById(Request $request, $courseId)
    {
        try {
            $user = $request->user();
            $course = CoursesModel::with(['sections.units.lessons', 'pricing'])->find($courseId);

            if (!$course) {
                return $this->errorResponse(
                    "Course not found",
                    Response::HTTP_NOT_FOUND
                );
            }

            $isEnrolled = $user->enrollments()->where('course_id', $courseId)->exists();

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
                'is_enrolled' => $isEnrolled,
                'pricing' => $course->pricing
                    ? new CoursePricingResource($course->pricing)
                    : null,
                'sections' => $course->sections->map(function ($section) {
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
                }),
            ];

            return $this->successResponse(
                $courseData,
                "User course retrieved successfully",
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve user course",
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
