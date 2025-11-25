<?php

namespace App\Http\Controllers;

use App\Models\BadgesModel;

class BadgesController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = BadgesModel::class;
        $this->resourceName = "Badge";
        $this->validationRules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:6',
            'icon' => 'nullable|mimes:jpg,jpeg,png|max:60000',
            'type' => 'required|string|in:completion,performance,streak,milestone',
            'criteria' => 'required|json',
            'points' => 'required|integer|min:1',
        ];
        $this->editValidationRules = [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|min:6',
            'icon' => 'nullable|mimes:jpg,jpeg,png|max:60000',
            'type' => 'sometimes|required|string|in:completion,performance,streak,milestone',
            'criteria' => 'sometimes|required|json',
            'points' => 'sometimes|required|integer|min:1',
        ];
        $this->fileFields = [
            'icon',
        ];
    }
}
