<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'current_streak' => $this->current_streak,
            'longest_streak' => $this->longest_streak,
            'last_activity_date' => $this->last_activity_date,
            'device_token' => $this->device_token,
            'created_at' => $this->created_at,
        ];
    }
}
