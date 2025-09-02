<?php

namespace App\Http\Responses;

use App\Constants\ApiConstants;
use Illuminate\Http\JsonResponse;

class SuccessResponse
{
    public static function make(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = ApiConstants::HTTP_OK,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    public static function paginated(
        array $data,
        array $pagination,
        string $message = 'Data retrieved successfully',
        int $statusCode = ApiConstants::HTTP_OK
    ): JsonResponse {
        return self::make(
            data: $data,
            message: $message,
            statusCode: $statusCode,
            meta: ['pagination' => $pagination]
        );
    }

    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return self::make($data, $message, ApiConstants::HTTP_CREATED);
    }

    public static function noContent(string $message = 'Operation completed successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], 204);
    }
}
