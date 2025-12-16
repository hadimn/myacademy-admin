<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'lesson_id' => $this->lesson_id,
            'unit_id' => $this->unit_id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'video_url' => $this->video_url ? asset('/storage/'.$this->video_url):null,
            'image_url'=> $this->image_url ? asset('/storage/'.$this->image_url) : null,
            'duration' => $this->duration,
            'lesson_type' => $this->lesson_type,
            'is_last' => $this->is_last,
            'chest_after' => $this->chest_after,
            'order' => $this->order,
            'created_at' => $this->created_at,
        ];
    }
}
