<?php

namespace App\Http\Controllers;

use App\Models\coursesModel;
use Illuminate\Http\Request;

class CoursesController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = coursesModel::class;
        $this->resourceName = "course";
        $this->validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:26',
            'video_url' => 'nullable|mimes:mp4,mov,avi,wmv|max:250600',
            'image_url' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:50048',
            'language' => 'required|string|min:2',
        ];
        $this->editValidationRules = [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|min:26',
            'video_url' => 'nullable|mimes:mp4,mov,avi,wmv|max:250600',
            'image_url' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:50048',
            'language' => 'sometimes|required|string|min:2',
        ];
        $this->fileFields = [
            'video_url',
            'image_url',
        ];
    }
}
