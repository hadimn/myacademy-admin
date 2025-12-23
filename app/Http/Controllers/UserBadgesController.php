<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserBadgesResource;
use App\Models\UserBadgesModel;

class UserBadgesController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = UserBadgesModel::class;
        $this->resourceName = 'User Badge';
        $this->resourceClass = UserBadgesResource::class;
        $this->validationRules = [
            "user_id" => "required|integer|exists:users,id",
            "badge_id" => "required|integer|exists:Badges,Badge_id",
            "earned_at" => "required|date|after_or_equal:today",
        ];

        $this->editValidationRules = [
            "user_id" => "sometimes|required|integer|exists:users,id",
            "badge_id" => "sometimes|required|integer|exists:Badges,Badge_id",
            "earned_at" => "sometimes|required|date|after_or_equal:today",
        ];
        $this->searchableFields = [
            "user_id",
            "badge_id",
        ];
    }
}
