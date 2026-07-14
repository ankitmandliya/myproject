<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\LeavePolicyServiceInterface;
use App\Contracts\LeaveReportServiceInterface;
use App\Models\Attendance;
use App\Models\EmployeeLeaveBalance;
use App\Models\Holiday;
use App\Models\LeaveApply;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class LeaveReportService implements LeaveReportServiceInterface
{
    protected array $titles = [
        'employee' => 'Employee Leave Report', 'department' => 'Department Report', 'leave-type' => 'Leave Type Report',
        'balance' => 'Leave Balance Report', 'liability' => 'Leave Liability Report', 'monthly' => 'Monthly Report',
        'financial-year' => 'Financial Year Report', 'approval' => 'Approval Performance Report', 'lwp' => 'LWP Report',
        'sandwich' => 'Sandwich Leave Report',
    ];

    public function __construct(
        protected LeaveApply $leaveApply,
        protected User $user,
        protected LeaveType $leaveType,
        protected EmployeeLeaveBalance $balance,
        protected Attendance $attendance,
        protected Holiday $holiday,
        protected LeavePolicyServiceInterface $leavePolicyService,
        protected AttendanceServiceInterface $attendanceService
    ) {}

    public function dashboard(array $filters, ?User $actor = null): array
    {
        $filters = $this->filters($filters);
        $leaves = $this->leaves($filters, $actor)->get();
        $balances = $this->balances($filters, $actor)->get();
        return [
            'title' => 'Leave Reports Dashboard', 'filters' => $filters,
            'summary' => $this->summary($leaves, $balances, $this->employees($filters, $actor)->count(), $filters['financial_year']),
            'charts' => $this->charts($leaves), 'widgets' => $this->widgets($filters, $actor), 'exports' => true,
        ];
    }

    public function report(string $type, array $filters, int $perPage, ?User $actor = null): array
    {
        $filters = $this->filters($filters); $perPage = $this->perPage($perPage);
        $rows = match ($type) {
            'employee' => $this->employeeRows($filters, $perPage, $actor),
            'department' => $this->arrayPaginator($this->departmentRows($filters, $actor), $perPage),
            'leave-type' => $this->arrayPaginator($this->leaveTypeRows($filters, $actor), $perPage),
            'balance' => $this->balanceRows($filters, $perPage, $actor),
            'liability' => $this->liabilityRows($filters, $perPage, $actor),
            'monthly' => $this->arrayPaginator($this->monthlyRows($filters, $actor), $perPage),
            'financial-year' => $this->arrayPaginator($this->financialYearRows($filters, $actor), $perPage),
            'approval' => $this->arrayPaginator($this->approvalRows($filters, $actor), $perPage),
            'lwp' => $this->lwpRows($filters, $perPage, $actor),
            'sandwich' => $this->sandwichRows($filters, $perPage, $actor),
            default => $this->arrayPaginator([], $perPage),
        };
        $leaves = $this->leaves($filters, $actor)->get(); $balances = $this->balances($filters, $actor)->get();
        return ['type' => $type, 'title' => $this->titles[$type] ?? 'Leave Report', 'filters' => $filters,
            'summary' => $this->summary($leaves, $balances, $this->employees($filters, $actor)->count(), $filters['financial_year']),
            'charts' => $this->charts($leaves), 'columns' => $this->columns($type), 'rows' => $rows, 'exports' => true];
    }

    public function employeeDetails(int $employeeId, array $filters, ?User $actor = null): array
    {
        $filters = $this->filters($filters);
        $employee = $this->employees($filters, $actor)->with(['userDetail', 'leaveBalances.leaveType'])->findOrFail($employeeId);
        $leaves = $this->leaves($filters, $actor)->with(['leaveType', 'approvedBy', 'rejectedBy', 'cancelledBy', 'revokedBy'])->where('user_id', $employeeId)->latest('from_date')->get();
        $balances = $this->balances($filters, $actor)->with('leaveType')->where('employee_id', $employeeId)->get();
        $attendance = $this->attendance->newQuery()->where('user_id', $employeeId)->latest('attendance_date')->limit(30)->get();
        $holidays = $this->holiday->newQueryWithoutScopes()->whereDate('to_date', '>=', $this->fyStart($filters))->whereDate('from_date', '<=', $this->fyEnd($filters))->get();
        return ['title' => 'Employee Leave Details', 'employee' => $this->employeePayload($employee),
            'balances' => $balances->map(fn ($b) => $this->balancePayload($b))->values(),
            'leave_history' => $leaves->map(fn ($l) => $this->leavePayload($l))->values(),
            'approval_history' => $this->approvalHistory($leaves),
            'attendance_summary' => ['present' => $attendance->where('status', 'Present')->count(), 'absent' => $attendance->where('status', 'Absent')->count(), 'late' => $attendance->where('late_minutes', '>', 0)->count(), 'half_day' => $attendance->where('half_day', true)->count()],
            'holiday_summary' => ['total' => $holidays->count()], 'sandwich_count' => round((float) $leaves->sum('sandwich_days'), 2),
            'lwp_count' => round((float) $leaves->filter(fn ($l) => $this->isLwp($l))->sum(fn ($l) => (float) ($l->payable_leave_days ?? $l->total_days)), 2), 'filters' => $filters];
    }

    protected function employeeRows(array $filters, int $perPage, ?User $actor): LengthAwarePaginator
    {
        $p = $this->employees($filters, $actor)->with(['userDetail', 'leaveBalances.leaveType', 'leaveApplications.leaveType'])->paginate($perPage);
        $p->setCollection($p->getCollection()->map(function (User $e) use ($filters): array {
            $leaves = $e->leaveApplications->filter(fn ($l) => $this->leaveInRange($l, $filters));
            $balances = $e->leaveBalances->where('financial_year', $filters['financial_year']);
            return ['photo' => $this->photoUrl($e), 'employee_code' => $e->userDetail?->emp_code ?? '-', 'employee_name' => $this->employeeName($e),
                'department' => $e->userDetail?->department ?? '-', 'designation' => $e->userDetail?->designation ?? '-',
                'allocated' => round((float) $balances->sum('allocated'), 2), 'consumed' => round((float) $balances->sum('used'), 2),
                'remaining' => round((float) $balances->sum('remaining'), 2), 'pending' => $leaves->where('status', LeaveApprovalService::STATUS_PENDING)->count(),
                'lwp' => round((float) $leaves->filter(fn ($l) => $this->isLwp($l))->sum('payable_leave_days'), 2), 'sandwich' => round((float) $leaves->sum('sandwich_days'), 2),
                'details_url' => route('hrms.leave-reports.employee.show', $e->id)];
        })); return $p;
    }

    protected function departmentRows(array $filters, ?User $actor): array
    {
        return $this->employees($filters, $actor)->with(['userDetail', 'leaveApplications.leaveType'])->get()->groupBy(fn ($u) => $u->userDetail?->department ?: 'Unassigned')->map(function ($employees, $department) use ($filters): array {
            $leaves = $employees->flatMap->leaveApplications->filter(fn ($l) => $this->leaveInRange($l, $filters));
            return ['department' => $department, 'employees' => $employees->count(), 'applied' => $leaves->count(), 'approved' => $leaves->where('status', LeaveApprovalService::STATUS_APPROVED)->count(),
                'rejected' => $leaves->where('status', LeaveApprovalService::STATUS_REJECTED)->count(), 'pending' => $leaves->whereIn('status', [LeaveApprovalService::STATUS_PENDING, LeaveApprovalService::STATUS_MANAGER_APPROVED, LeaveApprovalService::STATUS_HR_APPROVED])->count(),
                'lwp' => round((float) $leaves->filter(fn ($l) => $this->isLwp($l))->sum('payable_leave_days'), 2), 'sandwich' => round((float) $leaves->sum('sandwich_days'), 2),
                'average_leave' => $employees->count() ? round((float) $leaves->sum('payable_leave_days') / $employees->count(), 2) : 0];
        })->values()->all();
    }

    protected function leaveTypeRows(array $filters, ?User $actor): array
    {
        $leaves = $this->leaves($filters, $actor)->with('leaveType')->get(); $balances = $this->balances($filters, $actor)->with('leaveType')->get();
        return $this->leaveType->newQuery()->get()->map(function ($type) use ($leaves, $balances): array {
            $typeLeaves = $leaves->where('leave_type_id', $type->id); $typeBalances = $balances->where('leave_type_id', $type->id);
            return ['leave_type' => $type->leave_name, 'allocated' => round((float) $typeBalances->sum('allocated'), 2), 'consumed' => round((float) $typeBalances->sum('used'), 2),
                'remaining' => round((float) $typeBalances->sum('remaining'), 2), 'pending' => $typeLeaves->where('status', LeaveApprovalService::STATUS_PENDING)->count(),
                'rejected' => $typeLeaves->where('status', LeaveApprovalService::STATUS_REJECTED)->count(), 'average_usage' => $typeBalances->count() ? round((float) $typeBalances->sum('used') / $typeBalances->count(), 2) : 0];
        })->values()->all();
    }

    protected function balanceRows(array $filters, int $perPage, ?User $actor): LengthAwarePaginator
    {
        $p = $this->employees($filters, $actor)->with(['userDetail', 'leaveBalances.leaveType'])->paginate($perPage);
        $p->setCollection($p->getCollection()->map(function ($e) use ($filters): array {
            $balances = $e->leaveBalances->where('financial_year', $filters['financial_year']);
            return ['employee' => $this->employeeName($e), 'cl' => $this->balanceByName($balances, ['CL', 'CASUAL']), 'sl' => $this->balanceByName($balances, ['SL', 'SICK']), 'el' => $this->balanceByName($balances, ['EL', 'EARN']),
                'lwp' => $this->balanceByName($balances, ['LWP', 'WITHOUT PAY']), 'consumed' => round((float) $balances->sum('used'), 2), 'remaining' => round((float) $balances->sum('remaining'), 2),
                'carry_forward' => round((float) $balances->sum('carry_forward'), 2), 'low_balance' => $balances->contains(fn ($b) => (float) $b->remaining <= 2)];
        })); return $p;
    }

    protected function liabilityRows(array $filters, int $perPage, ?User $actor): LengthAwarePaginator
    {
        $p = $this->balances($filters, $actor)->with(['employee.userDetail', 'leaveType'])->paginate($perPage);
        $p->setCollection($p->getCollection()->filter(fn ($b) => $this->nameContains($b->leaveType?->leave_name, ['EL', 'EARN']))->map(fn ($b) => ['employee' => $this->employeeName($b->employee), 'earned_leave_remaining' => round((float) $b->remaining, 2), 'estimated_liability' => 'Payroll integration pending', 'carry_forward' => round((float) $b->carry_forward, 2)])->values());
        return $p;
    }

    protected function monthlyRows(array $filters, ?User $actor): array
    {
        $leaves = $this->leaves($filters, $actor)->get();
        return collect(range(1, 12))->map(function ($month) use ($leaves): array { $items = $leaves->filter(fn ($l) => (int) $l->from_date?->month === $month);
            return ['month' => Carbon::create(null, $month, 1)->format('F'), 'requests' => $items->count(), 'approved' => $items->where('status', LeaveApprovalService::STATUS_APPROVED)->count(),
                'rejected' => $items->where('status', LeaveApprovalService::STATUS_REJECTED)->count(), 'cancelled' => $items->where('status', LeaveApprovalService::STATUS_CANCELLED)->count(),
                'lwp' => round((float) $items->filter(fn ($l) => $this->isLwp($l))->sum('payable_leave_days'), 2), 'leave_days' => round((float) $items->sum('payable_leave_days'), 2)]; })->all();
    }

    protected function financialYearRows(array $filters, ?User $actor): array
    {
        return $this->balances($filters, $actor)->with('employee')->get()->groupBy('financial_year')->map(fn ($balances, $year) => ['financial_year' => $year, 'allocated' => round((float) $balances->sum('allocated'), 2),
            'consumed' => round((float) $balances->sum('used'), 2), 'remaining' => round((float) $balances->sum('remaining'), 2), 'carry_forward' => round((float) $balances->sum('carry_forward'), 2),
            'lwp' => round((float) $this->leaves(['financial_year' => $year], $actor)->get()->filter(fn ($l) => $this->isLwp($l))->sum('payable_leave_days'), 2), 'employees_covered' => $balances->pluck('employee_id')->unique()->count()])->values()->all();
    }

    protected function approvalRows(array $filters, ?User $actor): array
    {
        $leaves = $this->leaves($filters, $actor)->with(['approvedBy', 'rejectedBy', 'manager', 'hrApprover', 'adminApprover'])->get();
        $approvers = collect();

        foreach ($leaves as $leave) {
            foreach ([
                [$leave->manager, 'Manager'],
                [$leave->hrApprover, 'HR'],
                [$leave->adminApprover, 'Admin'],
                [$leave->approvedBy, 'Final Approver'],
                [$leave->rejectedBy, 'Rejector'],
            ] as [$user, $role]) {
                if ($user instanceof User) {
                    $approvers->push(['id' => $user->id, 'name' => $user->name, 'role' => $role]);
                }
            }
        }

        return $approvers->unique('id')->map(function (array $approver) use ($leaves): array {
            $handled = $leaves->filter(fn ($leave): bool => in_array((int) $approver['id'], array_filter([
                $leave->manager_id, $leave->hr_id, $leave->admin_id, $leave->approved_by, $leave->rejected_by,
            ]), true));

            return [
                'approver' => $approver['name'],
                'role' => $approver['role'],
                'assigned' => $handled->count(),
                'approved' => $handled->where('status', LeaveApprovalService::STATUS_APPROVED)->count(),
                'rejected' => $handled->where('status', LeaveApprovalService::STATUS_REJECTED)->count(),
                'pending' => $handled->whereIn('status', [LeaveApprovalService::STATUS_PENDING, LeaveApprovalService::STATUS_MANAGER_APPROVED, LeaveApprovalService::STATUS_HR_APPROVED])->count(),
                'average_time' => $this->averageApprovalTime($handled),
            ];
        })->values()->all();
    }

    protected function lwpRows(array $filters, int $perPage, ?User $actor): LengthAwarePaginator
    {
        $p = $this->leaves($filters, $actor)->with(['user.userDetail', 'leaveType'])->latest('from_date')->paginate($perPage);
        $p->setCollection($p->getCollection()->filter(fn ($leave) => $this->isLwp($leave))->map(fn ($leave): array => [
            'employee_code' => $leave->user?->userDetail?->emp_code ?? '-',
            'employee' => $this->employeeName($leave->user),
            'department' => $leave->user?->userDetail?->department ?? '-',
            'leave_type' => $leave->leaveType?->leave_name ?? '-',
            'from_date' => $leave->from_date?->format('d M Y') ?? '-',
            'to_date' => $leave->to_date?->format('d M Y') ?? '-',
            'lwp_days' => round((float) ($leave->payable_leave_days ?? $leave->total_days), 2),
            'status' => $leave->status,
        ])->values());

        return $p;
    }

    protected function sandwichRows(array $filters, int $perPage, ?User $actor): LengthAwarePaginator
    {
        $p = $this->leaves($filters, $actor)->with(['user.userDetail', 'leaveType'])->where('sandwich_days', '>', 0)->latest('from_date')->paginate($perPage);
        $p->setCollection($p->getCollection()->map(fn ($leave): array => [
            'employee_code' => $leave->user?->userDetail?->emp_code ?? '-',
            'employee' => $this->employeeName($leave->user),
            'department' => $leave->user?->userDetail?->department ?? '-',
            'leave_type' => $leave->leaveType?->leave_name ?? '-',
            'from_date' => $leave->from_date?->format('d M Y') ?? '-',
            'to_date' => $leave->to_date?->format('d M Y') ?? '-',
            'requested_days' => round((float) ($leave->requested_days ?? $leave->total_days), 2),
            'sandwich_days' => round((float) $leave->sandwich_days, 2),
            'payable_days' => round((float) $leave->payable_leave_days, 2),
            'status' => $leave->status,
        ])->values());

        return $p;
    }

    protected function summary(Collection $leaves, Collection $balances, int $employeeCount, string $financialYear): array
    {
        return [
            ['label' => 'Total Employees', 'value' => $employeeCount],
            ['label' => 'Total Leave Requests', 'value' => $leaves->count()],
            ['label' => 'Approved', 'value' => $leaves->where('status', LeaveApprovalService::STATUS_APPROVED)->count()],
            ['label' => 'Pending', 'value' => $leaves->whereIn('status', [LeaveApprovalService::STATUS_PENDING, LeaveApprovalService::STATUS_MANAGER_APPROVED, LeaveApprovalService::STATUS_HR_APPROVED])->count()],
            ['label' => 'Rejected', 'value' => $leaves->where('status', LeaveApprovalService::STATUS_REJECTED)->count()],
            ['label' => 'Cancelled', 'value' => $leaves->where('status', LeaveApprovalService::STATUS_CANCELLED)->count()],
            ['label' => 'Revoked', 'value' => $leaves->where('status', LeaveApprovalService::STATUS_REVOKED)->count()],
            ['label' => 'Total Leave Days', 'value' => round((float) $leaves->sum('payable_leave_days'), 2)],
            ['label' => 'LWP Days', 'value' => round((float) $leaves->filter(fn ($leave) => $this->isLwp($leave))->sum('payable_leave_days'), 2)],
            ['label' => 'Remaining Leave Balance', 'value' => round((float) $balances->sum('remaining'), 2)],
            ['label' => 'Current Financial Year', 'value' => $financialYear],
        ];
    }

    protected function charts(Collection $leaves): array
    {
        $months = collect(range(1, 12))->map(fn ($month): string => Carbon::create(null, $month, 1)->format('M'));

        return [
            'monthly_leave_trend' => [
                'labels' => $months->all(),
                'data' => collect(range(1, 12))->map(fn ($month): float => round((float) $leaves->filter(fn ($leave) => (int) $leave->from_date?->month === $month)->sum('payable_leave_days'), 2))->all(),
            ],
            'leave_type_distribution' => [
                'labels' => $leaves->groupBy(fn ($leave) => $leave->leaveType?->leave_name ?: 'Unassigned')->keys()->values()->all(),
                'data' => $leaves->groupBy(fn ($leave) => $leave->leaveType?->leave_name ?: 'Unassigned')->map->count()->values()->all(),
            ],
            'department_wise_leave' => [
                'labels' => $leaves->groupBy(fn ($leave) => $leave->user?->userDetail?->department ?: 'Unassigned')->keys()->values()->all(),
                'data' => $leaves->groupBy(fn ($leave) => $leave->user?->userDetail?->department ?: 'Unassigned')->map(fn ($items) => round((float) $items->sum('payable_leave_days'), 2))->values()->all(),
            ],
            'monthly_approval_trend' => [
                'labels' => $months->all(),
                'approved' => collect(range(1, 12))->map(fn ($month): int => $leaves->filter(fn ($leave) => (int) $leave->from_date?->month === $month && $leave->status === LeaveApprovalService::STATUS_APPROVED)->count())->all(),
                'rejected' => collect(range(1, 12))->map(fn ($month): int => $leaves->filter(fn ($leave) => (int) $leave->from_date?->month === $month && $leave->status === LeaveApprovalService::STATUS_REJECTED)->count())->all(),
            ],
        ];
    }

    protected function widgets(array $filters, ?User $actor): array
    {
        $leaves = $this->leaves($filters, $actor)->with(['user.userDetail', 'leaveType'])->get();
        $balances = $this->balances($filters, $actor)->with(['employee.userDetail', 'leaveType'])->get();

        return [
            'most_leave_taken' => $leaves->groupBy('user_id')->map(function ($items): array {
                $employee = $items->first()?->user;
                return ['employee' => $this->employeeName($employee), 'days' => round((float) $items->sum('payable_leave_days'), 2)];
            })->sortByDesc('days')->take(5)->values()->all(),
            'lowest_leave_balance' => $balances->groupBy('employee_id')->map(function ($items): array {
                $employee = $items->first()?->employee;
                return ['employee' => $this->employeeName($employee), 'balance' => round((float) $items->sum('remaining'), 2)];
            })->sortBy('balance')->take(5)->values()->all(),
            'pending_approvals' => $leaves->whereIn('status', [LeaveApprovalService::STATUS_PENDING, LeaveApprovalService::STATUS_MANAGER_APPROVED, LeaveApprovalService::STATUS_HR_APPROVED])->take(5)->map(fn ($leave): array => $this->leavePayload($leave))->values()->all(),
            'upcoming_leave' => $leaves->filter(fn ($leave): bool => $leave->from_date && $leave->from_date->isFuture())->sortBy('from_date')->take(5)->map(fn ($leave): array => $this->leavePayload($leave))->values()->all(),
        ];
    }

    protected function leaves(array $filters, ?User $actor): Builder
    {
        $filters = $this->filters($filters);
        $query = $this->leaveApply->newQuery()->with(['user.userDetail', 'leaveType']);

        if ($actor && ! $this->hasAnyRole($actor, ['Admin', 'HR'])) {
            $query->whereHas('user.userDetail', fn (Builder $q): Builder => $q->where('reporting_manager_id', $actor->id));
        }

        $query->where(function (Builder $q) use ($filters): void {
            $q->whereBetween('from_date', [$this->fyStart($filters)->toDateString(), $this->fyEnd($filters)->toDateString()])
                ->orWhereBetween('to_date', [$this->fyStart($filters)->toDateString(), $this->fyEnd($filters)->toDateString()])
                ->orWhere('leave_calculation_json->financial_year', $filters['financial_year']);
        });

        if ($filters['month']) {
            $query->whereMonth('from_date', (int) $filters['month']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['approval_stage']) {
            $query->where('approval_level', $filters['approval_stage']);
        }

        if ($filters['leave_type_id']) {
            $query->where('leave_type_id', (int) $filters['leave_type_id']);
        }

        if ($filters['from_date']) {
            $query->whereDate('from_date', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }

        if ($filters['to_date']) {
            $query->whereDate('to_date', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }

        if ($filters['employee']) {
            $term = strtolower($filters['employee']);
            $query->whereHas('user', function (Builder $q) use ($term): void {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                    ->orWhereHas('userDetail', fn (Builder $detail): Builder => $detail->whereRaw("LOWER(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?", ["%{$term}%"]));
            });
        }

        if ($filters['employee_code']) {
            $term = strtolower($filters['employee_code']);
            $query->whereHas('user.userDetail', fn (Builder $q): Builder => $q->whereRaw('LOWER(emp_code) LIKE ?', ["%{$term}%"]));
        }

        if ($filters['department']) {
            $term = strtolower($filters['department']);
            $query->whereHas('user.userDetail', fn (Builder $q): Builder => $q->whereRaw('LOWER(department) LIKE ?', ["%{$term}%"]));
        }

        if ($filters['designation']) {
            $term = strtolower($filters['designation']);
            $query->whereHas('user.userDetail', fn (Builder $q): Builder => $q->whereRaw('LOWER(designation) LIKE ?', ["%{$term}%"]));
        }

        if ($filters['approver_id']) {
            $approverId = (int) $filters['approver_id'];
            $query->where(function (Builder $q) use ($approverId): void {
                $q->where('manager_id', $approverId)->orWhere('hr_id', $approverId)->orWhere('admin_id', $approverId)->orWhere('approved_by', $approverId)->orWhere('rejected_by', $approverId);
            });
        }

        return $query;
    }

    protected function balances(array $filters, ?User $actor): Builder
    {
        $filters = $this->filters($filters);
        $query = $this->balance->newQuery()->with(['employee.userDetail', 'leaveType'])->where('financial_year', $filters['financial_year']);

        if ($actor && ! $this->hasAnyRole($actor, ['Admin', 'HR'])) {
            $query->whereHas('employee.userDetail', fn (Builder $q): Builder => $q->where('reporting_manager_id', $actor->id));
        }

        if ($filters['leave_type_id']) {
            $query->where('leave_type_id', (int) $filters['leave_type_id']);
        }

        $this->applyEmployeeFilters($query, $filters, 'employee');

        return $query;
    }

    protected function employees(array $filters, ?User $actor): Builder
    {
        $filters = $this->filters($filters);
        $query = $this->user->newQuery()->with('userDetail')->whereHas('userDetail');

        if ($actor && ! $this->hasAnyRole($actor, ['Admin', 'HR'])) {
            $query->whereHas('userDetail', fn (Builder $q): Builder => $q->where('reporting_manager_id', $actor->id));
        }

        $this->applyEmployeeFilters($query, $filters, null);

        return $query;
    }

    protected function applyEmployeeFilters(Builder $query, array $filters, ?string $relation): void
    {
        $userPath = $relation;
        $detailPath = $relation ? $relation . '.userDetail' : 'userDetail';

        if ($filters['employee']) {
            $term = strtolower($filters['employee']);
            if ($userPath) {
                $query->whereHas($userPath, function (Builder $q) use ($term): void {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                        ->orWhereHas('userDetail', fn (Builder $detail): Builder => $detail->whereRaw("LOWER(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?", ["%{$term}%"]));
                });
            } else {
                $query->where(function (Builder $q) use ($term): void {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                        ->orWhereHas('userDetail', fn (Builder $detail): Builder => $detail->whereRaw("LOWER(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?", ["%{$term}%"]));
                });
            }
        }

        foreach (['employee_code' => 'emp_code', 'department' => 'department', 'designation' => 'designation'] as $filter => $column) {
            if ($filters[$filter]) {
                $term = strtolower($filters[$filter]);
                $query->whereHas($detailPath, fn (Builder $q): Builder => $q->whereRaw("LOWER({$column}) LIKE ?", ["%{$term}%"]));
            }
        }
    }

    protected function columns(string $type): array
    {
        return match ($type) {
            'employee' => ['photo' => 'Photo', 'employee_code' => 'Employee Code', 'employee_name' => 'Employee', 'department' => 'Department', 'designation' => 'Designation', 'allocated' => 'Allocated', 'consumed' => 'Consumed', 'remaining' => 'Remaining', 'pending' => 'Pending', 'lwp' => 'LWP', 'sandwich' => 'Sandwich'],
            'department' => ['department' => 'Department', 'employees' => 'Employees', 'applied' => 'Applied', 'approved' => 'Approved', 'rejected' => 'Rejected', 'pending' => 'Pending', 'lwp' => 'LWP Days', 'sandwich' => 'Sandwich Days', 'average_leave' => 'Average Leave'],
            'leave-type' => ['leave_type' => 'Leave Type', 'allocated' => 'Allocated', 'consumed' => 'Consumed', 'remaining' => 'Remaining', 'pending' => 'Pending', 'rejected' => 'Rejected', 'average_usage' => 'Average Usage'],
            'balance' => ['employee' => 'Employee', 'cl' => 'CL', 'sl' => 'SL', 'el' => 'EL', 'lwp' => 'LWP', 'consumed' => 'Consumed', 'remaining' => 'Remaining', 'carry_forward' => 'Carry Forward'],
            'liability' => ['employee' => 'Employee', 'earned_leave_remaining' => 'Earned Leave Remaining', 'estimated_liability' => 'Estimated Liability', 'carry_forward' => 'Carry Forward'],
            'monthly' => ['month' => 'Month', 'requests' => 'Requests', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled', 'lwp' => 'LWP Days', 'leave_days' => 'Leave Days'],
            'financial-year' => ['financial_year' => 'Financial Year', 'allocated' => 'Allocated', 'consumed' => 'Consumed', 'remaining' => 'Remaining', 'carry_forward' => 'Carry Forward', 'lwp' => 'LWP Days', 'employees_covered' => 'Employees Covered'],
            'approval' => ['approver' => 'Approver', 'role' => 'Role', 'assigned' => 'Assigned', 'approved' => 'Approved', 'rejected' => 'Rejected', 'pending' => 'Pending', 'average_time' => 'Average Time'],
            'lwp' => ['employee_code' => 'Employee Code', 'employee' => 'Employee', 'department' => 'Department', 'leave_type' => 'Leave Type', 'from_date' => 'From Date', 'to_date' => 'To Date', 'lwp_days' => 'LWP Days', 'status' => 'Status'],
            'sandwich' => ['employee_code' => 'Employee Code', 'employee' => 'Employee', 'department' => 'Department', 'leave_type' => 'Leave Type', 'from_date' => 'From Date', 'to_date' => 'To Date', 'requested_days' => 'Requested Days', 'sandwich_days' => 'Sandwich Days', 'payable_days' => 'Payable Days', 'status' => 'Status'],
            default => [],
        };
    }

    protected function arrayPaginator(array $items, int $perPage): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage('page');
        $collection = collect($items);

        return new Paginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()?->query() ?? []]
        );
    }

    protected function leavePayload(LeaveApply $leave): array
    {
        return [
            'employee' => $this->employeeName($leave->user),
            'employee_code' => $leave->user?->userDetail?->emp_code ?? '-',
            'department' => $leave->user?->userDetail?->department ?? '-',
            'leave_type' => $leave->leaveType?->leave_name ?? '-',
            'from_date' => $leave->from_date?->format('d M Y') ?? '-',
            'to_date' => $leave->to_date?->format('d M Y') ?? '-',
            'days' => round((float) ($leave->payable_leave_days ?? $leave->total_days), 2),
            'status' => $leave->status,
        ];
    }

    protected function balancePayload(EmployeeLeaveBalance $balance): array
    {
        return [
            'leave_type' => $balance->leaveType?->leave_name ?? '-',
            'allocated' => round((float) $balance->allocated, 2),
            'used' => round((float) $balance->used, 2),
            'remaining' => round((float) $balance->remaining, 2),
            'carry_forward' => round((float) $balance->carry_forward, 2),
        ];
    }

    protected function employeePayload(User $employee): array
    {
        return [
            'id' => $employee->id,
            'name' => $this->employeeName($employee),
            'email' => $employee->email,
            'photo' => $this->photoUrl($employee),
            'employee_code' => $employee->userDetail?->emp_code ?? '-',
            'department' => $employee->userDetail?->department ?? '-',
            'designation' => $employee->userDetail?->designation ?? '-',
            'joining_date' => $employee->userDetail?->joining_date ? Carbon::parse($employee->userDetail->joining_date)->format('d M Y') : '-',
        ];
    }

    protected function approvalHistory(Collection $leaves): array
    {
        return $leaves->flatMap(function (LeaveApply $leave): array {
            $timeline = is_array($leave->approval_timeline) ? $leave->approval_timeline : [];
            return collect($timeline)->map(fn ($item): array => [
                'leave_type' => $leave->leaveType?->leave_name ?? '-',
                'action' => $item['action'] ?? '-',
                'status' => $item['status'] ?? $leave->status,
                'user' => $item['user_name'] ?? '-',
                'remarks' => $item['remarks'] ?? '-',
                'at' => isset($item['at']) ? Carbon::parse($item['at'])->format('d M Y h:i A') : '-',
            ])->all();
        })->values()->all();
    }

    protected function leaveInRange(LeaveApply $leave, array $filters): bool
    {
        if (! $leave->from_date || ! $leave->to_date) {
            return false;
        }

        return $leave->from_date->lte($this->fyEnd($filters)) && $leave->to_date->gte($this->fyStart($filters));
    }

    protected function fyStart(array $filters): Carbon
    {
        return Carbon::parse($this->leavePolicyService->financialYearStart($filters['financial_year']));
    }

    protected function fyEnd(array $filters): Carbon
    {
        return Carbon::parse($this->leavePolicyService->financialYearEnd($filters['financial_year']));
    }

    protected function isLwp(LeaveApply $leave): bool
    {
        $name = strtoupper((string) $leave->leaveType?->leave_name);
        return str_contains($name, 'LWP') || str_contains($name, 'WITHOUT PAY') || $this->leavePolicyService->isLeaveWithoutPay((int) $leave->leave_type_id);
    }

    protected function balanceByName(Collection $balances, array $terms): float
    {
        return round((float) $balances->filter(fn ($balance): bool => $this->nameContains($balance->leaveType?->leave_name, $terms))->sum('remaining'), 2);
    }

    protected function nameContains(?string $name, array $terms): bool
    {
        $name = strtoupper((string) $name);
        return collect($terms)->contains(fn ($term): bool => str_contains($name, strtoupper($term)));
    }

    protected function averageApprovalTime(Collection $leaves): string
    {
        $hours = $leaves->filter(fn ($leave): bool => $leave->created_at && ($leave->approved_at || $leave->rejected_at))->map(function ($leave): float {
            $ended = $leave->approved_at ?? $leave->rejected_at;
            return max(0, $leave->created_at->diffInMinutes($ended) / 60);
        });

        return $hours->count() ? round((float) $hours->avg(), 1) . ' hrs' : '-';
    }

    protected function employeeName(?User $employee): string
    {
        if (! $employee) {
            return '-';
        }

        $detailName = trim((string) ($employee->userDetail?->first_name . ' ' . $employee->userDetail?->last_name));
        return $detailName !== '' ? $detailName : (string) ($employee->name ?: '-');
    }

    protected function photoUrl(?User $employee): string
    {
        $photo = $employee?->userDetail?->profile_photo;
        return $photo ? asset('storage/' . ltrim((string) $photo, '/')) : asset('assets/images/users/avatar-1.jpg');
    }

    protected function hasAnyRole(User $user, array $roles): bool
    {
        $user->loadMissing('roles');
        return $user->roles->contains(fn ($role): bool => in_array($role->role_name, $roles, true));
    }

    protected function perPage(int $perPage): int
    {
        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
    }

    protected function filters(array $filters): array
    {
        $financialYear = trim((string) ($filters['financial_year'] ?? ''));
        $financialYear = $financialYear !== '' ? $financialYear : $this->leavePolicyService->currentFinancialYear();

        return [
            'financial_year' => $financialYear,
            'month' => $filters['month'] ?? null,
            'employee' => trim((string) ($filters['employee'] ?? '')),
            'employee_code' => trim((string) ($filters['employee_code'] ?? '')),
            'department' => trim((string) ($filters['department'] ?? '')),
            'designation' => trim((string) ($filters['designation'] ?? '')),
            'leave_type_id' => $filters['leave_type_id'] ?? null,
            'status' => trim((string) ($filters['status'] ?? '')),
            'approval_stage' => trim((string) ($filters['approval_stage'] ?? ($filters['approval_level'] ?? ''))),
            'approver_id' => $filters['approver_id'] ?? null,
            'from_date' => $filters['from_date'] ?? null,
            'to_date' => $filters['to_date'] ?? null,
            'per_page' => $filters['per_page'] ?? 25,
        ];
    }
}
