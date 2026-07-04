<?php

declare(strict_types=1);

namespace App\Contracts\Common;

interface EmployeeCodeServiceInterface
{
    public function generateEmployeeCode(): string;

    public function employeeCodeExists(string $code): bool;

    public function nextSequence(): int;
}
