<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\LeaveApply;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LeaveApprovalServiceInterface
{
    public function initializeAfterApply(LeaveApply $leave, ?int $actorId = null): LeaveApply;

    public function getApprovalDashboard(array $filters = [], int $perPage = 25, ?int $actorId = null): LengthAwarePaginator;

    public function approve(int $leaveId, int $actorId, ?string $remarks = null): LeaveApply;

    public function reject(int $leaveId, int $actorId, string $remarks): LeaveApply;

    public function cancel(int $leaveId, int $actorId, ?string $remarks = null): LeaveApply;

    public function revoke(int $leaveId, int $actorId, ?string $remarks = null): LeaveApply;

    public function canAct(LeaveApply $leave, int $actorId, string $action): bool;

    /** @return array<int, string> */
    public function configuredLevels(): array;
}
