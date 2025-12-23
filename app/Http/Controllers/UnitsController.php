<?php

namespace App\Http\Controllers;

use App\Http\Resources\UnitsResource;
use App\Models\UnitsModel;
use Illuminate\Http\Request;

class UnitsController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = UnitsModel::class;
        $this->resourceName = "Unit";
        $this->resourceClass = UnitsResource::class;
        $this->validationRules = [
            "section_id" => "required|integer|exists:sections,section_id",
            "title" => "required|string|max:255",
            "color" => "required|string|hex_color",
            "order" => "required|integer|min:0",
            "is_last" => "nullable|boolean",
        ];
        $this->editValidationRules = [
            "section_id" => "sometimes|required|integer|exists:sections,section_id",
            "title" => "sometimes|required|string|max:255",
            "color" => "sometimes|required|string|hex_color",
            "order" => "sometimes|required|integer|min:0",
            "is_last" => "nullable|boolean",
        ];
        $this->searchableFields = [
            "title",
            "color",
        ];
    }
}
