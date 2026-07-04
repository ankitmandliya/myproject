<?php

declare(strict_types=1);

namespace App\Contracts\Common;

interface ResponseServiceInterface
{
    public function success(string $message = '', mixed $data = []): array;

    public function error(string $message = '', mixed $data = [], int $code = 400): array;

    public function validationError(array $errors, string $message = 'Validation failed.'): array;

    public function notFound(string $message = 'Resource not found.'): array;

    public function forbidden(string $message = 'Forbidden.'): array;
}
