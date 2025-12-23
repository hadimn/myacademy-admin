<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionsAnsweredResource;
use App\Models\AnsweredQuestionsModel;
use Illuminate\Http\Request;

class QuestionsAnsweredController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = AnsweredQuestionsModel::class;
        $this->resourceName = "answered question";
        $this->resourceClass = QuestionsAnsweredResource::class;
        $this->validationRules = [
            "user_id" => "required|integer|exists:users,id",
            "questions_id" => "required|integer|exists:questions,question_id",
            "earned_points" => "nullable|integer|min:0",
            "is_passed" => "nullable|boolean",
        ];
        $this->editValidationRules = [
            "user_id" => "sometimes|required|integer|exists:users,id",
            "questions_id" => "sometimes|required|integer|exists:questions,question_id",
            "earned_points" => "nullable|integer|min:0",
            "is_passed" => "nullable|boolean",
        ];
        $this->searchableFields = [
            "user_id",
            "questions_id",
        ];
    }
}
