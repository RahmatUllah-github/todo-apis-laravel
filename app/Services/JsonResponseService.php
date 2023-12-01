<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class JsonResponseService
{
    /**
     * Generate a JSON response with a success status.
     *
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    public static function successResponse(string $message, $data = []): JsonResponse
    {
        return self::jsonResponse($message, $data, 200);
    }

    /**
     * Generate a JSON response with a validation error status.
     *
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    public static function validationErrorResponse(string $message, $data = []): JsonResponse
    {
        return self::jsonResponse($message, $data, 422);
    }

    /**
     * Generate a JSON response with an unauthorized error status.
     *
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    public static function unauthorizedErrorResponse(string $message, $data = []): JsonResponse
    {
        return self::jsonResponse($message, $data, 403);
    }

    /**
     * Generate a JSON response with an error status.
     *
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    public static function errorResponse(string $message, $data = []): JsonResponse
    {
        return self::jsonResponse($message, $data, 500);
    }

    /**
     * Generate a JSON response with a not found error status.
     *
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    public static function notFoundErrorResponse(string $message, $data = []): JsonResponse
    {
        return self::jsonResponse($message, $data, 404);
    }

    /**
     * Helper function to generate a JSON response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    private static function jsonResponse(string $message, $data, int $code): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
