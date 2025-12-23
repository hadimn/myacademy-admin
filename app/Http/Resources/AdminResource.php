<?php

namespace App\Http\Resources;

use App\Models\Admin;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
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
            'created_at' => $this->createdAt,
            'last_used_at' => $this->latestToken?->last_used_at,
        ];
    }
}
