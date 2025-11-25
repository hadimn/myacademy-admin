<?php

namespace App\Http\Controllers;

use App\Models\SectionsModel;
use Illuminate\Http\Request;

class SectionsController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = SectionsModel::class;
        $this->resourceName = "section";
        $this->validationRules = [
            'course_id' => 'required|integer|exists:courses,course_id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:6|',
            'image_url' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:50000',
            'order' => 'required|integer|min:1',
            'is_last' => 'nullable|boolean',
        ];
        $this->editValidationRules = [
            'course_id' => 'sometimes|required|integer|exists:courses,course_id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|min:6|',
            'image_url' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:50000',
            'order' => 'required|integer|min:1',
            'is_last' => 'nullable|boolean',
        ];
        $this->fileFields = [
            'image_url',
        ];
    }
}
