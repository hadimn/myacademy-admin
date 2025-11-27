<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BaseCrudController extends Controller
{
    protected $model;
    protected $validationRules = [];
    protected $editValidationRules = [];
    protected $fileFields = [];
    protected $resourceName = 'resource';

    public function index()
    {
        try {
            $data = $this->model::all();

            return response()->json([
                'status' => 'success',
                'message' => "All {$this->resourceName}s retrieved successfully",
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, "Failed to retrieve {$this->resourceName}");
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->validationRules);

            $resource = $this->model::create($validated);

            $this->handleFileUploads($request, $resource);

            $resource->save();

            if (!$resource) {
                return $this->errorResponse("Couldn't create {$this->resourceName} due to an error");
            }

            return response()->json([
                'status' => 'success',
                'message' => "{$this->resourceName} created successfully",
                'data' => $resource,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            return $this->handleError($e, "Failed to create {$this->resourceName}");
        }
    }

    public function show($id)
    {
        try {
            $resource = $this->model::find($id);

            if (!$resource) {
                return $this->notFoundResponse($id);
            }

            return response()->json([
                'status' => 'success',
                'message' => "{$this->resourceName} with id: $id retrieved successfully",
                'data' => $resource,
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, "Failed to retrieve {$this->resourceName}");
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

            return response()->json([
                'status' => 'success',
                'message' => "{$this->resourceName} updated successfully",
                'data' => $resource,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            return $this->handleError($e, "Failed to update {$this->resourceName}");
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
                return $this->errorResponse("Failed to delete {$this->resourceName}");
            }

            return response()->json([
                'status' => 'success',
                'message' => "{$this->resourceName} deleted successfully",
                'data' => $resource,
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, "Failed to delete {$this->resourceName}");
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

    protected function handleError(\Exception $e, $message)
    {
        return response()->json([
            'status' => 'failed',
            'message' => $message,
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }

    protected function validationErrorResponse(\Illuminate\Validation\ValidationException $e)
    {
        return response()->json([
            'status' => 'failed',
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    }

    protected function notFoundResponse($id)
    {
        return response()->json([
            'status' => 'failed',
            'message' => "{$this->resourceName} with id: $id not found",
        ], 404);
    }

    protected function errorResponse($message)
    {
        return response()->json([
            'status' => 'failed',
            'message' => $message,
        ], 400);
    }
}
