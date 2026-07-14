<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\CompanySettingServiceInterface;
use App\Contracts\HolidayServiceInterface;
use App\Contracts\LeaveApprovalServiceInterface;
use App\Contracts\LeavePolicyServiceInterface;
use App\Contracts\LeaveServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Models\LeaveApply;
use App\Models\LeaveType;
use App\Services\LeaveApprovalService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as SupportCollection;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service for HRMS leave application management.
 */
class LeaveService implements LeaveServiceInterface
{
    /** Allowed persisted leave statuses for current schema. */
    protected const STATUSES = [
        LeaveApprovalService::STATUS_PENDING,
        LeaveApprovalService::STATUS_MANAGER_APPROVED,
        LeaveApprovalService::STATUS_HR_APPROVED,
        LeaveApprovalService::STATUS_APPROVED,
        LeaveApprovalService::STATUS_REJECTED,
        LeaveApprovalService::STATUS_CANCELLED,
        LeaveApprovalService::STATUS_REVOKED,
    ];

    /** Create a new leave service instance. */
    public function __construct(
        protected LeaveApply $leaveApply,
        protected LeaveType $leaveType,
        protected UserServiceInterface $userService,
        protected AttendanceServiceInterface $attendanceService,
        protected CompanySettingServiceInterface $companySettingService,
        protected HolidayServiceInterface $holidayService,
        protected LeavePolicyServiceInterface $leavePolicyService,
        protected LeaveApprovalServiceInterface $leaveApprovalService
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

    /** Get filtered, paginated leave applications. */
    public function getFilteredLeaves(array $filters = [], int $perPage = 10, ?int $userId = null): LengthAwarePaginator
    {
        $query = $this->leaveApply->with(['user.userDetail', 'leaveType', 'approvedBy']);

        if ($userId !== null) {
            $this->validateUserExists($userId);
            $query->where('user_id', $userId);
        }

        if (! empty($filters['employee'])) {
            $employee = strtolower(trim((string) $filters['employee']));
            $query->whereHas('user', function ($query) use ($employee): void {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$employee}%"])
                    ->orWhereHas('userDetail', function ($query) use ($employee): void {
                        $query->whereRaw("LOWER(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?", ["%{$employee}%"]);
                    });
            });
        }

        if (! empty($filters['employee_code'])) {
            $employeeCode = strtolower(trim((string) $filters['employee_code']));
            $query->whereHas('user.userDetail', function ($query) use ($employeeCode): void {
                $query->whereRaw('LOWER(emp_code) LIKE ?', ["%{$employeeCode}%"]);
            });
        }

        if (! empty($filters['department'])) {
            $department = strtolower(trim((string) $filters['department']));
            $query->whereHas('user.userDetail', function ($query) use ($department): void {
                $query->whereRaw('LOWER(department) LIKE ?', ["%{$department}%"]);
            });
        }

        if (! empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', (int) $filters['leave_type_id']);
        }

        if (! empty($filters['status'])) {
            $status = (string) $filters['status'];
            $this->validateStatus($status);
            $query->where('status', $status);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('from_date', '>=', $this->parseDate($filters['from_date'], 'From date')->toDateString());
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('to_date', '<=', $this->parseDate($filters['to_date'], 'To date')->toDateString());
        }

        return $query->latest()->paginate($this->validatedPerPage($perPage));
    }

    /** Get leave dashboard summary values. */
    public function getLeaveSummary(?int $userId = null): array
    {
        if ($userId !== null) {
            $this->validateUserExists($userId);
        }

        $baseQuery = $this->leaveApply->newQuery();
        if ($userId !== null) {
            $baseQuery->where('user_id', $userId);
            $balances = $this->leavePolicyService->getEmployeeBalances($userId);
            $totalBalance = (float) $balances->sum(fn ($balance): float => (float) $balance->allocated + (float) $balance->carry_forward);
            $usedLeave = (float) $balances->sum('used');
        } else {
            $totalBalance = (float) $this->leaveType->active()->sum('total_days');
            $usedLeave = (float) (clone $baseQuery)->where('status', LeaveApprovalService::STATUS_APPROVED)->sum('total_days');
        }

        return [
            'total_balance' => round($totalBalance, 2),
            'total_leave_balance' => round($totalBalance, 2),
            'used_leave' => round($usedLeave, 2),
            'remaining_leave' => max(0, round($totalBalance - $usedLeave, 2)),
            'total_applied' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', LeaveApprovalService::STATUS_PENDING)->count(),
            'approved' => (clone $baseQuery)->where('status', LeaveApprovalService::STATUS_APPROVED)->count(),
            'rejected' => (clone $baseQuery)->where('status', LeaveApprovalService::STATUS_REJECTED)->count(),
        ];
    }

