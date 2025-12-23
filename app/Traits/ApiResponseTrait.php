<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    // ... (Existing successResponse and errorResponse methods) ...

    /**
     * Return a new JSON response for successful operations.
     *
     * @param array<mixed>|object|null $data
     * @param string|null $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse(
        array|object|null $data = null,
        ?string $message = null,
        int $statusCode = Response::HTTP_OK,
        bool $status = true,
    ): JsonResponse {
        $response = [
            'status'  => $status,
            'message' => $message,
        ];

        // Check if the data is a paginated collection
        if ($data instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection && $data->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $paginator         = $data->resource;
            $response['data'] = $data->collection;
            $response['total']       = $paginator->total();
            $response['per_page']    = $paginator->perPage();
            $response['current_page'] = $paginator->currentPage();
            $response['last_page']     = $paginator->lastPage();
        } else {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a new JSON response for error operations.
     *
     * @param string|null $message
     * @param int $statusCode
     * @param array<string, mixed>|object|null $errors
     * @return JsonResponse
     */
    protected function errorResponse(
        ?string $message = null,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        array|object|null $errors = null
    ): JsonResponse {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $statusCode);
    }

    /**
     * Return a new JSON response for validation errors (HTTP 422).
     *
     * @param array<string, mixed> $errors
     * @param string|null $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(
        array $errors,
        ?string $message = 'Validation failed.'
    ): JsonResponse {
        return $this->errorResponse(
            $message,
            Response::HTTP_UNPROCESSABLE_ENTITY, // 422
            $errors
        );
    }
}
