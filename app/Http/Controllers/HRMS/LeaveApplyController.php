<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\LeaveServiceInterface;
use App\Contracts\LeaveApprovalServiceInterface;
use App\Contracts\LeavePolicyServiceInterface;
use App\Contracts\RolePermissionServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\HRMS\ApproveLeaveRequest;
use App\Http\Requests\HRMS\RejectLeaveRequest;
use App\Http\Requests\HRMS\StoreLeaveRequest;
use App\Http\Requests\HRMS\UpdateLeaveRequest;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Controller for HRMS leave application operations.
 */
class LeaveApplyController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected LeaveServiceInterface $leaveService,
        protected RolePermissionServiceInterface $rolePermissionService,
        protected LeavePolicyServiceInterface $leavePolicyService,
        protected LeaveApprovalServiceInterface $leaveApprovalService
    ) {
    }

    /** Display leave application listing. */
    public function index(Request $request): View
    {
        $filters = $request->only(['employee', 'employee_code', 'department', 'leave_type_id', 'status', 'from_date', 'to_date']);
        $perPage = $this->perPage($request);
        $canManageLeave = $this->canManageLeave();
        $userId = $canManageLeave ? null : (int) auth()->id();
        $leaves = $this->leaveService->getFilteredLeaves($filters, $perPage, $userId);
        $leaves->setCollection($leaves->getCollection()->map(fn ($leave) => $this->prepareLeavePresentation($leave)));
        $summary = $this->leaveService->getLeaveSummary($userId);
        $leaveTypes = $this->leaveService->getActiveLeaveTypes();
        $canApproveLeave = $canManageLeave || $this->isReportingManager();
        $filters['per_page'] = $perPage;

        return view('Adminpanel.HRMS.Leaves.index', compact('leaves', 'summary', 'filters', 'perPage', 'leaveTypes', 'canManageLeave', 'canApproveLeave'));
    }

    /** Show leave application form. */
    public function create(): View
    {
        $employee = auth()->user();
        $employee?->loadMissing('userDetail');
        $leaveTypes = $this->leaveService->getActiveLeaveTypes();
        $summary = $this->leaveService->getLeaveSummary((int) auth()->id());
        $leaveBalances = $this->leaveService->getEmployeeLeaveSummary((int) auth()->id())['balances'] ?? [];

        return view('Adminpanel.HRMS.Leaves.create', compact('employee', 'leaveTypes', 'summary', 'leaveBalances'));
    }

    /** Calculate leave duration and balance live for the leave form. */
    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:users,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'half_day' => ['nullable', 'boolean'],
            'is_half_day' => ['nullable', 'boolean'],
            'half_day_session' => ['nullable', 'string', 'in:first_half,second_half'],
            'half_day_type' => ['nullable', 'string', 'in:first_half,second_half'],
            'emergency_leave' => ['nullable', 'boolean'],
        ]);
        $data['employee_id'] = $this->requestUserId($data);
        $data['user_id'] = $data['employee_id'];

        try {
            return response()->json($this->leavePolicyService->calculateLiveLeave($data));
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return response()->json([
                'warning' => $exception->getMessage(),
                'can_submit' => false,
            ], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected live leave calculation exception.', ['user_id' => auth()->id(), 'exception' => $exception]);

            return response()->json([
                'warning' => 'Unable to calculate leave. Please try again or contact the administrator.',
                'can_submit' => false,
            ], 500);
        }
    }
    /** Store a leave application. */
    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $this->requestUserId($data);

        try {
            $this->leaveService->applyLeave((int) $data['user_id'], $data);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected leave store exception.', ['user_id' => auth()->id(), 'exception' => $exception]);

            return back()->withInput()->with('error', 'Unable to apply leave. Please try again or contact the administrator.');
        }

        return redirect()->route('hrms.leave-apply.index')->with('success', 'Leave applied successfully.');
    }

    /** Display a leave application. */
    public function show(int $id): View
    {
        $leave = $this->prepareLeavePresentation($this->authorizedLeave($id));
        $statusBadge = $leave->status_badge;

        return view('Adminpanel.HRMS.Leaves.show', compact('leave', 'statusBadge'));
    }

    /** Show leave edit form. */
    public function edit(int $id): View
    {
        $leave = $this->authorizedLeave($id);
        abort_unless($leave->status === 'Pending', 403);
        $employee = $leave->user;
        $employee?->loadMissing('userDetail');
        $leaveTypes = $this->leaveService->getActiveLeaveTypes();
        $summary = $this->leaveService->getLeaveSummary((int) $leave->user_id);
        $leaveBalances = $this->leaveService->getEmployeeLeaveSummary((int) $leave->user_id)['balances'] ?? [];

        return view('Adminpanel.HRMS.Leaves.edit', compact('leave', 'employee', 'leaveTypes', 'summary', 'leaveBalances'));
    }

    /** Update a leave application. */
    public function update(UpdateLeaveRequest $request, int $id): RedirectResponse
    {
        $leave = $this->authorizedLeave($id);
        abort_unless($leave->status === 'Pending', 403);
        $data = $request->validated();
        $data['user_id'] = $this->requestUserId($data, (int) $leave->user_id);

        try {
            $this->leaveService->updateLeave($id, $data);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected leave update exception.', ['leave_id' => $id, 'exception' => $exception]);

            return back()->withInput()->with('error', 'Unable to update leave. Please try again or contact the administrator.');
        }

        return redirect()->route('hrms.leave-apply.index')->with('success', 'Leave updated successfully.');
    }

    /** Delete a leave application. */
    public function destroy(int $id): RedirectResponse
    {
        $this->authorizedLeave($id);

        try {
            $this->leaveService->deleteLeave($id);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected leave delete exception.', ['leave_id' => $id, 'exception' => $exception]);

            return back()->with('error', 'Unable to delete leave. Please try again or contact the administrator.');
        }

        return redirect()->route('hrms.leave-apply.index')->with('success', 'Leave deleted successfully.');
    }

    /** Display leave requests for approval workflow. */
    public function approvals(Request $request): View
    {
        abort_unless($this->canManageLeave() || $this->isReportingManager(), 403);
        $filters = $request->only([
            'employee', 'employee_code', 'department', 'designation', 'leave_type_id', 'status',
            'approval_level', 'financial_year', 'from_date', 'to_date', 'per_page',
        ]);
        $perPage = $this->perPage($request);
        $leaves = $this->leaveApprovalService->getApprovalDashboard($filters, $perPage, (int) auth()->id());
        $leaves->setCollection($leaves->getCollection()->map(fn ($leave) => $this->prepareLeavePresentation($leave)));
        $summary = $this->approvalSummary($leaves->getCollection());
        $dashboardCards = $this->approvalDashboardCards($summary);
        $leaveTypes = $this->leaveService->getActiveLeaveTypes();
        $statuses = \App\Services\LeaveApprovalService::STATUSES;
        $approvalLevels = $this->leaveApprovalService->configuredLevels();
        $canBulkApprove = $this->canManageLeave();
        $canBulkReject = $this->canManageLeave();
        $filters['per_page'] = $perPage;

        return view('Adminpanel.HRMS.Leaves.approvals', compact(
            'leaves', 'summary', 'dashboardCards', 'filters', 'perPage', 'leaveTypes', 'statuses',
            'approvalLevels', 'canBulkApprove', 'canBulkReject'
        ));
    }

    /** Approve a leave application. */
    public function approve(ApproveLeaveRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        try {
            $this->leaveApprovalService->approve($id, (int) auth()->id(), $data['remarks'] ?? null);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected leave approve exception.', ['leave_id' => $id, 'exception' => $exception]);

            return back()->withInput()->with('error', 'Unable to approve leave. Please try again or contact the administrator.');
        }

        return back()->with('success', 'Leave approval step completed successfully.');
    }

    /** Reject a leave application. */
    public function reject(RejectLeaveRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        try {
            $this->leaveApprovalService->reject($id, (int) auth()->id(), (string) $data['remarks']);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected leave reject exception.', ['leave_id' => $id, 'exception' => $exception]);

            return back()->withInput()->with('error', 'Unable to reject leave. Please try again or contact the administrator.');
        }

        return back()->with('success', 'Leave rejected successfully.');
    }

    /** Cancel a leave application before final approval. */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate(['remarks' => ['nullable', 'string', 'max:1000']]);

        try {
            $this->leaveApprovalService->cancel($id, (int) auth()->id(), $data['remarks'] ?? null);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Leave cancelled successfully.');
    }

    /** Revoke an approved leave application. */
    public function revoke(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate(['remarks' => ['nullable', 'string', 'max:1000']]);

        try {
            $this->leaveApprovalService->revoke($id, (int) auth()->id(), $data['remarks'] ?? null);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Leave revoked successfully.');
    }

    /** Display leave calendar. */
    public function calendar(Request $request): View
    {
        $selectedMonth = (string) $request->input('month', Carbon::now()->format('Y-m'));
        $calendarMonth = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth) === 1
            ? Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth()
            : Carbon::now()->startOfMonth();
        $canManageLeave = $this->canManageLeave();
        $userId = $canManageLeave ? null : (int) auth()->id();
        $calendarItems = $this->leaveService->getLeaveCalendar((int) $calendarMonth->month, (int) $calendarMonth->year, $userId);
        $filters = ['month' => $calendarMonth->format('Y-m')];
        $monthLabel = $calendarMonth->format('F Y');
        $previousMonth = $calendarMonth->copy()->subMonth()->format('Y-m');
        $currentMonth = Carbon::now()->format('Y-m');
        $nextMonth = $calendarMonth->copy()->addMonth()->format('Y-m');

        return view('Adminpanel.HRMS.Leaves.calendar', compact(
            'calendarItems', 'filters', 'monthLabel', 'previousMonth', 'currentMonth', 'nextMonth'
        ));
    }

    /** Display leave history. */
    public function history(): View
    {
        $leaves = $this->leaveService->getFilteredLeaves([], 10, (int) auth()->id());
        $leaves->setCollection($leaves->getCollection()->map(fn ($leave) => $this->prepareLeavePresentation($leave)));
        $summary = $this->leaveService->getLeaveSummary((int) auth()->id());
        $filters = [];
        $perPage = 10;
        $leaveTypes = $this->leaveService->getActiveLeaveTypes();
        $canManageLeave = false;
        $canApproveLeave = $this->isReportingManager();

        return view('Adminpanel.HRMS.Leaves.index', compact('leaves', 'summary', 'filters', 'perPage', 'leaveTypes', 'canManageLeave', 'canApproveLeave'));
    }

    /** Prepare display-only leave fields for list, detail, history, and approval views. */
    protected function prepareLeavePresentation(mixed $leave): mixed
    {
        $leave->loadMissing([
            'user.userDetail',
            'leaveType',
            'approvedBy',
            'rejectedBy',
            'cancelledBy',
            'revokedBy',
            'manager',
            'hrApprover',
            'adminApprover',
        ]);

        $employee = $leave->user;
        $detail = $employee?->userDetail;
        $badge = $this->statusBadge((string) ($leave->status ?? 'Pending'));
        $payableDays = (float) ($leave->payable_leave_days ?? $leave->total_days ?? 0);
        $requestedDays = (float) ($leave->requested_days ?? $leave->total_days ?? 0);
        $durationText = $this->daysLabel($payableDays);
        $currentStage = $this->stageLabel((string) ($leave->approval_level ?? ''), (string) ($leave->status ?? ''));
        $auditItems = $this->approvalItems($leave);
        $timelineItems = $this->timelineItems($leave, $auditItems);

        $leave->setAttribute('employee_photo_url', $this->employeePhotoUrl($detail?->profile_photo));
        $leave->setAttribute('employee_name', $this->employeeName($employee));
        $leave->setAttribute('employee_code', $detail?->emp_code ?: '-');
        $leave->setAttribute('department', $detail?->department ?: '-');
        $leave->setAttribute('designation', $detail?->designation ?: '-');
        $leave->setAttribute('joining_date_label', $this->dateLabel($detail?->joining_date));
        $leave->setAttribute('leave_type_label', $leave->leaveType?->leave_name ?? '-');
        $leave->setAttribute('status_badge', $badge);
        $leave->setAttribute('status_color', $badge['color']);
        $leave->setAttribute('status_label', $badge['label']);
        $leave->setAttribute('duration_text', $durationText);
        $leave->setAttribute('requested_days_label', number_format($requestedDays, 2));
        $leave->setAttribute('payable_days_label', number_format($payableDays, 2));
        $leave->setAttribute('holiday_days_label', number_format((float) ($leave->holiday_days ?? 0), 2));
        $leave->setAttribute('weekly_off_days_label', number_format((float) ($leave->weekly_off_days ?? 0), 2));
        $leave->setAttribute('applied_date_label', $this->dateTimeLabel($leave->created_at));
        $leave->setAttribute('approved_date_label', $this->dateTimeLabel($leave->approved_at));
        $leave->setAttribute('rejected_date_label', $this->dateTimeLabel($leave->rejected_at));
        $leave->setAttribute('cancelled_date_label', $this->dateTimeLabel($leave->cancelled_at));
        $leave->setAttribute('revoked_date_label', $this->dateTimeLabel($leave->revoked_at));
        $leave->setAttribute('from_date_label', $this->dateLabel($leave->from_date));
        $leave->setAttribute('to_date_label', $this->dateLabel($leave->to_date));
        $leave->setAttribute('approved_by_label', $leave->approvedBy?->name ?? '-');
        $leave->setAttribute('rejected_by_label', $leave->rejectedBy?->name ?? '-');
        $leave->setAttribute('cancelled_by_label', $leave->cancelledBy?->name ?? '-');
        $leave->setAttribute('revoked_by_label', $leave->revokedBy?->name ?? '-');
        $leave->setAttribute('pending_with', $this->pendingWith($leave));
        $leave->setAttribute('current_stage_label', $currentStage);
        $leave->setAttribute('reason_label', $leave->reason ?: '-');
        $leave->setAttribute('remarks_label', $this->latestRemarks($leave) ?: '-');
        $leave->setAttribute('audit_items', $auditItems);
        $leave->setAttribute('timeline_items', $timelineItems);
        $leave->setAttribute('balance_summary', $this->balanceSummary($leave));
        $leave->setAttribute('sandwich_summary', $this->sandwichSummary($leave));
        $leave->setAttribute('lwp_summary', $this->lwpSummary($leave));
        $leave->setAttribute('remaining_balance', $this->remainingBalanceLabel($leave));
        $leave->setAttribute('can_approve', $this->leaveApprovalService->canAct($leave, (int) auth()->id(), 'approve'));
        $leave->setAttribute('can_reject', $this->leaveApprovalService->canAct($leave, (int) auth()->id(), 'reject'));
        $leave->setAttribute('can_cancel', $this->leaveApprovalService->canAct($leave, (int) auth()->id(), 'cancel'));
        $leave->setAttribute('can_revoke', $this->leaveApprovalService->canAct($leave, (int) auth()->id(), 'revoke'));

        return $leave;
    }

    protected function statusBadge(string $status): array
    {
        return match ($status) {
            \App\Services\LeaveApprovalService::STATUS_APPROVED => ['label' => $status, 'class' => 'badge-success', 'color' => 'success'],
            \App\Services\LeaveApprovalService::STATUS_REJECTED => ['label' => $status, 'class' => 'badge-danger', 'color' => 'danger'],
            \App\Services\LeaveApprovalService::STATUS_CANCELLED => ['label' => $status, 'class' => 'badge-secondary', 'color' => 'secondary'],
            \App\Services\LeaveApprovalService::STATUS_REVOKED => ['label' => $status, 'class' => 'badge-dark', 'color' => 'dark'],
            \App\Services\LeaveApprovalService::STATUS_MANAGER_APPROVED => ['label' => $status, 'class' => 'badge-info', 'color' => 'info'],
            \App\Services\LeaveApprovalService::STATUS_HR_APPROVED => ['label' => $status, 'class' => 'badge-primary', 'color' => 'primary'],
            default => ['label' => $status ?: 'Pending', 'class' => 'badge-warning', 'color' => 'warning'],
        };
    }

    protected function approvalItems(mixed $leave): array
    {
        $timeline = is_array($leave->approval_timeline) ? $leave->approval_timeline : [];
        $audit = is_array($leave->approval_audit_log) ? $leave->approval_audit_log : [];
        $items = $audit !== [] ? $audit : $timeline;

        return collect($items)->map(function (array $item) use ($leave): array {
            $action = (string) ($item['action'] ?? 'Workflow Update');
            $at = $this->parseDate($item['at'] ?? null);

            return [
                'user' => (string) ($item['user_name'] ?? '-'),
                'role' => $this->roleFromAction($action),
                'action' => $action,
                'badge' => $this->badgeForAction($action, (string) ($item['status'] ?? $leave->status ?? '')),
                'date' => $this->dateLabel($at),
                'time' => $this->timeLabel($at),
                'remarks' => (string) (($item['remarks'] ?? null) ?: '-'),
                'ip' => (string) (($item['ip'] ?? null) ?: '-'),
            ];
        })->values()->all();
    }

    protected function timelineItems(mixed $leave, array $auditItems): array
    {
        if ($auditItems !== []) {
            return $auditItems;
        }

        return [[
            'user' => $this->employeeName($leave->user),
            'role' => 'Employee',
            'action' => 'Applied',
            'badge' => 'warning',
            'date' => $this->dateLabel($leave->created_at),
            'time' => $this->timeLabel($leave->created_at),
            'remarks' => $leave->reason ?: '-',
            'ip' => '-',
        ]];
    }

    protected function stageLabel(string $level, string $status): string
    {
        if (in_array($status, [
            \App\Services\LeaveApprovalService::STATUS_APPROVED,
            \App\Services\LeaveApprovalService::STATUS_REJECTED,
            \App\Services\LeaveApprovalService::STATUS_CANCELLED,
            \App\Services\LeaveApprovalService::STATUS_REVOKED,
        ], true)) {
            return $status;
        }

        return match (strtolower($level)) {
            \App\Services\LeaveApprovalService::LEVEL_MANAGER => 'Manager Approval',
            \App\Services\LeaveApprovalService::LEVEL_HR => 'HR Approval',
            \App\Services\LeaveApprovalService::LEVEL_ADMIN => 'Admin Approval',
            'auto' => 'Auto Approval',
            'closed' => 'Completed',
            default => 'Pending Approval',
        };
    }

    protected function pendingWith(mixed $leave): string
    {
        return match (strtolower((string) ($leave->approval_level ?? ''))) {
            \App\Services\LeaveApprovalService::LEVEL_MANAGER => $leave->manager?->name ?? 'Manager',
            \App\Services\LeaveApprovalService::LEVEL_HR => 'HR',
            \App\Services\LeaveApprovalService::LEVEL_ADMIN => 'Admin',
            default => '-',
        };
    }

    protected function employeeName(mixed $employee): string
    {
        if (! $employee) {
            return '-';
        }
        $detail = $employee->userDetail;
        $name = trim((string) ($detail?->first_name . ' ' . $detail?->last_name));

        return $name !== '' ? $name : (string) ($employee->name ?: '-');
    }

    protected function employeePhotoUrl(?string $photo): string
    {
        return $photo ? asset('storage/' . ltrim($photo, '/')) : asset('assets/img/profile.jpg');
    }

    protected function dateLabel(mixed $value): string
    {
        $date = $this->parseDate($value);
        return $date ? $date->format('d M Y') : '-';
    }

    protected function dateTimeLabel(mixed $value): string
    {
        $date = $this->parseDate($value);
        return $date ? $date->format('d M Y h:i A') : '-';
    }

    protected function timeLabel(mixed $value): string
    {
        $date = $this->parseDate($value);
        return $date ? $date->format('h:i A') : '-';
    }

    protected function parseDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    protected function daysLabel(float $days): string
    {
        return number_format($days, 2) . ' ' . (abs($days - 1.0) < 0.001 ? 'day' : 'days');
    }

    protected function latestRemarks(mixed $leave): ?string
    {
        foreach (['admin_remarks', 'hr_remarks', 'manager_remarks'] as $field) {
            if (! empty($leave->{$field})) {
                return (string) $leave->{$field};
            }
        }

        return null;
    }

    protected function roleFromAction(string $action): string
    {
        $action = strtolower($action);
        return str_contains($action, 'manager') ? 'Manager' : (str_contains($action, 'hr') ? 'HR' : (str_contains($action, 'admin') ? 'Admin' : (str_contains($action, 'employee') || str_contains($action, 'applied') ? 'Employee' : 'System')));
    }

    protected function badgeForAction(string $action, string $status): string
    {
        $text = strtolower($action . ' ' . $status);
        if (str_contains($text, 'reject')) {
            return 'danger';
        }
        if (str_contains($text, 'cancel')) {
            return 'secondary';
        }
        if (str_contains($text, 'revoke')) {
            return 'dark';
        }
        if (str_contains($text, 'approve')) {
            return 'success';
        }

        return $this->statusBadge($status)['color'];
    }


    protected function balanceSummary(mixed $leave): array
    {
        return [
            'requested' => number_format((float) ($leave->requested_days ?? $leave->total_days ?? 0), 2),
            'payable' => number_format((float) ($leave->payable_leave_days ?? $leave->total_days ?? 0), 2),
            'remaining' => $this->remainingBalanceLabel($leave),
        ];
    }

    protected function sandwichSummary(mixed $leave): array
    {
        return [
            'days' => number_format((float) ($leave->sandwich_days ?? 0), 2),
            'label' => number_format((float) ($leave->sandwich_days ?? 0), 2) . ' days',
        ];
    }

    protected function lwpSummary(mixed $leave): array
    {
        $isLwp = $this->presentationLeaveTypeIsWithoutPay($leave);
        return [
            'is_lwp' => $isLwp,
            'days' => $isLwp ? number_format((float) ($leave->payable_leave_days ?? $leave->total_days ?? 0), 2) : '0.00',
        ];
    }

    protected function remainingBalanceLabel(mixed $leave): string
    {
        if ($this->presentationLeaveTypeIsWithoutPay($leave)) {
            return 'Not applicable';
        }

        return '-';
    }

    protected function presentationLeaveTypeIsWithoutPay(mixed $leave): bool
    {
        $code = strtoupper(trim((string) $leave->leaveType?->leave_code));
        $name = strtolower(trim((string) $leave->leaveType?->leave_name));
        $isPaid = $leave->leaveType?->getRawOriginal('is_paid');

        return in_array($code, ['LWP', 'LOP', 'LWOP'], true)
            || str_contains($name, 'without pay')
            || str_contains($name, 'loss of pay')
            || str_contains($name, 'unpaid')
            || ($isPaid !== null && (bool) $leave->leaveType?->is_paid === false);
    }
    /** Prepare approval dashboard counts from the already-loaded presentation collection. */
    protected function approvalSummary(mixed $leaves): array
    {
        return [
            'pending' => $leaves->where('status', \App\Services\LeaveApprovalService::STATUS_PENDING)->count(),
            'manager_pending' => $leaves->where('approval_level', \App\Services\LeaveApprovalService::LEVEL_MANAGER)->count(),
            'hr_pending' => $leaves->where('approval_level', \App\Services\LeaveApprovalService::LEVEL_HR)->count(),
            'approved' => $leaves->where('status', \App\Services\LeaveApprovalService::STATUS_APPROVED)->count(),
            'rejected' => $leaves->where('status', \App\Services\LeaveApprovalService::STATUS_REJECTED)->count(),
            'cancelled' => $leaves->where('status', \App\Services\LeaveApprovalService::STATUS_CANCELLED)->count(),
            'revoked' => $leaves->where('status', \App\Services\LeaveApprovalService::STATUS_REVOKED)->count(),
        ];
    }

    /** Prepare approval dashboard card view models. */
    protected function approvalDashboardCards(array $summary): array
    {
        return [
            ['label' => 'Pending Approval', 'value' => $summary['pending'] ?? 0, 'class' => 'warning', 'icon' => 'fa-clock'],
            ['label' => 'Manager Pending', 'value' => $summary['manager_pending'] ?? 0, 'class' => 'info', 'icon' => 'fa-user-tie'],
            ['label' => 'HR Pending', 'value' => $summary['hr_pending'] ?? 0, 'class' => 'primary', 'icon' => 'fa-user-check'],
            ['label' => 'Approved', 'value' => $summary['approved'] ?? 0, 'class' => 'success', 'icon' => 'fa-check-circle'],
        ];
    }
    /** Resolve a user ID from request data according to the active user's role. */
    protected function requestUserId(array $data, ?int $fallback = null): int
    {
        if ($this->canManageLeave() && isset($data['user_id'])) {
            return (int) $data['user_id'];
        }

        return $fallback ?? (int) auth()->id();
    }

    /** Resolve a leave after checking employee or HR/Admin access. */
    protected function authorizedLeave(int $id): mixed
    {
        $leave = $this->leaveService->getById($id);
        abort_unless((int) $leave->user_id === (int) auth()->id() || $this->canManageLeave() || $this->isLeaveReportingManager($leave), 403);

        return $leave;
    }

    /** Restrict leave management to HR and Admin roles. */
    protected function authorizeLeaveManagement(): void
    {
        abort_unless($this->canManageLeave(), 403);
    }

    /** Determine whether the authenticated user manages any reporting employees. */
    protected function isReportingManager(): bool
    {
        return \App\Models\UserDetail::where('reporting_manager_id', (int) auth()->id())->exists();
    }

    /** Determine whether the authenticated user is the reporting manager for this leave. */
    protected function isLeaveReportingManager(mixed $leave): bool
    {
        return (int) ($leave->user?->userDetail?->reporting_manager_id ?? 0) === (int) auth()->id();
    }

    /** Determine whether the authenticated user can manage leave requests. */
    protected function canManageLeave(): bool
    {
        return $this->rolePermissionService->userHasAnyRole((int) auth()->id(), ['Admin', 'HR']);
    }

    /** Resolve supported pagination sizes. */
    protected function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 10);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
    }
}









