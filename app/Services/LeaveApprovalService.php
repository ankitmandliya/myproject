<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CompanySettingServiceInterface;
use App\Contracts\LeaveApprovalServiceInterface;
use App\Contracts\LeavePolicyServiceInterface;
use App\Contracts\RolePermissionServiceInterface;
use App\Events\Leave\LeaveApplied;
use App\Events\Leave\LeaveApproved;
use App\Events\Leave\LeaveCancelled;
use App\Events\Leave\LeaveHRApproved;
use App\Events\Leave\LeaveManagerApproved;
use App\Events\Leave\LeaveRejected;
use App\Events\Leave\LeaveRevoked;
use App\Models\Attendance;
use App\Models\LeaveApply;
use App\Models\SalarySlip;
use App\Models\User;
use App\Notifications\LeaveWorkflowNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;

class LeaveApprovalService implements LeaveApprovalServiceInterface
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_MANAGER_APPROVED = 'Manager Approved';
    public const STATUS_HR_APPROVED = 'HR Approved';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_REVOKED = 'Revoked';

    public const LEVEL_MANAGER = 'manager';
    public const LEVEL_HR = 'hr';
    public const LEVEL_ADMIN = 'admin';

    /** @var array<int, string> */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_MANAGER_APPROVED,
        self::STATUS_HR_APPROVED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
        self::STATUS_REVOKED,
    ];

    /** @var array<int, string> */
    protected const ACTIONABLE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_MANAGER_APPROVED,
        self::STATUS_HR_APPROVED,
    ];

    public function __construct(
        protected LeaveApply $leaveApply,
        protected User $user,
        protected Attendance $attendance,
        protected SalarySlip $salarySlip,
        protected LeavePolicyServiceInterface $leavePolicyService,
        protected CompanySettingServiceInterface $companySettingService,
        protected RolePermissionServiceInterface $rolePermissionService
    ) {
    }

    public function initializeAfterApply(LeaveApply $leave, ?int $actorId = null): LeaveApply
    {
        return DB::transaction(function () use ($leave, $actorId): LeaveApply {
            $leave = $this->findLeave((int) $leave->id);
            $managerId = $leave->user?->userDetail?->reporting_manager_id;
            $levels = $this->levelsForManager($managerId ?: null);
            $payload = [
                'manager_id' => $managerId ?: null,
                'approval_level' => $this->settingsAutoApprove() ? 'auto' : ($levels[0] ?? self::LEVEL_HR),
            ];

            $this->appendHistory($leave, 'Employee Applied', $actorId ?? (int) $leave->user_id, null);
            $leave->fill($payload)->save();

            if ($this->settingsAutoApprove()) {
                $this->finalApprove($leave, $actorId ?? (int) $leave->user_id, 'Auto approved by company policy.');
                event(new LeaveApproved($leave->fresh(), $actorId, 'Auto approved by company policy.'));
            } else {
                event(new LeaveApplied($leave->fresh(), $actorId));
                $this->notifyNextApprover($leave->fresh(), 'Leave request awaiting approval.');
            }

            return $this->findLeave((int) $leave->id);
        });
    }

    public function getApprovalDashboard(array $filters = [], int $perPage = 25, ?int $actorId = null): LengthAwarePaginator
    {
        $query = $this->leaveApply->with(['user.userDetail', 'leaveType', 'approvedBy', 'manager', 'hrApprover', 'adminApprover']);

        if ($actorId !== null && ! $this->hasAnyRole($actorId, ['Admin', 'HR'])) {
            $query->whereHas('user.userDetail', fn (Builder $query): Builder => $query->where('reporting_manager_id', $actorId));
        }

        if (! empty($filters['status'])) {
            $status = (string) $filters['status'];
            $this->validateStatus($status);
            $query->where('status', $status);
        } else {
            $query->whereIn('status', self::STATUSES);
        }

        if (! empty($filters['employee'])) {
            $employee = strtolower(trim((string) $filters['employee']));
            $query->whereHas('user', function (Builder $query) use ($employee): void {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$employee}%"])
                    ->orWhereHas('userDetail', function (Builder $query) use ($employee): void {
                        $query->whereRaw("LOWER(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?", ["%{$employee}%"])
                            ->orWhereRaw('LOWER(emp_code) LIKE ?', ["%{$employee}%"]);
                    });
            });
        }

        if (! empty($filters['employee_code'])) {
            $employeeCode = strtolower(trim((string) $filters['employee_code']));
            $query->whereHas('user.userDetail', fn (Builder $query): Builder => $query->whereRaw('LOWER(emp_code) LIKE ?', ["%{$employeeCode}%"]));
        }

        if (! empty($filters['department'])) {
            $department = strtolower(trim((string) $filters['department']));
            $query->whereHas('user.userDetail', fn (Builder $query): Builder => $query->whereRaw('LOWER(department) LIKE ?', ["%{$department}%"]));
        }

        if (! empty($filters['designation'])) {
            $designation = strtolower(trim((string) $filters['designation']));
            $query->whereHas('user.userDetail', fn (Builder $query): Builder => $query->whereRaw('LOWER(designation) LIKE ?', ["%{$designation}%"]));
        }

        if (! empty($filters['approval_level'])) {
            $query->where('approval_level', strtolower(trim((string) $filters['approval_level'])));
        }

        if (! empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', (int) $filters['leave_type_id']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('from_date', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('to_date', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }

        if (! empty($filters['financial_year'])) {
            $query->where('leave_calculation_json->financial_year', (string) $filters['financial_year']);
        }

        return $query->latest()->paginate(in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25);
    }

    public function approve(int $leaveId, int $actorId, ?string $remarks = null): LeaveApply
    {
        return DB::transaction(function () use ($leaveId, $actorId, $remarks): LeaveApply {
            $leave = $this->findLeave($leaveId);
            $this->ensurePayrollOpen($leave);
            $this->ensureActiveEmployee($leave);

            if (! in_array($leave->status, self::ACTIONABLE_STATUSES, true)) {
                throw new RuntimeException('This leave request cannot be approved again.');
            }

            $level = $this->currentRequiredLevel($leave);
            $this->authorizeLevel($leave, $actorId, $level);

            if ($this->isFinalLevel($leave, $level)) {
                $this->ensureNoApprovedOverlap($leave);
                $this->finalApprove($leave, $actorId, $remarks);
                event(new LeaveApproved($leave->fresh(), $actorId, $remarks));
            } else {
                $this->approveIntermediate($leave, $actorId, $level, $remarks);
            }

            return $this->findLeave($leaveId);
        });
    }

    public function reject(int $leaveId, int $actorId, string $remarks): LeaveApply
    {
        $remarks = trim($remarks);
        if (mb_strlen($remarks) < 10) {
            throw new InvalidArgumentException('Rejection remarks must be at least 10 characters.');
        }

        return DB::transaction(function () use ($leaveId, $actorId, $remarks): LeaveApply {
            $leave = $this->findLeave($leaveId);
            $this->ensurePayrollOpen($leave);

            if (! in_array($leave->status, self::ACTIONABLE_STATUSES, true)) {
                throw new RuntimeException('This leave request cannot be rejected.');
            }

            $level = $this->currentRequiredLevel($leave);
            $this->authorizeLevel($leave, $actorId, $level);
            $now = Carbon::now();

            $updates = [
                'status' => self::STATUS_REJECTED,
                'approval_level' => 'closed',
                'rejected_by' => $actorId,
                'rejected_at' => $now,
                'approved_by' => null,
                'approved_at' => null,
            ] + $this->levelUpdates($level, $actorId, self::STATUS_REJECTED, $remarks, $now);

            $leave->fill($updates);
            $this->appendHistory($leave, 'Rejected by ' . ucfirst($level), $actorId, $remarks);
            $leave->save();
            $this->notifyEmployee($leave->fresh(), 'Leave request rejected.', $remarks);
            event(new LeaveRejected($leave->fresh(), $actorId, $remarks));

            return $this->findLeave($leaveId);
        });
    }

    public function cancel(int $leaveId, int $actorId, ?string $remarks = null): LeaveApply
    {
        return DB::transaction(function () use ($leaveId, $actorId, $remarks): LeaveApply {
            $leave = $this->findLeave($leaveId);
            $this->ensurePayrollOpen($leave);

            if ((int) $leave->user_id !== $actorId) {
                throw new RuntimeException('Only the employee who applied leave can cancel it.');
            }

            if (! in_array($leave->status, self::ACTIONABLE_STATUSES, true)) {
                throw new RuntimeException('Leave cannot be cancelled after final approval, rejection, cancellation, or revoke.');
            }

            $leave->fill([
                'status' => self::STATUS_CANCELLED,
                'approval_level' => 'closed',
                'cancelled_by' => $actorId,
                'cancelled_at' => Carbon::now(),
            ]);
            $this->appendHistory($leave, 'Cancelled by Employee', $actorId, $remarks);
            $leave->save();
            $this->notifyEmployee($leave->fresh(), 'Leave request cancelled.', $remarks);
            event(new LeaveCancelled($leave->fresh(), $actorId, $remarks));

            return $this->findLeave($leaveId);
        });
    }

    public function revoke(int $leaveId, int $actorId, ?string $remarks = null): LeaveApply
    {
        return DB::transaction(function () use ($leaveId, $actorId, $remarks): LeaveApply {
            $leave = $this->findLeave($leaveId);
            $this->ensureAdmin($actorId);
            $this->ensurePayrollOpen($leave);

            if ($leave->status !== self::STATUS_APPROVED) {
                throw new RuntimeException('Only approved leave can be revoked.');
            }

            $days = $this->payableDays($leave);
            if (! $this->leavePolicyService->isLeaveWithoutPay((int) $leave->leave_type_id)) {
                $this->leavePolicyService->restoreLeave((int) $leave->user_id, (int) $leave->leave_type_id, $days, $this->leavePolicyService->currentFinancialYear($leave->from_date));
            }

            $leave->fill([
                'status' => self::STATUS_REVOKED,
                'approval_level' => 'closed',
                'revoked_by' => $actorId,
                'revoked_at' => Carbon::now(),
            ]);
            $this->appendHistory($leave, 'Revoked by Admin', $actorId, $remarks);
            $leave->save();
            $this->notifyEmployee($leave->fresh(), 'Approved leave revoked.', $remarks);
            event(new LeaveRevoked($leave->fresh(), $actorId, $remarks));

            return $this->findLeave($leaveId);
        });
    }

    public function canAct(LeaveApply $leave, int $actorId, string $action): bool
    {
        try {
            return match ($action) {
                'approve', 'reject' => in_array($leave->status, self::ACTIONABLE_STATUSES, true)
                    && $this->actorCanApproveLevel($leave, $actorId, $this->currentRequiredLevel($leave)),
                'cancel' => (int) $leave->user_id === $actorId && in_array($leave->status, self::ACTIONABLE_STATUSES, true),
                'revoke' => $leave->status === self::STATUS_APPROVED && $this->hasAnyRole($actorId, ['Admin']),
                default => false,
            };
        } catch (RuntimeException|InvalidArgumentException) {
            return false;
        }
    }

    public function configuredLevels(): array
    {
        $settings = $this->companySettingService->getSettings();
        $levels = $settings->leave_approval_levels ?? null;

        if (is_string($levels)) {
            $decoded = json_decode($levels, true);
            $levels = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $levels, -1, PREG_SPLIT_NO_EMPTY);
        }

        if (! is_array($levels) || $levels === []) {
            $levels = [self::LEVEL_HR];
        }

        $allowed = [self::LEVEL_MANAGER, self::LEVEL_HR, self::LEVEL_ADMIN];

        return collect($levels)
            ->map(fn (mixed $level): string => strtolower(trim((string) $level)))
            ->filter(fn (string $level): bool => in_array($level, $allowed, true))
            ->unique()
            ->values()
            ->whenEmpty(fn ($collection) => $collection->push(self::LEVEL_HR))
            ->all();
    }

    protected function approveIntermediate(LeaveApply $leave, int $actorId, string $level, ?string $remarks): void
    {
        $now = Carbon::now();
        $status = $level === self::LEVEL_MANAGER ? self::STATUS_MANAGER_APPROVED : self::STATUS_HR_APPROVED;
        $nextLevel = $this->nextLevelAfter($level, $leave);

        $leave->fill([
            'status' => $status,
            'approval_level' => $nextLevel,
        ] + $this->levelUpdates($level, $actorId, $status, $remarks, $now));

        $this->appendHistory($leave, $status, $actorId, $remarks);
        $leave->save();

        if ($level === self::LEVEL_MANAGER) {
            event(new LeaveManagerApproved($leave->fresh(), $actorId, $remarks));
        } else {
            event(new LeaveHRApproved($leave->fresh(), $actorId, $remarks));
        }

        $this->notifyNextApprover($leave->fresh(), 'Leave request awaiting ' . strtoupper((string) $nextLevel) . ' approval.');
    }

    protected function finalApprove(LeaveApply $leave, int $actorId, ?string $remarks): void
    {
        $level = $this->currentRequiredLevel($leave);
        $now = Carbon::now();
        $days = $this->payableDays($leave);
        $attendanceCount = $this->attendanceCount($leave);

        if (! $this->leavePolicyService->isLeaveWithoutPay((int) $leave->leave_type_id)) {
            $this->leavePolicyService->consumeLeave((int) $leave->user_id, (int) $leave->leave_type_id, $days, $this->leavePolicyService->currentFinancialYear($leave->from_date));
        }

        $leave->fill([
            'status' => self::STATUS_APPROVED,
            'approval_level' => 'closed',
            'approved_by' => $actorId,
            'approved_at' => $now,
            'attendance_warning' => $attendanceCount > 0 ? "{$attendanceCount} attendance record(s) already exist in this leave range." : null,
        ] + $this->levelUpdates($level, $actorId, self::STATUS_APPROVED, $remarks, $now));

        $this->appendHistory($leave, 'Final Approved', $actorId, $remarks);
        $leave->save();
        $this->notifyEmployee($leave->fresh(), 'Leave request approved.', $remarks);
    }

    protected function currentRequiredLevel(LeaveApply $leave): string
    {
        $levels = $this->levelsForLeave($leave);
        $status = (string) $leave->status;

        if ($status === self::STATUS_PENDING) {
            return $levels[0] ?? self::LEVEL_HR;
        }

        if ($status === self::STATUS_MANAGER_APPROVED) {
            return $this->nextLevelAfter(self::LEVEL_MANAGER, $leave) ?? ($levels[0] ?? self::LEVEL_HR);
        }

        if ($status === self::STATUS_HR_APPROVED) {
            return $this->nextLevelAfter(self::LEVEL_HR, $leave) ?? ($levels[0] ?? self::LEVEL_ADMIN);
        }

        return (string) ($leave->approval_level ?: ($levels[0] ?? self::LEVEL_HR));
    }

    protected function levelsForLeave(LeaveApply $leave): array
    {
        return $this->levelsForManager($leave->manager_id ? (int) $leave->manager_id : null);
    }

    protected function levelsForManager(?int $managerId): array
    {
        $levels = collect($this->configuredLevels())
            ->reject(fn (string $level): bool => $level === self::LEVEL_MANAGER && empty($managerId))
            ->values()
            ->all();

        return $levels !== [] ? $levels : [self::LEVEL_HR];
    }

    protected function nextLevelAfter(string $level, ?LeaveApply $leave = null): ?string
    {
        $levels = $leave instanceof LeaveApply ? $this->levelsForLeave($leave) : $this->configuredLevels();
        $index = array_search($level, $levels, true);

        if ($index === false) {
            return $levels[0] ?? null;
        }

        return $levels[$index + 1] ?? null;
    }

    protected function isFinalLevel(LeaveApply $leave, string $level): bool
    {
        return $this->nextLevelAfter($level, $leave) === null;
    }

    protected function authorizeLevel(LeaveApply $leave, int $actorId, string $level): void
    {
        if (! $this->actorCanApproveLevel($leave, $actorId, $level)) {
            throw new RuntimeException('You are not authorized to approve this leave stage.');
        }
    }

    protected function actorCanApproveLevel(LeaveApply $leave, int $actorId, string $level): bool
    {
        return match ($level) {
            self::LEVEL_MANAGER => (int) $leave->manager_id === $actorId,
            self::LEVEL_HR => $this->hasAnyRole($actorId, ['HR', 'Admin']),
            self::LEVEL_ADMIN => $this->hasAnyRole($actorId, ['Admin']),
            default => false,
        };
    }

    protected function levelUpdates(string $level, int $actorId, string $status, ?string $remarks, Carbon $now): array
    {
        return match ($level) {
            self::LEVEL_MANAGER => ['manager_id' => $actorId, 'manager_status' => $status, 'manager_remarks' => $remarks, 'manager_action_at' => $now],
            self::LEVEL_HR => ['hr_id' => $actorId, 'hr_status' => $status, 'hr_remarks' => $remarks, 'hr_action_at' => $now],
            self::LEVEL_ADMIN => ['admin_id' => $actorId, 'admin_status' => $status, 'admin_remarks' => $remarks, 'admin_action_at' => $now],
            default => [],
        };
    }

    protected function appendHistory(LeaveApply $leave, string $action, ?int $actorId, ?string $remarks): void
    {
        $entry = [
            'action' => $action,
            'status' => $leave->status,
            'user_id' => $actorId,
            'user_name' => $actorId ? $this->user->find($actorId)?->name : null,
            'remarks' => $remarks,
            'ip' => request()?->ip(),
            'at' => Carbon::now()->toIso8601String(),
        ];

        $timeline = is_array($leave->approval_timeline) ? $leave->approval_timeline : [];
        $timeline[] = $entry;
        $leave->approval_timeline = $timeline;

        $audit = is_array($leave->approval_audit_log) ? $leave->approval_audit_log : [];
        $audit[] = $entry;
        $leave->approval_audit_log = $audit;
    }

    protected function notifyEmployee(LeaveApply $leave, string $action, ?string $remarks = null): void
    {
        if (Schema::hasTable('notifications')) {
            $leave->user?->notify(new LeaveWorkflowNotification($leave, $action, $remarks));
        }
    }

    protected function notifyNextApprover(LeaveApply $leave, string $action): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        $level = $this->currentRequiredLevel($leave);
        if ($level === self::LEVEL_MANAGER && $leave->manager_id) {
            $this->user->find($leave->manager_id)?->notify(new LeaveWorkflowNotification($leave, $action));
            return;
        }

        if ($level === self::LEVEL_HR) {
            $this->usersWithRoles(['HR'])->each(fn (User $user) => $user->notify(new LeaveWorkflowNotification($leave, $action)));
            return;
        }

        if ($level === self::LEVEL_ADMIN) {
            $this->usersWithRoles(['Admin'])->each(fn (User $user) => $user->notify(new LeaveWorkflowNotification($leave, $action)));
        }
    }

    protected function usersWithRoles(array $roles): \Illuminate\Support\Collection
    {
        return $this->user->newQuery()
            ->whereHas('roles', fn ($query) => $query->whereIn('role_name', $roles))
            ->get();
    }

    protected function ensurePayrollOpen(LeaveApply $leave): void
    {
        if ($leave->payroll_locked_at !== null || $this->payrollExists($leave)) {
            throw new RuntimeException('Payroll has already been processed for this leave period.');
        }
    }

    protected function payrollExists(LeaveApply $leave): bool
    {
        $start = $leave->from_date->copy()->startOfMonth();
        $end = $leave->to_date->copy()->startOfMonth();

        for ($date = $start->copy(); $date->lte($end); $date->addMonth()) {
            if ($this->salarySlip->where('user_id', $leave->user_id)->where('month', $date->month)->where('year', $date->year)->exists()) {
                return true;
            }
        }

        return false;
    }

    protected function ensureActiveEmployee(LeaveApply $leave): void
    {
        if (! $leave->user?->userDetail || ! (bool) $leave->user->userDetail->status) {
            throw new RuntimeException('Inactive, resigned, or terminated employees cannot be approved for leave.');
        }
    }

    protected function ensureNoApprovedOverlap(LeaveApply $leave): void
    {
        $exists = $this->leaveApply
            ->where('id', '!=', $leave->id)
            ->where('user_id', $leave->user_id)
            ->where('status', self::STATUS_APPROVED)
            ->where(function (Builder $query) use ($leave): void {
                $query->whereBetween('from_date', [$leave->from_date->toDateString(), $leave->to_date->toDateString()])
                    ->orWhereBetween('to_date', [$leave->from_date->toDateString(), $leave->to_date->toDateString()])
                    ->orWhere(function (Builder $query) use ($leave): void {
                        $query->where('from_date', '<=', $leave->from_date->toDateString())
                            ->where('to_date', '>=', $leave->to_date->toDateString());
                    });
            })
            ->exists();

        if ($exists) {
            throw new RuntimeException('Leave overlaps with an already approved leave request.');
        }
    }

    protected function attendanceCount(LeaveApply $leave): int
    {
        return $this->attendance
            ->where('user_id', $leave->user_id)
            ->whereBetween('attendance_date', [$leave->from_date->toDateString(), $leave->to_date->toDateString()])
            ->where(function (Builder $query): void {
                $query->whereNotNull('check_in')->orWhere('status', 'Present');
            })
            ->count();
    }

    protected function ensureAdmin(int $actorId): void
    {
        if (! $this->hasAnyRole($actorId, ['Admin'])) {
            throw new RuntimeException('Only Admin users can revoke approved leave.');
        }
    }

    protected function hasAnyRole(int $actorId, array $roles): bool
    {
        try {
            return $this->rolePermissionService->userHasAnyRole($actorId, $roles);
        } catch (RuntimeException) {
            return false;
        }
    }

    protected function findLeave(int $leaveId): LeaveApply
    {
        $leave = $this->leaveApply
            ->with(['user.userDetail', 'leaveType', 'approvedBy', 'manager', 'hrApprover', 'adminApprover', 'rejectedBy', 'cancelledBy', 'revokedBy'])
            ->find($leaveId);

        if (! $leave instanceof LeaveApply) {
            throw new RuntimeException("Leave request [{$leaveId}] was not found.");
        }

        return $leave;
    }

    protected function payableDays(LeaveApply $leave): float
    {
        return (float) ($leave->payable_leave_days ?: $leave->total_days ?: 0.0);
    }

    protected function settingsAutoApprove(): bool
    {
        return (bool) ($this->companySettingService->getSettings()->leave_auto_approval ?? false);
    }

    protected function validateStatus(string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Unsupported leave status.');
        }
    }
}




