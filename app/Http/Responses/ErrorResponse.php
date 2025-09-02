<?php

namespace App\Http\Responses;

use App\Constants\ApiConstants;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class ErrorResponse
{
    public static function make(
        string $message,
        int $statusCode = ApiConstants::HTTP_BAD_REQUEST,
        string $errorType = ApiConstants::ERROR_TYPE_SERVER,
        array $errors = [],
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'error' => [
                'type' => $errorType,
                'message' => $message,
            ],
        ];

        if (!empty($errors)) {
            $response['error']['details'] = $errors;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    public static function notFound(
        string $message = ApiConstants::ERROR_POST_NOT_FOUND
    ): JsonResponse {
        return self::make(
            message: $message,
            statusCode: ApiConstants::HTTP_NOT_FOUND,
            errorType: ApiConstants::ERROR_TYPE_NOT_FOUND
        );
    }

    public static function validation(
        array $errors,
        string $message = ApiConstants::ERROR_INVALID_PARAMETERS
    ): JsonResponse {
        return self::make(
            message: $message,
            statusCode: ApiConstants::HTTP_UNPROCESSABLE_ENTITY,
            errorType: ApiConstants::ERROR_TYPE_VALIDATION,
            errors: $errors
        );
    }

    public static function unauthorized(
        string $message = 'Unauthorized access'
    ): JsonResponse {
        return self::make(
            message: $message,
            statusCode: ApiConstants::HTTP_UNAUTHORIZED,
            errorType: ApiConstants::ERROR_TYPE_UNAUTHORIZED
        );
    }

    public static function forbidden(
        string $message = 'Access forbidden'
    ): JsonResponse {
        return self::make(
            message: $message,
            statusCode: ApiConstants::HTTP_FORBIDDEN,
            errorType: ApiConstants::ERROR_TYPE_UNAUTHORIZED
        );
    }

    public static function serverError(
        string $message = ApiConstants::ERROR_SERVER_ERROR,
        ?Throwable $exception = null
    ): JsonResponse {
        $meta = [];

        if ($exception && config('app.debug')) {
            $meta['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'message' => $exception->getMessage(),
            ];
        }

        return self::make(
            message: $message,
            statusCode: ApiConstants::HTTP_INTERNAL_SERVER_ERROR,
            errorType: ApiConstants::ERROR_TYPE_SERVER,
            meta: $meta
        );
    }

    public static function fromValidationException(ValidationException $exception): JsonResponse
    {
        return self::validation(
            errors: $exception->errors(),
            message: $exception->getMessage()
        );
    }
}
