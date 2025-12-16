<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionsAnsweredResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'answered_id' => $this->answered_id,
            'user_id' => $this->user_id,
            'questions_id' => $this->questions_id,
            'earned_points' => $this->earned_points,
            'is_passed' => $this->is_passed,
            'created_at' => $this->created_at,
        ];
    }
}
