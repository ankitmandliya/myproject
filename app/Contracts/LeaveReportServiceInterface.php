<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LeaveReportServiceInterface
{
    public function dashboard(array $filters, ?User $actor = null): array;

    public function report(string $type, array $filters, int $perPage, ?User $actor = null): array;

    public function employeeDetails(int $employeeId, array $filters, ?User $actor = null): array;
}
