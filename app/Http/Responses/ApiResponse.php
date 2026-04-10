<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{

    public static function success(
        mixed $data = null,
        string $message = 'Success.',
        int $status = 200,
        array $headers = []
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status, $headers);
    }

    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully.'
    ): JsonResponse {
        return self::success($data, $message, 201);
    }

    public static function noContent(string $message = 'No content.'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], 204);
    }


    public static function error(
        string $message = 'Something went wrong.',
        int $status = 500,
        mixed $errors = null
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    public static function badRequest(
        string $message = 'Bad request.',
        mixed $errors = null
    ): JsonResponse {
        return self::error($message, 400, $errors);
    }

    public static function unauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return self::error($message, 403);
    }

    public static function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return self::error($message, 404);
    }

    public static function conflict(
        string $message = 'Conflict.',
        mixed $errors = null
    ): JsonResponse {
        return self::error($message, 409, $errors);
    }

    public static function validationError(
        mixed $errors,
        string $message = 'Validation failed.'
    ): JsonResponse {
        return self::error($message, 422, $errors);
    }

    public static function tooManyRequests(string $message = 'Too many requests.'): JsonResponse
    {
        return self::error($message, 429);
    }

    public static function internalServerError(
        string $message = 'Internal server error.'
    ): JsonResponse {
        return self::error($message, 500);
    }

    public static function serviceUnavailable(
        string $message = 'Service unavailable.'
    ): JsonResponse {
        return self::error($message, 503);
    }
}