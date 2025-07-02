<?php
// app/Http/Traits/Api/ApiResponseTrait.php

namespace App\Traits\Api;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    protected function successResponse(
        array  $data,
        string $message,
        int    $status = Response::HTTP_OK
    ): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'total' => count($data)
        ], $status);
    }

    protected function errorResponse(
        string $message,
        string $error = null,
        int    $status = Response::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($error) {
            $response['error'] = $error;
        }

        return response()->json($response, $status);
    }

    protected function handleException(\Exception $e, string $defaultMessage): JsonResponse
    {
        \Log::error($defaultMessage . ': ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return $this->errorResponse($defaultMessage, $e->getMessage());
    }
}
