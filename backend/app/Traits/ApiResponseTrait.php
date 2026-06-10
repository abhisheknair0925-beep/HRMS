<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Return a standardized success JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse(mixed $data, string $message = 'Operation completed successfully', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return a standardized error JSON response.
     *
     * @param string $message
     * @param int $code
     * @param array $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Something went wrong', int $code = 500, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
