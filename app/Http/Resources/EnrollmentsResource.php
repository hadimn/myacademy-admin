<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'enrollment_id' => $this->enrollment_id,
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'amount_paid' => $this->amount_paid,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'enrolled_at' => $this->enrolled_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
        ];
    }
}
