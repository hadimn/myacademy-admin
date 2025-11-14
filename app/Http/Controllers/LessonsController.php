<?php

namespace App\Http\Controllers;

use App\Models\LessonsModel;
use Illuminate\Http\Request;

class LessonsController extends Controller
{
    public function newLesson(Request $request)
    {
        try {
            $request->validate([
                "title" => "required|string|max:255",
                "description" => "required|string|min:6",
                "content" => "nullable|string|min:10",
                "video_url" => "nullable|mimes:mp4,mov,avi,wmv|max:250600",
                "image_url" => "nullable|mimes:jpg,jpeg,png,gif,svg|max:50048",
                "duration" => "required|integer|min:1|max:1000",
                "level" => "required|in:beginner,intermediate,advanced",
                "course_id" => "required|integer|exists:courses,course_id",
            ]);

            $imagePath = null;
            $videoPath = null;

            if ($request->hasFile('video_url')) {
                $videoPath = $request->file('video_url')->store('lesson_videos', 'public');
            }
            if ($request->hasFile('image_url')) {
                $imagePath = $request->file('image_url')->store('lesson_images', 'public');
            }

            $lesson = LessonsModel::create([
                "title" => $request->title,
                "description" => $request->description,
                "content" => $request->input("content"),
                "video_url" => $videoPath,
                "image_url" => $imagePath,
                "duration" => $request->duration,
                "level" => $request->level,
                "course_id" => $request->course_id,
            ]);

            if (!$lesson) {
                return response()->json([
                    "status" => "failed",
                    "message" => "failed to create a new lesson!",
                ]);
            }

            return response()->json([
                "status" => "success",
                "message" => "lesson created successfully!",
                "data" => $lesson,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "status" => "failed",
                "message" => "data is not valid",
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function showLessons()
    {
        $lessons = LessonsModel::all();
        return response()->json([
            "status" => "success",
            "message" => "the following are all the lessons available",
            "data" => $lessons,
        ]);
    }

    public function getLessonById($lesson_id)
    {
        $lesson = LessonsModel::find($lesson_id);
        if (!$lesson) {
            return response()->json([
                "status" => "failed",
                "message" => "can't find lesson with id = $lesson_id",
            ]);
        }
        return response()->json([
            "status" => "success",
            "message" => "the following is the lesson with id = $lesson_id",
            "data" => $lesson,
        ]);
    }

    public function editLesson($lesson_id, Request $request)
    {
        try {
            $lesson = LessonsModel::find($lesson_id);

            if (!$lesson) {
                return response()->json([
                    "status" => "failed",
                    "message" => "lesson of id = $lesson_id is not fount",
                ]);
            }

            $request->validate([
                "title" => "sometimes|required|string|max:255",
                "description" => "sometimes|required|string|min:6",
                "content" => "nullable|string|min:10",
                "video_url" => "nullable|mimes:mp4,mov,avi,wmv|max:250600",
                "image_url" => "nullable|mimes:jpg,jpeg,png,gif,svg|max:50048",
                "duration" => "sometimes|required|integer|min:1|max:1000",
                "level" => "sometimes|required|in:beginner,intermediate,advanced",
                "course_id" => "sometimes|required|integer|exists:courses,course_id",
            ]);

            if ($request->has('title')) $lesson->title = $request->title;
            if ($request->has('description')) $lesson->description = $request->description;
            if ($request->has('content')) $lesson->content = $request->input("content");
            if ($request->has('duration')) $lesson->duration = $request->duration;
            if ($request->has('level')) $lesson->level = $request->level;
            if ($request->has('course_id')) $lesson->course_id = $request->course_id;

            $imagePath = $lesson->image_url;
            $videoPath = $lesson->video_url;
            if ($request->hasFile('video_url')) {
                $videoPath = $request->file('video_url')->store('lesson_videos', 'public');
                $lesson->video_url = $videoPath;
            }
            if ($request->hasFile('image_url')) {
                $imagePath = $request->file('image_url')->store('lesson_images', 'public');
                $lesson->image_url = $imagePath;
            }

            $lesson->save();

            return response()->json([
                "status" => "success",
                "message" => "lesson has been updated successfuly!",
                "data" => $lesson,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "status" => "failed",
                "message" => "inputs are invalid, please check your inputs",
                "error" => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failed",
                "message" => "there was an error while editing your lesson!",
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function deleteLesson($lesson_id)
    {
        try {
            $lesson = LessonsModel::find($lesson_id);
            if (!$lesson) {
                return response()->json([
                    "status" => "failed",
                    "message" => "lesson is not available or does not exist",
                ], 401);
            }
            if ($lesson->delete()) {
                return response()->json([
                    "status" => "success",
                    "message" => "lesson \"$lesson->title\" has been deleted successfuly",
                ], 200);
            } else {
                return response()->json([
                    "status" => "failed",
                    "message" => "lesson \"$lesson->title\" can't be deleted, try again!",
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failed",
                "message" => "there was an error, please check before deleting again",
                "error" => $e->getMessage(),
            ]);
        }
    }
}
