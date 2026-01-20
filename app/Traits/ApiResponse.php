<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected const API_VERSION = '1.0';

    protected function success($data = null, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'api_version' => self::API_VERSION,
            ],
        ], $code)->header('X-API-Version', self::API_VERSION);
    }

    protected function error(string $message, int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => [
                'api_version' => self::API_VERSION,
            ],
        ], $code)->header('X-API-Version', self::API_VERSION);
    }

    protected function paginated($data, string $message = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'api_version' => self::API_VERSION,
            ],
        ])->header('X-API-Version', self::API_VERSION);
    }
}
