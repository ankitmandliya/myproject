<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\LeaveServiceInterface;
use App\Contracts\RolePermissionServiceInterface;
use App\Contracts\SalaryServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\HRMS\StoreUserRequest;
use App\Http\Requests\HRMS\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Controller for HRMS employee operations.
 */
class UserController extends Controller
{
    /** Create a new controller instance. */
    public function __construct(
        protected UserServiceInterface $userService,
        protected RolePermissionServiceInterface $rolePermissionService,
        protected AttendanceServiceInterface $attendanceService,
        protected LeaveServiceInterface $leaveService,
        protected SalaryServiceInterface $salaryService
    ) {
    }

    /** Display employee listing. */
    public function index(Request $request): View
    {
        $filters = $request->only(['name', 'emp_code', 'department', 'designation', 'role', 'status', 'reporting_manager_id', 'sort']);
        $serviceFilters = $this->scopedFilters($filters);
        $perPage = (int) $request->input('per_page', 10);
        $users = $this->userService->getFilteredUsers($serviceFilters, $perPage);
        $roles = $this->rolePermissionService->getRolesWithPermissions();
        $reportingManagers = $this->userService->getReportingManagers();

        return view('Adminpanel.HRMS.Employees.index', compact('users', 'filters', 'perPage', 'roles', 'reportingManagers'));
    }

    /** Show employee creation form. */
    public function create(): View
    {
        abort_unless($this->canManageHierarchy(), 403);

        $roles = $this->rolePermissionService->getRolesWithPermissions();
        $reportingManagers = $this->userService->getReportingManagers();

        return view('Adminpanel.HRMS.Employees.create', compact('roles', 'reportingManagers'));
    }

    /** Store a new employee. */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        abort_unless($this->canManageHierarchy(), 403);

        try {
            $this->userService->store($this->employeePayload($request));
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['reporting_manager_id' => $exception->getMessage()]);
        }

        return redirect()->route('hrms.users.index')->with('success', 'Employee created successfully.');
    }

    /** Display an employee. */
    public function show(int $id): View
    {
        $user = $this->userService->getById($id);
        abort_unless($this->canViewEmployee($user), 403);

        $attendanceSummary = $this->attendanceService->getUserAttendanceSummary($user->id, (int) now()->month, (int) now()->year);
        $leaveSummary = $this->leaveService->getEmployeeLeaveSummary($user->id);
        $salarySummary = $this->salaryService->getEmployeeSalarySummary($user->id);

        return view(
            'Adminpanel.HRMS.Employees.show',
            compact('user', 'attendanceSummary', 'leaveSummary', 'salarySummary')
        );
    }

    /** Show employee edit form. */
    public function edit(int $id): View
    {
        abort_unless($this->canManageHierarchy(), 403);

        $user = $this->userService->getById($id);
        $roles = $this->rolePermissionService->getRolesWithPermissions();
        $reportingManagers = $this->userService->getReportingManagers($user->id);

        return view('Adminpanel.HRMS.Employees.edit', compact('user', 'roles', 'reportingManagers'));
    }

    /** Update an employee. */
    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        abort_unless($this->canManageHierarchy(), 403);

        try {
            $this->userService->update($id, $this->employeePayload($request));
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['reporting_manager_id' => $exception->getMessage()]);
        }

        return redirect()->route('hrms.users.index')->with('success', 'Employee updated successfully.');
    }

    /** Display reporting hierarchy report. */
    public function reportingHierarchy(Request $request): View
    {
        $filters = $request->only(['name', 'department', 'designation', 'status', 'reporting_manager_id', 'sort']);
        $serviceFilters = $this->scopedFilters($filters);
        $perPage = (int) $request->input('per_page', 25);
        $reportRows = $this->userService->getReportingHierarchyReport($serviceFilters, $perPage);
        $reportingManagers = $this->userService->getReportingManagers();

        return view('Adminpanel.HRMS.Employees.reporting-hierarchy', compact('reportRows', 'filters', 'perPage', 'reportingManagers'));
    }

    /** Prepare employee request data for the service layer. */
    protected function employeePayload(StoreUserRequest|UpdateUserRequest $request): array
    {
        return $request->validated();
    }

    /** Deactivate an employee without removing their history. */
    public function destroy(int $id): RedirectResponse
    {
        abort_unless($this->canManageHierarchy(), 403);

        $this->userService->deactivateUser($id);

        return redirect()->route('hrms.users.index')->with('success', 'Employee deactivated successfully.');
    }

    /** Assign a role to an employee. */
    public function assignRole(FormRequest $request): RedirectResponse
    {
        abort_unless($this->canManageHierarchy(), 403);

        $this->userService->assignRole((int) $request->input('user_id'), (int) $request->input('role_id'));

        return redirect()->route('hrms.users.index')->with('success', 'Role assigned successfully.');
    }

    /** Remove an employee role. */
    public function removeRole(int $userId): RedirectResponse
    {
        abort_unless($this->canManageHierarchy(), 403);

        $this->userService->removeRole($userId);

        return redirect()->route('hrms.users.index')->with('success', 'Role removed successfully.');
    }

    /** Apply HR/Admin, manager, and employee visibility rules. */
    protected function scopedFilters(array $filters): array
    {
        if ($this->canManageHierarchy()) {
            return $filters;
        }

        $actor = auth()->user();
        if ($actor instanceof User && $this->isReportingManager($actor)) {
            $filters['manager_scope'] = $actor->id;

            return $filters;
        }

        if ($actor instanceof User) {
            $filters['employee_scope'] = $actor->id;
        }

        return $filters;
    }

    /** Determine if the current user may view a profile. */
    protected function canViewEmployee(User $employee): bool
    {
        $actor = auth()->user();
        if (! $actor instanceof User) {
            return false;
        }

        return $this->canManageHierarchy()
            || (int) $actor->id === (int) $employee->id
            || (int) ($employee->userDetail?->reporting_manager_id ?? 0) === (int) $actor->id
            || (int) ($actor->userDetail?->reporting_manager_id ?? 0) === (int) $employee->id;
    }

    /** Determine if the current user can manage employee hierarchy. */
    protected function canManageHierarchy(): bool
    {
        $actor = auth()->user();
        if (! $actor instanceof User) {
            return false;
        }

        $actor->loadMissing('roles');
        $roleNames = $actor->roles->pluck('role_name');

        return $roleNames->isEmpty()
            || $roleNames->intersect(['Admin', 'HR'])->isNotEmpty();
    }

    /** Determine if the current user manages at least one active employee. */
    protected function isReportingManager(User $actor): bool
    {
        return \App\Models\UserDetail::query()
            ->where('reporting_manager_id', $actor->id)
            ->exists();
    }
}
