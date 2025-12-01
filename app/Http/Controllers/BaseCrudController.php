<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
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

    public function index()
    {
        try {
            $data = $this->model::all();

            return $this->successResponse(
                $data,
                "All {$this->resourceName}s retrieved successfully",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to retrieve {$this->resourceName}",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage(),],
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->validationRules);

            $resource = $this->model::make($validated);

            $this->handleFileUploads($request, $resource);

            $resource->save();

            return $this->successResponse(
                $resource,
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
                $resource,
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
                    $resource->$key = $value;
                }
            }

            $this->handleFileUploads($request, $resource);

            $resource->save();

            return $this->successResponse(
                $resource,
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
                $resource,
                "{$this->resourceName} deleted successfully",
                Response::HTTP_NO_CONTENT,
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
}
