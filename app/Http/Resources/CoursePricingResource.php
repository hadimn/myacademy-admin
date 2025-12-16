<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoursePricingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pricing_id' => $this->pricing_id,
            'course_id' => $this->course_id,
            'price' => $this->price,
            'is_free' => $this->is_free,
            'discount_price' => $this->discount_price,
            'discount_expires_at' => $this->discount_expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
