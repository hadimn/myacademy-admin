<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class BaseCrudController extends Controller
{
    use ApiResponseTrait;

    protected $model;
    protected $validationRules = [];
    protected $editValidationRules = [];
    protected $fileFields = [];
    protected $resourceName = 'resource';
    protected $resourceClass = null;
    protected $searchableFields = [];

    public function index(Request $request)
    {
        try {
            $query = $this->model::query();

            // Handle search if searchableFields are defined
            if ($request->has('search') && !empty($request->search) && !empty($this->searchableFields)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    foreach ($this->searchableFields as $field) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                });
            }

            // Use paginate instead of get to enable pagination.
            $perPage = $request->input('per_page', 5); // Default to 5 items per page, or get from request.
            $data = $query->paginate($perPage);

            return $this->successResponse(
                $this->wrapResource($data),
                "All {$this->resourceName}s retrieved successfully",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve {$this->resourceName}",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->validationRules);

            // Decode criteria if it exists
            if (isset($validated['criteria']) && is_string($validated['criteria'])) {
                $validated['criteria'] = json_decode($validated['criteria'], true);
            } 

            if (isset($validated['password']) && !empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $resource = $this->model::make($validated);

            $this->handleFileUploads($request, $resource);

            $resource->save();

            return $this->successResponse(
                $this->wrapResource($resource),
                "{$this->resourceName} created successfully",
                Response::HTTP_CREATED, //201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                [$e->getMessage()],
                "failed to store, due to an invalid input.",
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to create {$this->resourceName}",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    public function show($id)
    {
        try {
            $resource = $this->model::find($id);

            if (!$resource) {
                return $this->notFoundResponse($id);
            }

            return $this->successResponse(
                $this->wrapResource($resource),
                "{$this->resourceName} with id: $id retrieved successfully",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve {$this->resourceName}",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $resource = $this->model::find($id);

            if (!$resource) {
                return $this->notFoundResponse($id);
            }

            $validated = $request->validate($this->editValidationRules);

            foreach ($validated as $key => $value) {
                if ($request->has($key)) {
                    if ($key === 'criteria' && is_string($value)) {
                        $value = json_decode($value, true);
                    }
                    if ($key === 'password' && !empty($value)) {
                        $value = Hash::make($value);
                    }
                    $resource->$key = $value;
                }
            }

            $this->handleFileUploads($request, $resource);

            $resource->save();

            return $this->successResponse(
                $this->wrapResource($resource),
                "{$this->resourceName} updated successfully",
                Response::HTTP_OK,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                [$e->getMessage()],
                'failed to update due to an invalid inputs!',
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to update {$this->resourceName}",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    public function destroy($id)
    {
        try {
            $resource = $this->model::find($id);

            if (!$resource) {
                return $this->notFoundResponse($id);
            }

            if (!$resource->delete()) {
                return $this->errorResponse(
                    "Failed to delete {$this->resourceName}",
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            return $this->successResponse(
                $this->wrapResource($resource),
                "{$this->resourceName} deleted successfully",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to delete {$this->resourceName}",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    // Helper methods
    protected function handleFileUploads(Request $request, $resource)
    {
        foreach ($this->fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if it exists
                if (!empty($resource->$field)) {
                    // Normalize slashes to forward
                    $oldFile = str_replace('\\', '/', $resource->$field);

                    if (Storage::disk('public')->exists($oldFile)) {
                        Storage::disk('public')->delete($oldFile);
                    }
                }

                $path = $request->file($field)->store('uploads/' . $this->resourceName . '/' . $field, 'public');
                $resource->$field = $path;
            }
        }
    }


    protected function notFoundResponse($id)
    {
        return response()->json([
            'status' => 'failed',
            'message' => "{$this->resourceName} with id: $id not found",
        ], 404);
    }

    protected function wrapResource($data)
    {
        if (!$this->resourceClass) {
            return $data;
        }

        if ($data instanceof \Illuminate\Support\Collection || $data instanceof \Illuminate\Database\Eloquent\Collection || $data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return ($this->resourceClass)::collection($data);
        }

        return new $this->resourceClass($data);
    }
}
