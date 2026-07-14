<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\FinancialYearClosing;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FinancialYearClosingServiceInterface
{
    public function dashboard(?string $financialYear = null): array;

    public function preview(?string $financialYear = null): array;

    public function close(string $financialYear, User $actor, ?string $ipAddress = null): FinancialYearClosing;

    public function reopen(int $closingId, User $actor, ?string $ipAddress = null): FinancialYearClosing;

    public function history(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function show(int $closingId): array;
}
