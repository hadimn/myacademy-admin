<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'questions_id' => $this->questions_id,
            'lesson_id' => $this->lesson_id,
            'title' => $this->title,
            'description' => $this->description,
            'question_type' => $this->question_type,
            'video_url' => $this->video_url ? asset('/storage/'.$this->video_url):null,
            'image_url'=> $this->image_url ? asset('/storage/'.$this->image_url) : null,
            'points' => $this->points,
            'is_last' => $this->is_last,
            'options' => $this->options,
            'correct_answer' => $this->correct_answer,
            'explanation' => $this->explanation,
            'order' => $this->order,
            'created_at' => $this->created_at,
        ];
    }
}
