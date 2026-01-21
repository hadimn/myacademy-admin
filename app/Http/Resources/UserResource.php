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
            'profile_image' => $this->profile_image ? asset('/storage/' . $this->profile_image) : null,
            'username' => $this->username,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'created_at' => $this->created_at,
            // courses_completed:
            'stats' => [
                'courses_completed' => $this->enrollments()->where('completed_at', '!=', null)->count(),
                'badges_count' => $this->badges()->count(),
                'courses_enrolled' => $this->enrollments(),
                'courses_in_progress' => $this->enrollments()->where('completed_at', null)->count(),
            ]
        ];
    }
}
