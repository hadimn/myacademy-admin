<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'video_url' => $this->video_url ? asset('/storage/'.$this->video_url):null,
            'image_url'=> $this->image_url ? asset('/storage/'.$this->image_url) : null,
            'language' => $this->language,
            'order' => $this->order,
            'created_at' => $this->created_at,
        ];
    }
}
