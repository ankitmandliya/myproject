<?php

declare(strict_types=1);

namespace App\Contracts\Common;

interface CommonServiceInterface
{
    public function isActive(bool $status): bool;

    public function generateUuid(): string;

    public function generateReferenceNumber(string $prefix): string;

    public function sanitizeString(string $value): string;

    public function emptyToNull(mixed $value): mixed;
}