    /** Get active leave types for forms and filters. */
    public function getActiveLeaveTypes(): Collection
    {
        return $this->leaveType->active()->get();
    }

    /** Get prepared leave calendar rows. */
    public function getLeaveCalendar(int $month, int $year, ?int $userId = null): SupportCollection
    {
        $this->validateMonthYear($month, $year);
        if ($userId !== null) {
            $this->validateUserExists($userId);
        }

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();
        $rows = collect();

        $leaveQuery = $this->leaveApply
            ->with(['user.userDetail', 'leaveType'])
            ->where('status', LeaveApprovalService::STATUS_APPROVED)
            ->where(function ($query) use ($from, $to): void {
                $query->whereBetween('from_date', [$from->toDateString(), $to->toDateString()])
                    ->orWhereBetween('to_date', [$from->toDateString(), $to->toDateString()])
                    ->orWhere(function ($query) use ($from, $to): void {
                        $query->where('from_date', '<=', $from->toDateString())
                            ->where('to_date', '>=', $to->toDateString());
                    });
            });

        if ($userId !== null) {
            $leaveQuery->where('user_id', $userId);
        }

        foreach ($leaveQuery->orderBy('from_date')->get() as $leave) {
            $start = $leave->from_date->copy()->max($from);
            $end = $leave->to_date->copy()->min($to);
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $rows->push((object) [
                    'date' => $date->copy(),
                    'employee_name' => $leave->user?->name ?? '-',
                    'type' => 'Leave',
                    'description' => $leave->leaveType?->leave_name ?? 'Approved Leave',
                    'status' => $leave->status,
                    'leave' => $leave,
                ]);
            }
        }

