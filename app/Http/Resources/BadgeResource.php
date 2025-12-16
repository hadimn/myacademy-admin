<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'badge_id' => $this->badge_id,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon ? asset('/storage/' . $this->icon) : null,
            'type' => $this->type,
            'criteria' => $this->criteria,
            'points' => $this->points,
        ];
    }
}
