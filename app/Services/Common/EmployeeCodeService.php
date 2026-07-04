<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\Common\EmployeeCodeServiceInterface;
use App\Models\UserDetail;

class EmployeeCodeService implements EmployeeCodeServiceInterface
{
    public function __construct(
        protected UserDetail $userDetail
    ) {
    }

    public function generateEmployeeCode(): string
    {
        return sprintf('EMP%04d', $this->nextSequence());
    }

    public function employeeCodeExists(string $code): bool
    {
        return $this->userDetail->where('emp_code', strtoupper(trim($code)))->exists();
    }

    public function nextSequence(): int
    {
        $lastCode = $this->userDetail
            ->where('emp_code', 'like', 'EMP%')
            ->orderByRaw('CAST(SUBSTRING(emp_code, 4) AS UNSIGNED) DESC')
            ->value('emp_code');

        if (! is_string($lastCode) || $lastCode === '') {
            return 1;
        }

        $number = (int) preg_replace('/\D/', '', $lastCode);

        return $number + 1;
    }
}
