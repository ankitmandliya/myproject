<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\LeaveReportServiceInterface;
use App\Contracts\LeaveServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use App\Services\LeaveApprovalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeaveReportController extends Controller
{
    public function __construct(
        protected LeaveReportServiceInterface $leaveReportService,
        protected LeaveServiceInterface $leaveService
    ) {}

    public function index(Request $request): View
    {
        $actor = $this->authorizeReports();
        $report = $this->leaveReportService->dashboard($request->query(), $actor);

        return view('Adminpanel.HRMS.LeaveReports.index', $this->viewData($request, $report));
    }

    public function employee(Request $request): View
    {
        return $this->reportView($request, 'employee', 'employee-report');
    }

    public function department(Request $request): View
    {
        return $this->reportView($request, 'department', 'department-report');
    }

    public function leaveType(Request $request): View
    {
        return $this->reportView($request, 'leave-type', 'leave-type-report');
    }

    public function balance(Request $request): View
    {
        return $this->reportView($request, 'balance', 'balance-report');
    }

    public function liability(Request $request): View
    {
        return $this->reportView($request, 'liability', 'liability-report');
    }

    public function monthly(Request $request): View
    {
        return $this->reportView($request, 'monthly', 'monthly-report');
    }

    public function financialYear(Request $request): View
    {
        return $this->reportView($request, 'financial-year', 'financial-year-report');
    }

    public function approval(Request $request): View
    {
        return $this->reportView($request, 'approval', 'approval-report');
    }

    public function lwp(Request $request): View
    {
        return $this->reportView($request, 'lwp', 'lwp-report');
    }

    public function sandwich(Request $request): View
    {
        return $this->reportView($request, 'sandwich', 'sandwich-report');
    }

    public function employeeShow(Request $request, int $employee): View
    {
        $actor = $this->authorizeReports();
        $report = $this->leaveReportService->employeeDetails($employee, $request->query(), $actor);

        return view('Adminpanel.HRMS.LeaveReports.employee-details', $this->viewData($request, $report));
    }

    protected function reportView(Request $request, string $type, string $view): View
    {
        $actor = $this->authorizeReports();
        $report = $this->leaveReportService->report($type, $request->query(), $this->perPage($request), $actor);

        return view('Adminpanel.HRMS.LeaveReports.' . $view, $this->viewData($request, $report));
    }

    protected function viewData(Request $request, array $report): array
    {
        return [
            'report' => $report,
            'filters' => $report['filters'] ?? $request->query(),
            'leaveTypes' => $this->leaveService->getActiveLeaveTypes(),
            'statuses' => LeaveApprovalService::STATUSES,
            'approvalStages' => [
                LeaveApprovalService::LEVEL_MANAGER => 'Manager',
                LeaveApprovalService::LEVEL_HR => 'HR',
                LeaveApprovalService::LEVEL_ADMIN => 'Admin',
                'auto' => 'Auto',
                'closed' => 'Closed',
            ],
            'approvers' => $this->approvers(),
            'perPageOptions' => [10, 25, 50, 100],
            'reportRoutes' => $this->reportRoutes(),
        ];
    }

    protected function authorizeReports(): User
    {
        $actor = auth()->user();

        if (! $actor instanceof User) {
            throw new HttpException(403, 'Unauthorized.');
        }

        if (! $this->hasAnyRole($actor, ['Admin', 'HR']) && ! $this->isReportingManager($actor)) {
            throw new HttpException(403, 'You are not authorized to view leave reports.');
        }

        return $actor;
    }

    protected function hasAnyRole(User $user, array $roles): bool
    {
        $user->loadMissing('roles');
        return $user->roles->contains(fn ($role): bool => in_array($role->role_name, $roles, true));
    }

    protected function isReportingManager(User $user): bool
    {
        return UserDetail::query()->where('reporting_manager_id', $user->id)->exists();
    }

    protected function approvers(): array
    {
        return User::query()
            ->with('userDetail')
            ->whereHas('roles', fn ($query) => $query->whereIn('role_name', ['Admin', 'HR']))
            ->orWhereIn('id', UserDetail::query()->select('reporting_manager_id')->whereNotNull('reporting_manager_id'))
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
            ->values()
            ->all();
    }

    protected function reportRoutes(): array
    {
        return [
            'Dashboard' => route('hrms.leave-reports.index'),
            'Employee' => route('hrms.leave-reports.employee'),
            'Department' => route('hrms.leave-reports.department'),
            'Leave Type' => route('hrms.leave-reports.leave-type'),
            'Balance' => route('hrms.leave-reports.balance'),
            'Liability' => route('hrms.leave-reports.liability'),
            'Monthly' => route('hrms.leave-reports.monthly'),
            'Financial Year' => route('hrms.leave-reports.financial-year'),
            'Approval' => route('hrms.leave-reports.approval'),
            'LWP' => route('hrms.leave-reports.lwp'),
            'Sandwich' => route('hrms.leave-reports.sandwich'),
        ];
    }

    protected function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);
        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
    }
}
