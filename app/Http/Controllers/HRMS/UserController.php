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
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        $filters = $request->only(['name', 'emp_code', 'department', 'designation', 'role', 'status']);
        $perPage = (int) $request->input('per_page', 10);
        $users = $this->userService->getFilteredUsers($filters, $perPage);
        $roles = $this->rolePermissionService->getRolesWithPermissions();

        return view('Adminpanel.HRMS.Employees.index', compact('users', 'filters', 'perPage', 'roles'));
    }

    /** Show employee creation form. */
    public function create(): View
    {
        $roles = $this->rolePermissionService->getRolesWithPermissions();

        return view('Adminpanel.HRMS.Employees.create', compact('roles'));
    }

    /** Store a new employee. */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->store($this->employeePayload($request));

        return redirect()->route('hrms.users.index')->with('success', 'Employee created successfully.');
    }

    /** Display an employee. */
    public function show(int $id): View
    {
        $user = $this->userService->getById($id);
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
        $user = $this->userService->getById($id);
        $roles = $this->rolePermissionService->getRolesWithPermissions();

        return view('Adminpanel.HRMS.Employees.edit', compact('user', 'roles'));
    }

    /** Update an employee. */
    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $this->userService->update($id, $this->employeePayload($request));

        return redirect()->route('hrms.users.index')->with('success', 'Employee updated successfully.');
    }

    /** Prepare employee request data for the service layer. */
    protected function employeePayload(StoreUserRequest|UpdateUserRequest $request): array
    {
        return $request->validated();
    }
    /** Deactivate an employee without removing their history. */
    public function destroy(int $id): RedirectResponse
    {
        $this->userService->deactivateUser($id);

        return redirect()->route('hrms.users.index')->with('success', 'Employee deactivated successfully.');
    }

    /** Assign a role to an employee. */
    public function assignRole(FormRequest $request): RedirectResponse
    {
        $this->userService->assignRole((int) $request->input('user_id'), (int) $request->input('role_id'));

        return redirect()->route('hrms.users.index')->with('success', 'Role assigned successfully.');
    }

    /** Remove an employee role. */
    public function removeRole(int $userId): RedirectResponse
    {
        $this->userService->removeRole($userId);

        return redirect()->route('hrms.users.index')->with('success', 'Role removed successfully.');
    }
}