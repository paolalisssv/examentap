<?php

namespace App\Helpers;

use App\Enums\ApiResponseStatus;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => ApiResponseStatus::Success->value,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $status);
    }

    public static function error(string $message = '', mixed $errors = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status' => ApiResponseStatus::Error->value,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $status);
    }
}
