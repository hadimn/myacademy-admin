<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'unit_id' => $this->unit_id,
            'section_id' => $this->section_id,
            'title' => $this->title,
            'color' => $this->color,
            'order' => $this->order,
            'is_last' => $this->is_last,
            'created_at' => $this->created_at,
        ];
    }
}
