<?php

namespace App\Shared\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Uniform JSON envelope for every API response, so API consumers can rely
 * on one predictable shape for both success and error payloads.
 */
class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            $extra = ['success' => true];
            if ($message !== null) {
                $extra['message'] = $message;
            }

            return $data->additional($extra)->response()->setStatusCode($status);
        }

        $payload = ['success' => true];
        if ($message !== null) {
            $payload['message'] = $message;
        }
        $payload['data'] = $data;

        return response()->json($payload, $status);
    }

    public static function error(string $message, int $status = 400, array $errors = [], ?string $errorCode = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errorCode !== null) {
            $payload['error_code'] = $errorCode;
        }

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
