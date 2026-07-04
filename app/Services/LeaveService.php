<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\CompanySettingServiceInterface;
use App\Contracts\LeaveServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Models\LeaveApply;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service for HRMS leave application management.
 */
class LeaveService implements LeaveServiceInterface
{
    /** Allowed persisted leave statuses for current schema. */
    protected const STATUSES = ['Pending', 'Approved', 'Rejected'];

    /** Create a new leave service instance. */
    public function __construct(
        protected LeaveApply $leaveApply,
        protected LeaveType $leaveType,
        protected UserServiceInterface $userService,
        protected AttendanceServiceInterface $attendanceService,
        protected CompanySettingServiceInterface $companySettingService
    ) {
    }

    /** Get paginated leave applications. */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->leaveApply
            ->with(['user.userDetail', 'leaveType', 'approvedBy'])
            ->latest()
            ->paginate($perPage);
    }

    /** Get a leave application by ID. */
    public function getById(int $id): LeaveApply
    {
        return $this->findLeave($id);
    }

    /** Employee submits a leave request. */
    public function applyLeave(int $userId, array $data): LeaveApply
    {
        $this->validateLeave($userId, $data);

        $from = $this->parseDate($data['from_date'], 'From date');
        $to = $this->parseDate($data['to_date'], 'To date');

        return DB::transaction(function () use ($userId, $data, $from, $to): LeaveApply {
            $leave = $this->leaveApply->create([
                'user_id' => $userId,
                'leave_type_id' => (int) $data['leave_type_id'],
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
                'total_days' => $this->calculateLeaveDays($from, $to),
                'reason' => $data['reason'] ?? null,
                'status' => 'Pending',
            ]);

            return $this->findLeave($leave->id);
        });
    }

    /** Validate leave request data. */
    public function validateLeave(int $userId, array $data): bool
    {
        $this->validateActiveUser($userId);

        if (! array_key_exists('leave_type_id', $data)) {
            throw new InvalidArgumentException('Leave type is required.');
        }

        $this->findLeaveType((int) $data['leave_type_id']);

        if (! array_key_exists('from_date', $data) || ! array_key_exists('to_date', $data)) {
            throw new InvalidArgumentException('Leave from date and to date are required.');
        }

        $from = $this->parseDate($data['from_date'], 'From date');
        $to = $this->parseDate($data['to_date'], 'To date');
        $today = Carbon::today();

        if ($from->lessThan($today)) {
            throw new InvalidArgumentException('Leave from date must be today or a future date.');
        }

        if ($to->lessThan($from)) {
            throw new InvalidArgumentException('Leave to date must be greater than or equal to from date.');
        }

        if ($this->hasOverlappingLeave($userId, $from, $to)) {
            throw new RuntimeException('Leave overlaps with an existing pending or approved leave request.');
        }

        return true;
    }

    /** Calculate leave duration in calendar days. */
    public function calculateLeaveDays(Carbon $from, Carbon $to): int
    {
        if ($to->lessThan($from)) {
            throw new InvalidArgumentException('Leave to date must be greater than or equal to from date.');
        }

        return $from->diffInDays($to) + 1;
    }

    /** Detect overlapping pending or approved leave. */
    public function hasOverlappingLeave(int $userId, Carbon $from, Carbon $to): bool
    {
        $this->validateUserExists($userId);

        if ($to->lessThan($from)) {
            throw new InvalidArgumentException('Leave to date must be greater than or equal to from date.');
        }

        return $this->leaveApply
            ->where('user_id', $userId)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where(function ($query) use ($from, $to): void {
                $query->whereBetween('from_date', [$from->toDateString(), $to->toDateString()])
                    ->orWhereBetween('to_date', [$from->toDateString(), $to->toDateString()])
                    ->orWhere(function ($query) use ($from, $to): void {
                        $query->where('from_date', '<=', $from->toDateString())
                            ->where('to_date', '>=', $to->toDateString());
                    });
            })
            ->exists();
    }

    /** Approve a pending leave request. */
    public function approveLeave(int $leaveId, ?int $approvedBy = null): LeaveApply
    {
        $approverId = $this->validateApprover($approvedBy);

        return DB::transaction(function () use ($leaveId, $approverId): LeaveApply {
            $leave = $this->findLeave($leaveId);
            $this->ensurePending($leave);

            $leave->update([
                'status' => 'Approved',
                'approved_by' => $approverId,
                'approved_at' => Carbon::now(),
            ]);

            return $this->findLeave($leave->id);
        });
    }

    /** Reject a pending leave request. */
    public function rejectLeave(int $leaveId, ?int $approvedBy = null): LeaveApply
    {
        $approverId = $this->validateApprover($approvedBy);

        return DB::transaction(function () use ($leaveId, $approverId): LeaveApply {
            $leave = $this->findLeave($leaveId);
            $this->ensurePending($leave);

            $leave->update([
                'status' => 'Rejected',
                'approved_by' => $approverId,
                'approved_at' => Carbon::now(),
            ]);

            return $this->findLeave($leave->id);
        });
    }

    /** Get leave by ID or null. */
    public function getLeave(int $leaveId): ?LeaveApply
    {
        if ($leaveId <= 0) {
            throw new InvalidArgumentException('Leave ID must be a positive integer.');
        }

        return $this->leaveApply
            ->with(['user.userDetail', 'leaveType', 'approvedBy'])
            ->find($leaveId);
    }

    /** Get leave applications for a user. */
    public function getUserLeaves(int $userId): Collection
    {
        $this->validateUserExists($userId);

        return $this->leaveApply
            ->with(['user.userDetail', 'leaveType', 'approvedBy'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /** Get pending leaves. */
    public function getPendingLeaves(): Collection
    {
        return $this->getLeavesByStatus('Pending');
    }

    /** Get approved leaves. */
    public function getApprovedLeaves(): Collection
    {
        return $this->getLeavesByStatus('Approved');
    }

    /** Get rejected leaves. */
    public function getRejectedLeaves(): Collection
    {
        return $this->getLeavesByStatus('Rejected');
    }

    /** Get leaves by status. */
    public function getLeavesByStatus(string $status): Collection
    {
        $this->validateStatus($status);

        return $this->leaveApply
            ->with(['user.userDetail', 'leaveType', 'approvedBy'])
            ->where('status', $status)
            ->latest()
            ->get();
    }

    /** Get leaves by month and year. */
    public function getLeavesByMonth(int $month, int $year): Collection
    {
        $this->validateMonthYear($month, $year);

        return $this->leaveApply
            ->with(['user.userDetail', 'leaveType', 'approvedBy'])
            ->whereMonth('from_date', $month)
            ->whereYear('from_date', $year)
            ->orderBy('from_date')
            ->get();
    }

    /** Get leaves between dates. */
    public function getLeavesBetweenDates(Carbon $from, Carbon $to): Collection
    {
        $this->validateDateRange($from, $to);

        return $this->leaveApply
            ->with(['user.userDetail', 'leaveType', 'approvedBy'])
            ->where(function ($query) use ($from, $to): void {
                $query->whereBetween('from_date', [$from->toDateString(), $to->toDateString()])
                    ->orWhereBetween('to_date', [$from->toDateString(), $to->toDateString()])
                    ->orWhere(function ($query) use ($from, $to): void {
                        $query->where('from_date', '<=', $from->toDateString())
                            ->where('to_date', '>=', $to->toDateString());
                    });
            })
            ->orderBy('from_date')
            ->get();
    }

    /** Get employee leave summary. */
    public function getEmployeeLeaveSummary(int $userId): array
    {
        $leaves = $this->getUserLeaves($userId);

        return [
            'total_applied' => $leaves->count(),
            'pending' => $leaves->where('status', 'Pending')->count(),
            'approved' => $leaves->where('status', 'Approved')->count(),
            'rejected' => $leaves->where('status', 'Rejected')->count(),
            'cancelled' => 0,
            'total_leave_days' => (int) $leaves->where('status', 'Approved')->sum('total_days'),
        ];
    }

    /** Get leave report for all employees. */
    public function getLeaveReport(int $month, int $year): Collection
    {
        return $this->getLeavesByMonth($month, $year);
    }

    /** Determine whether leave is approved. */
    public function isLeaveApproved(int $leaveId): bool
    {
        return $this->findLeave($leaveId)->status === 'Approved';
    }

    /** Determine whether leave is pending. */
    public function isLeavePending(int $leaveId): bool
    {
        return $this->findLeave($leaveId)->status === 'Pending';
    }

    /** Determine whether leave is rejected. */
    public function isLeaveRejected(int $leaveId): bool
    {
        return $this->findLeave($leaveId)->status === 'Rejected';
    }

    /** Delete a pending leave request. */
    public function deleteLeave(int $leaveId): bool
    {
        return DB::transaction(function () use ($leaveId): bool {
            $leave = $this->findLeave($leaveId);
            $this->ensurePending($leave);

            return (bool) $leave->delete();
        });
    }

    /** Find leave by ID. */
    protected function findLeave(int $leaveId): LeaveApply
    {
        if ($leaveId <= 0) {
            throw new InvalidArgumentException('Leave ID must be a positive integer.');
        }

        $leave = $this->leaveApply
            ->with(['user.userDetail', 'leaveType', 'approvedBy'])
            ->find($leaveId);

        if (! $leave instanceof LeaveApply) {
            throw new RuntimeException("Leave request [{$leaveId}] was not found.");
        }

        return $leave;
    }

    /** Find leave type by ID. */
    protected function findLeaveType(int $leaveTypeId): LeaveType
    {
        if ($leaveTypeId <= 0) {
            throw new InvalidArgumentException('Leave type ID must be a positive integer.');
        }

        $leaveType = $this->leaveType->find($leaveTypeId);

        if (! $leaveType instanceof LeaveType) {
            throw new RuntimeException("Leave type [{$leaveTypeId}] was not found.");
        }

        return $leaveType;
    }

    /** Validate a user exists. */
    protected function validateUserExists(int $userId): void
    {
        if ($userId <= 0 || ! $this->userService->userExists($userId)) {
            throw new RuntimeException("User [{$userId}] was not found.");
        }
    }

    /** Validate a user exists and is active. */
    protected function validateActiveUser(int $userId): User
    {
        $this->validateUserExists($userId);

        $user = $this->userService->getEmployeeProfile($userId);

        if (! $user instanceof User) {
            throw new RuntimeException("User [{$userId}] was not found.");
        }

        if (! $user->userDetail || ! (bool) $user->userDetail->status) {
            throw new RuntimeException("User [{$userId}] is inactive.");
        }

        return $user;
    }

    /** Validate and return approver ID. */
    protected function validateApprover(?int $approvedBy): int
    {
        if ($approvedBy === null) {
            throw new InvalidArgumentException('Approver is required.');
        }

        $this->validateActiveUser($approvedBy);

        return $approvedBy;
    }

    /** Ensure leave is pending. */
    protected function ensurePending(LeaveApply $leave): void
    {
        if ($leave->status !== 'Pending') {
            throw new RuntimeException('Only pending leave requests can be modified.');
        }
    }

    /** Parse a Carbon date from a value. */
    protected function parseDate(mixed $value, string $label): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy()->startOfDay();
        }

        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("{$label} is required.");
        }

        return Carbon::parse($value)->startOfDay();
    }

    /** Validate a supported status. */
    protected function validateStatus(string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Leave status must be Pending, Approved, or Rejected.');
        }
    }

    /** Validate month and year. */
    protected function validateMonthYear(int $month, int $year): void
    {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('Leave month must be between 1 and 12.');
        }

        if ($year < 2000 || $year > 2100) {
            throw new InvalidArgumentException('Leave year must be between 2000 and 2100.');
        }
    }

    /** Validate date range. */
    protected function validateDateRange(Carbon $from, Carbon $to): void
    {
        if ($to->lessThan($from)) {
            throw new InvalidArgumentException('End date must be greater than or equal to start date.');
        }
    }
}
