<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentsModel;

class EnrollmentsController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = EnrollmentsModel::class;
        $this->resourceName = 'Enrollment';
        $this->validationRules = [
            "user_id"=>"required|integer|exists:users,id",
            "course_id" => "required|integer|exists:courses,course_id",
            "amount_paid" => "nullable|numeric|min:0",
            "payment_status" => "required|string|in:pending,paid,failed,refunded,canceled",
            "payment_method" => "required|string",
            "transaction_id" =>"nullable|string",
            "enrolled_at" => "nullable|date|after_or_equal:today",
            "completed_at" => "nullable|date",
        ];
        $this->editValidationRules = [
            "user_id"=>"sometimes|required|integer|exists:users,id",
            "course_id" => "sometimes|required|integer|exists:courses,course_id",
            "amount_paid" => "nullable|numeric|min:0",
            "payment_status" => "sometimes|required|string|in:pending,paid,failed,refunded,canceled",
            "payment_method" => "sometimes|required|string",
            "transaction_id" =>"nullable|string",
            "enrolled_at" => "nullable|date|after_or_equal:today",
            "completed_at" => "nullable|date",
        ];
    }
}
