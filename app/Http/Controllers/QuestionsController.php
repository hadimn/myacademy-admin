<?php

namespace App\Http\Controllers;

use App\Models\QuestionsModel;
use App\Models\UserProgressModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionsController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = QuestionsModel::class;
        $this->resourceName = 'question';
        $this->validationRules = [
            "lesson_id" => "required|integer|exists:lessons,lesson_id",
            "title" => "required|string|max:255",
            "description" => "required|string|min:6",
            "question_type" => "required|in:mcq,fill,torf,checkbox,matching",
            "video_url" => "nullable|mimes:mp4,mov,avi,wmv,m4a|max:250600",
            "image_url" => "nullable|mimes:jpg,jpeg,png,gif,svg|max:50048",
            "points" => "required|integer|min:2|max:5",
            "is_last" => "nulllable|boolean",
            "options" => "nullable|json",
            "correct_answer" => "required|json",
            "explanation" => "nullable|string|min:6",
            "order" => "required|integer|min:1",
        ];
        $this->editValidationRules = [
            "lesson_id" => "sometimes|required|integer|exists:lessons,lesson_id",
            "title" => "sometimes|required|string|max:255",
            "description" => "sometimes|required|string|min:6",
            "question_type" => "sometimes|required|in:mcq, fill, torf, checkbox, matching",
            "video_url" => "nullable|mimes:mp4,mov,avi,wmv|max:250600",
            "image_url" => "nullable|mimes:jpg,jpeg,png,gif,svg|max:50048",
            "points" => "sometimes|required|integer|min:2|max:5",
            "is_last" => "nulllable|boolean",
            "options" => "nullable|json",
            "correct_answer" => "sometimes|required|json",
            "explanation" => "nullable|string|min:6",
            "order" => "sometimes|required|integer|min:1",
        ];

        $this->fileFields = [
            'image_url',
            'video_url',
        ];
    }

}
