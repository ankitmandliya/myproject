<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\LeaveApply;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Defines the leave service contract.
 */
interface LeaveServiceInterface
{
    /** Get paginated leave applications. */
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    /** Get a leave application by ID. */
    public function getById(int $id): LeaveApply;

    /** Apply employee leave. */
    public function applyLeave(int $userId, array $data): LeaveApply;

    /** Approve a leave request. */
    public function approveLeave(int $leaveId, ?int $approvedBy = null): LeaveApply;

    /** Reject a leave request. */
    public function rejectLeave(int $leaveId, ?int $approvedBy = null): LeaveApply;

    /** Validate leave request data. */
    public function validateLeave(int $userId, array $data): bool;

    /** Calculate leave duration in calendar days. */
    public function calculateLeaveDays(Carbon $from, Carbon $to): int;

    /** Determine whether leave overlaps pending or approved leave. */
    public function hasOverlappingLeave(int $userId, Carbon $from, Carbon $to): bool;

    /** Get leave by ID. */
    public function getLeave(int $leaveId): ?LeaveApply;

    /** Get leave applications for a user. */
    public function getUserLeaves(int $userId): Collection;

    /** Get pending leave applications. */
    public function getPendingLeaves(): Collection;

    /** Get approved leave applications. */
    public function getApprovedLeaves(): Collection;

    /** Get rejected leave applications. */
    public function getRejectedLeaves(): Collection;

    /** Get leave applications by status. */
    public function getLeavesByStatus(string $status): Collection;

    /** Get leave applications by month. */
    public function getLeavesByMonth(int $month, int $year): Collection;

    /** Get leave applications between dates. */
    public function getLeavesBetweenDates(Carbon $from, Carbon $to): Collection;

    /** Get employee leave summary. */
    public function getEmployeeLeaveSummary(int $userId): array;

    /** Get leave report for all employees. */
    public function getLeaveReport(int $month, int $year): Collection;

    /** Determine whether leave is approved. */
    public function isLeaveApproved(int $leaveId): bool;

    /** Determine whether leave is pending. */
    public function isLeavePending(int $leaveId): bool;

    /** Determine whether leave is rejected. */
    public function isLeaveRejected(int $leaveId): bool;

    /** Delete a pending leave request. */
    public function deleteLeave(int $leaveId): bool;
}
