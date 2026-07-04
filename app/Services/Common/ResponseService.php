<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\Common\ResponseServiceInterface;

class ResponseService implements ResponseServiceInterface
{
    public function success(string $message = '', mixed $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    public function error(string $message = '', mixed $data = [], int $code = 400): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data,
            'code' => $code,
        ];
    }

    public function validationError(array $errors, string $message = 'Validation failed.'): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'code' => 422,
        ];
    }

    public function notFound(string $message = 'Resource not found.'): array
    {
        return $this->error($message, [], 404);
    }

    public function forbidden(string $message = 'Forbidden.'): array
    {
        return $this->error($message, [], 403);
    }
}
