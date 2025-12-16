<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserprogressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'progress_id' => $this->progress_id,
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'section_id' => $this->section_id,
            'unit_id' => $this->unit_id,
            'lesson_id' => $this->lesson_id,
            'is_completed' => $this->is_completed,
            'time_spent' => $this->time_spent,
            'points' => $this->points,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
        ];
    }
}
