<?php

namespace App\Http\Controllers;

use App\Models\QuestionsModel;
use Illuminate\Http\Request;

class QuestionsController extends Controller
{
    public function newQuestion(Request $request)
    {
        try {
            $request->validate([
                "lesson_id" => "required|integer|exists:lessons,lesson_id",
                "title" => "required|string|max:255",
                "description" => "required|string|min:6",
                "question_type" => "required|in:mcq, fill, torf, checkbox, matching",
                "video_url" => "nullable|mimes:mp4,mov,avi,wmv|max:250600",
                "image_url" => "nullable|mimes:jpg,jpeg,png,gif,svg|max:50048",
                "points" => "required|integer|min:2|max:5",
                "difficulty" => "required|in:easy, medium, hard",
                "options" => "nullable|json",
                "correct_answer" => "required|json",
                "explanation" => "required|string|min:6",
                "order" => "required|integer|min:1",
            ]);

            $imagePath = null;
            $videoPath = null;

            if ($request->hasFile('image_url')) {
                $imagePath = $request->file('image_url')->store('question_images', 'public');
            }

            if ($request->hasFile('video_url')) {
                $videoPath = $request->file('video_url')->store('question_videos', 'public');
            }

            $question = QuestionsModel::create([
                "lesson_id" => $request->lesson_id,
                "title" => $request->title,
                "description" => $request->description,
                "question_type" => $request->question_type,
                "video_url" => $videoPath,
                "image_url" => $imagePath,
                "points" => $request->points,
                "difficulty" => $request->difficulty,
                "options" => $request->options,
                "correct_answer" => $request->correct_answer,
                "explanation" => $request->explanation,
                "order" => $request->order,
            ]);

            if (!$question) {
                return response()->json([
                    "status" => "failed",
                    "message" => "can't create question!",
                ]);
            }

            return response()->json([
                "status" => "success",
                "message" => "question created successfuly",
                "data" => $question,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "status" => "failed",
                "message" => "your input is invalid",
                "error" => $e->getMessage(),
            ]);
        }
    }
}
