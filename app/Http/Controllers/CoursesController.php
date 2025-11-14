<?php

namespace App\Http\Controllers;

use App\Models\coursesModel;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    public function newCourse(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|min:26',
                'video_url' => 'nullable|mimes:mp4,mov,avi,wmv|max:250600',
                'image_url' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:50048',
            ]);

            $imagePath = null;
            $videoPath = null;

            if ($request->hasFile('image_url')) {
                $imagePath = $request->file('image_url')->store('Course_images', 'public');
            }

            if ($request->hasFile('video_url')) {
                $videoPath = $request->file('video_url')->store('course_videos', 'public');
            }

            $course = CoursesModel::create([
                'title' => $request->title,
                'description' => $request->description,
                'video_url' => $videoPath,
                'image_url' => $imagePath,
            ]);

            if (!$course) {
                return response()->json([
                    "status" => "failed",
                    "message" => "there was an issue creating the course!",
                ], 401);
            }

            return response()->json([
                "status" => "ok",
                "message" => "course has been created successfully",
                "data" => $course,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "status" => "failed",
                "message" => "failed to create a course.",
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function showCourses()
    {
        $courses = CoursesModel::all();
        return response()->json([
            "status" => "success",
            "message" => "the following are all courses available.",
            "data" => $courses,
        ]);
    }

    public function getCourseById($course_id)
    {
        $course = CoursesModel::find($course_id);

        if (!$course) {
            return response()->json([
                "status" => "failed",
                "message" => "course not found",
            ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "the following is the course that contains id=$course_id",
            "data" => $course,
        ]);
    }

    public function editCourse($course_id, Request $request)
    {
        $course = CoursesModel::find($course_id);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|min:26',
            'video_url' => 'nullable|mimes:mp4,mov,avi,wmv|max:250600',
            'image_url' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:50048',
        ]);

        $imagePath = $course->image_url;
        $videoPath = $course->video_url;

        if ($request->has('title')) $course->title = $request->title;
        if ($request->has('description')) $course->description = $request->description;
        if ($request->hasFile('video_url')) {
            $videoPath = $request->file('video_url')->store('course_videos', 'public');
            $course->video_url = $videoPath;
        }
        if ($request->hasFile('image_url')) {
            $imagePath = $request->file('image_url')->store('Course_images', 'public');
            $course->image_url = $imagePath;
        }

        $course->save();

        return response()->json([
            "status" => "success",
            "message" => "course has been updated successfuly!",
            "data" => $course,
        ]);
    }

    public function deleteCourse($course_id)
    {
        $course = coursesModel::find($course_id);

        if (!$course) {
            return response()->json([
                "status" => "failed",
                "message" => "can't delete missing course!",
            ], 401);
        }
        $course->delete();
        return response()->json([
            "status" => "success",
            "message" => "course with id=$course_id has bees deleted successfully!",
        ]);
    }
}
