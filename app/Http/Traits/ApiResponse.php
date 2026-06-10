<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse($data, string $message = 'Operation successful', int $statusCode = 200, array $meta = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    protected function errorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        $statusCode = $this->safeStatusCode($statusCode);

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => [],
        ], $statusCode);
    }

    private function safeStatusCode(int $statusCode): int
    {
        return $statusCode >= 100 && $statusCode <= 599 ? $statusCode : 400;
    }
}