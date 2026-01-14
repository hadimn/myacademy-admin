<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserShopResource extends JsonResource
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
            'level' => $this->level,
            'language' => $this->language,
            'image_url' => $this->image_url ? asset('storage/' . $this->image_url) : null,
            'video_url' => $this->video_url ? asset('storage/' . $this->video_url) : null,
            'topics' => json_decode($this->topics),
            'order' => $this->order,
            'created_at' => $this->created_at,
            'is_enrolled' => $this->is_enrolled ?? false,
            'pricing' => new CoursePricingResource($this->pricing),
        ];
    }
}