        foreach ($this->holidayService->active() as $holiday) {
            $start = $holiday->from_date->copy()->max($from);
            $end = $holiday->to_date->copy()->min($to);
            if ($start->gt($to) || $end->lt($from)) {
                continue;
            }

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $rows->push((object) [
                    'date' => $date->copy(),
                    'employee_name' => '-',
                    'type' => 'Holiday',
                    'description' => $holiday->name,
                    'status' => 'Holiday',
                ]);
            }
        }

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            if ($this->companySettingService->isWeeklyOff($date)) {
                $rows->push((object) [
                    'date' => $date->copy(),
                    'employee_name' => '-',
                    'type' => 'Weekly Off',
                    'description' => $this->companySettingService->getWeeklyOff(),
                    'status' => 'Weekly Off',
                ]);
            }
        }

        return $rows->sortBy(fn ($row): string => $row->date->toDateString() . $row->type)->values();
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
        $calculationSnapshot = $this->leavePolicyService->prepareCalculationSnapshot($userId, (int) $data['leave_type_id'], $from, $to, $data);

        return DB::transaction(function () use ($userId, $data, $from, $to, $calculationSnapshot): LeaveApply {
            $leave = $this->leaveApply->create(array_merge([
                'user_id' => $userId,
                'leave_type_id' => (int) $data['leave_type_id'],
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
                'total_days' => (int) ceil((float) $calculationSnapshot['payable_leave_days']),
                'reason' => $data['reason'] ?? null,
                'status' => LeaveApprovalService::STATUS_PENDING,
            ], $calculationSnapshot));

            return $this->leaveApprovalService->initializeAfterApply($leave, $userId);
        });
    }

    /** Update a pending employee leave request. */
    public function updateLeave(int $leaveId, array $data): LeaveApply
    {
        return DB::transaction(function () use ($leaveId, $data): LeaveApply {
            $leave = $this->findLeave($leaveId);
            $this->ensurePending($leave);

            $userId = (int) ($data['user_id'] ?? $leave->user_id);
            $this->validateActiveUser($userId);
            $this->findLeaveType((int) ($data['leave_type_id'] ?? $leave->leave_type_id));

            $from = $this->parseDate($data['from_date'] ?? $leave->from_date, 'From date');
            $to = $this->parseDate($data['to_date'] ?? $leave->to_date, 'To date');
            $today = Carbon::today();

            if ($from->lessThan($today)) {
                throw new InvalidArgumentException('Leave from date must be today or a future date.');
            }

            if ($to->lessThan($from)) {
                throw new InvalidArgumentException('Leave to date must be greater than or equal to from date.');
            }

            if ($this->hasOverlappingLeaveExcept($userId, $from, $to, $leaveId)) {
                throw new RuntimeException('Leave overlaps with an existing pending or approved leave request.');
            }

            $leaveTypeId = (int) ($data['leave_type_id'] ?? $leave->leave_type_id);
            $calculationSnapshot = $this->leavePolicyService->prepareCalculationSnapshot($userId, $leaveTypeId, $from, $to, $data);
            $this->leavePolicyService->validateLeaveBalance($userId, $leaveTypeId, (float) $calculationSnapshot['payable_leave_days'], $this->leavePolicyService->currentFinancialYear($from));

            $leave->update(array_merge([
                'user_id' => $userId,
                'leave_type_id' => $leaveTypeId,
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
                'total_days' => (int) ceil((float) $calculationSnapshot['payable_leave_days']),
                'reason' => $data['reason'] ?? null,
            ], $calculationSnapshot));

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

        $calculationSnapshot = $this->leavePolicyService->prepareCalculationSnapshot($userId, (int) $data['leave_type_id'], $from, $to, $data);
        $this->leavePolicyService->validateLeaveBalance($userId, (int) $data['leave_type_id'], (float) $calculationSnapshot['payable_leave_days'], $this->leavePolicyService->currentFinancialYear($from));

        return true;
    }

    /** Calculate leave duration in calendar days. */
    public function calculateLeaveDays(Carbon $from, Carbon $to): int
    {
        return (int) $this->leavePolicyService->calculateLeaveDays($from, $to);
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
            ->whereIn('status', [LeaveApprovalService::STATUS_PENDING, LeaveApprovalService::STATUS_MANAGER_APPROVED, LeaveApprovalService::STATUS_HR_APPROVED, LeaveApprovalService::STATUS_APPROVED])
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
        return $this->leaveApprovalService->approve($leaveId, $this->validateApprover($approvedBy));
    }

    /** Reject a pending leave request. */
    public function rejectLeave(int $leaveId, ?int $approvedBy = null): LeaveApply
    {
        return $this->leaveApprovalService->reject($leaveId, $this->validateApprover($approvedBy), 'Rejected via leave service workflow.');
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
        return $this->leaveApply->pending()->with(['user.userDetail', 'leaveType', 'approvedBy'])->latest()->get();
    }

    /** Get approved leaves. */
    public function getApprovedLeaves(): Collection
    {
        return $this->getLeavesByStatus(LeaveApprovalService::STATUS_APPROVED);
    }

    /** Get rejected leaves. */
    public function getRejectedLeaves(): Collection
    {
        return $this->getLeavesByStatus(LeaveApprovalService::STATUS_REJECTED);
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
            'pending' => $leaves->where('status', LeaveApprovalService::STATUS_PENDING)->count(),
            'approved' => $leaves->where('status', LeaveApprovalService::STATUS_APPROVED)->count(),
            'rejected' => $leaves->where('status', LeaveApprovalService::STATUS_REJECTED)->count(),
            'cancelled' => 0,
            'total_leave_days' => (int) $leaves->where('status', LeaveApprovalService::STATUS_APPROVED)->sum('total_days'),
            'balances' => $this->leavePolicyService->getBalanceResponse($userId)->all(),
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
        return $this->findLeave($leaveId)->status === LeaveApprovalService::STATUS_APPROVED;
    }

    /** Determine whether leave is pending. */
    public function isLeavePending(int $leaveId): bool
    {
        return in_array($this->findLeave($leaveId)->status, [LeaveApprovalService::STATUS_PENDING, LeaveApprovalService::STATUS_MANAGER_APPROVED, LeaveApprovalService::STATUS_HR_APPROVED], true);
    }

    /** Determine whether leave is rejected. */
    public function isLeaveRejected(int $leaveId): bool
    {
        return $this->findLeave($leaveId)->status === LeaveApprovalService::STATUS_REJECTED;
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

    /** Detect overlapping pending or approved leave while excluding the current request. */
    protected function hasOverlappingLeaveExcept(int $userId, Carbon $from, Carbon $to, int $leaveId): bool
    {
        $this->validateUserExists($userId);

        return $this->leaveApply
            ->where('id', '!=', $leaveId)
            ->where('user_id', $userId)
            ->whereIn('status', [LeaveApprovalService::STATUS_PENDING, LeaveApprovalService::STATUS_MANAGER_APPROVED, LeaveApprovalService::STATUS_HR_APPROVED, LeaveApprovalService::STATUS_APPROVED])
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
            throw new InvalidArgumentException('Unsupported leave status.');
        }
    }

    /** Validate supported pagination size. */
    protected function validatedPerPage(int $perPage): int
    {
        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
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








