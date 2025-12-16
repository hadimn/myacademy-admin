<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'section_id' => $this->section_id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'image_url'=> $this->image_url ? asset('/storage/'.$this->image_url) : null,
            'order' => $this->order,
            'is_last' => $this->is_last,
            'created_at' => $this->created_at,
        ];
    }
}
