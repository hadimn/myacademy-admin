<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\CoursesModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoursesController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = CoursesModel::class;
        $this->resourceName = "Course";
        $this->resourceClass = CourseResource::class;
        $this->validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:26',
            'level' => 'required|string|in:beginner,intermediate,advanced',
            'topics' => 'required|json',
            'video_url' => 'nullable|mimes:mp4,mov,avi,wmv|max:250600',
            'image_url' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:50048',
            'language' => 'required|string|min:2',
            'order' => 'required|integer|min:0',
        ];
        $this->editValidationRules = [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|min:26',
            'level' => 'sometimes|required|string|in:beginner,intermediate,advanced',
            'topics' => 'sometimes|required|json',
            'video_url' => 'nullable|mimes:mp4,mov,avi,wmv|max:250600',
            'image_url' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:50048',
            'language' => 'sometimes|required|string|min:2',
            'order' => 'sometimes|required|integer|min:0',
        ];
        $this->fileFields = [
            'video_url',
            'image_url',
        ];
        $this->searchableFields = [
            'title',
            'description',
            'language',
        ];
    }

    // create a search method to search for a specific course by searchable fields no need for pagination just get the result
    public function search(Request $request)
    {
        try {
            $query = $this->model::query();

            if ($request->has('search') && !empty($request->search) && !empty($this->searchableFields)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    foreach ($this->searchableFields as $field) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                });
            } else {
                return $this->errorResponse(
                    "Please provide a search query.",
                    Response::HTTP_BAD_REQUEST
                );
            }

            $data = $query->get();

            if ($data->isEmpty()) {
                return $this->successResponse(
                    [],
                    "No {$this->resourceName}s found matching your search.",
                    Response::HTTP_OK
                );
            }

            return $this->successResponse(
                $this->wrapResource($data),
                "{$this->resourceName}s retrieved successfully",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to search {$this->resourceName}s",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }
    
}
