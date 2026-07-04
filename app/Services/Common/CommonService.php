<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\Common\CommonServiceInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CommonService implements CommonServiceInterface
{
    public function isActive(bool $status): bool
    {
        return $status === true;
    }

    public function generateUuid(): string
    {
        return (string) Str::uuid();
    }

    public function generateReferenceNumber(string $prefix): string
    {
        $prefix = strtoupper($this->sanitizeString($prefix));

        if ($prefix === '') {
            throw new InvalidArgumentException('Reference prefix is required.');
        }

        return sprintf('%s-%s-%s', $prefix, now()->format('YmdHis'), Str::upper(Str::random(6)));
    }

    public function sanitizeString(string $value): string
    {
        return trim(strip_tags($value));
    }

    public function emptyToNull(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === '' ? null : $value;
    }
}
