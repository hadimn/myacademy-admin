<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBadgesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_badge_id' => $this->user_badge_id,
            'user_id' => $this->user_id,
            'badge_id' => $this->badge_id,
            'earned_at' => $this->earned_at,
            'created_at' => $this->created_at,
        ];
    }
}
