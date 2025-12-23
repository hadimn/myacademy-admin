<?php

namespace App\Http\Controllers;

use App\Http\Resources\LessonsResource;
use App\Models\LessonsModel;
use Illuminate\Http\Request;

class LessonsController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = LessonsModel::class;
        $this->resourceName = 'lesson';
        $this->resourceClass = LessonsResource::class;
        $this->validationRules = [
            "unit_id" => "required|integer|exists:units,unit_id",
            "title" => "required|string|max:255",
            "description" => "required|string|min:6",
            "content" => "nullable|string|min:10",
            "video_url" => "nullable|mimes:mp4,mov,avi,wmv,m4a|max:250600",
            "image_url" => "nullable|mimes:jpg,jpeg,png,gif,svg|max:50048",
            "duration" => "required|integer|min:1|max:1000",
            "lesson_type" => "required|string|in:normal,review,practice",
            "is_last" => "nullable|boolean",
            "chest_after" => "nullable|boolean",
            "order" => "required|integer",
        ];
        $this->editValidationRules = [
            "unit_id" => "sometimes|required|integer|exists:units,unit_id",
            "title" => "sometimes|required|string|max:255",
            "description" => "sometimes|required|string|min:6",
            "content" => "nullable|string|min:10",
            "video_url" => "nullable|mimes:mp4,mov,avi,wmv,m4a|max:250600",
            "image_url" => "nullable|mimes:jpg,jpeg,png,gif,svg|max:50048",
            "duration" => "sometimes|required|integer|min:1|max:1000",
            "lesson_type" => "sometimes|required|string|in:normal,review,practice",
            "is_last" => "nullable|boolean",
            "chest_after" => "nullable|boolean",
            "order" => "sometimes|required|integer",
        ];
        $this->fileFields = [
            'video_url',
            'image_url',
        ];
        $this->searchableFields = [
            'title',
            'description',
            'lesson_type',
        ];
    }
}
