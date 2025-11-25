<?php

namespace App\Http\Controllers;

use App\Models\CoursePricingModel;

class CoursePricingController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = CoursePricingModel::class;
        $this->resourceName = "Course Pricing";
        $this->validationRules = [
            "course_id" => "required|integer|exists:courses,course_id",
            "price" => "required|numeric|min:0",
            "is_free" => "required|boolean",
            "discount_price" => "nullable|numeric|min:0",
            "discount_expires_at" => "nullable|date|after_or_equal:today",
        ];
        $this->editValidationRules = [
            "course_id" => "required|integer|exists:courses,course_id",
            "price" => "required|numeric|min:0",
            "is_free" => "required|boolean",
            "discount_price" => "nullable|numeric|min:0",
            "discount_expires_at" => "nullable|date|after_or_equal:today",
        ];
    }
}
