<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\Request;

class AdminsController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = Admin::class;
        $this->resourceName = "admin";
        $this->resourceClass = AdminResource::class;
        $this->searchableFields = ['name', 'email'];
        $this->validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
        ];
        $this->editValidationRules = [
            'name' => 'sometimes|required|string|max:255',
            'password' => 'sometimes|required|string|min:6|confirmed',
        ];
        $this->searchableFields = ['name', 'email'];
    }
}
