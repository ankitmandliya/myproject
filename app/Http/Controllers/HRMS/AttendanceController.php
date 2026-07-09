<?php

declare(strict_types=1);

namespace App\Http\Controllers\HRMS;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\RolePermissionServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\HRMS\StoreAttendanceRequest;
use App\Http\Requests\HRMS\UpdateAttendanceRequest;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/** Controller for HRMS attendance operations. */
class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceServiceInterface $attendanceService,
        protected UserServiceInterface $userService,
        protected RolePermissionServiceInterface $rolePermissionService
    )
    {
    }

    /** Display filtered attendance and prepared presentation data. */
    public function index(Request $request): View
    {
        if (! $this->canManageAttendance()) {
            return $this->myAttendance($request);
        }

        $filters = $request->only(['name', 'emp_code', 'department', 'designation', 'status', 'from_date', 'to_date']);
        $perPage = (int) $request->input('per_page', 10);
        $attendance = $this->attendanceService->getFilteredAttendance($filters, $perPage);
        $summary = $this->attendanceService->getTodaySummary();
        $statusList = ['Present', 'Absent', 'Late', 'Half Day', 'Leave', 'Holiday'];
        $records = $attendance->getCollection();
        $employees = $records->pluck('user')->filter()->unique('id')->values();
        $departments = $records->pluck('user.userDetail.department')->filter()->unique()->values();
        $designations = $records->pluck('user.userDetail.designation')->filter()->unique()->values();

        $records->each(function ($record): void {
            $todayAttendance = $this->attendanceService->getTodayAttendance((int) $record->user_id);
            $record->setAttribute('can_check_in', false);
            $record->setAttribute('can_check_out', $todayAttendance?->id === $record->id && $todayAttendance->check_out === null);
            $record->setAttribute('can_edit', true);
            $record->setAttribute('can_delete', true);
        });

        return view('Adminpanel.HRMS.Attendance.index', compact(
            'attendance', 'summary', 'filters', 'statusList', 'employees',
            'departments', 'designations', 'perPage'
        ));
    }

    public function create(): View
    {
        return view('Adminpanel.HRMS.Attendance.create');
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->attendanceService->markCheckIn((int) $data['user_id'], $data);

        return redirect()->route('hrms.attendance.index')->with('success', 'Attendance saved successfully.');
    }

    public function show(int $id): View
    {
        $attendance = $this->attendanceService->getById($id);
        $employee = $attendance->user;
        $workingHours = $attendance->working_hours;
        $status = $attendance->status;
        $lateMinutes = $attendance->late_minutes;
        $notes = $attendance->notes ?? null;

        return view('Adminpanel.HRMS.Attendance.show', compact(
            'attendance', 'employee', 'workingHours', 'status', 'lateMinutes', 'notes'
        ));
    }

    public function edit(int $id): View
    {
        $attendance = $this->attendanceService->getById($id);

        return view('Adminpanel.HRMS.Attendance.edit', compact('attendance'));
    }

    public function update(UpdateAttendanceRequest $request, int $id): RedirectResponse
    {
        $this->attendanceService->getById($id);

        return redirect()->route('hrms.attendance.index')->with('success', 'Attendance updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->attendanceService->deleteAttendance($id);

        return redirect()->route('hrms.attendance.index')->with('success', 'Attendance deleted successfully.');
    }

    public function checkIn(StoreAttendanceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->attendanceService->markCheckIn((int) $data['user_id'], $data);

        return redirect()->route('hrms.attendance.index')->with('success', 'Check-in marked successfully.');
    }

    public function checkOut(int $userId): RedirectResponse
    {
        $this->attendanceService->markCheckOut($userId);

        return redirect()->route('hrms.attendance.index')->with('success', 'Check-out marked successfully.');
    }

    public function monthlyReport(int $userId, int $month, int $year): View
    {
        $attendance = $this->attendanceService->getMonthlyAttendance($userId, $month, $year);

        return view('Adminpanel.HRMS.Attendance.monthly-report', compact('attendance'));
    }

    /** Display an employee's monthly attendance history. */
    public function history(Request $request, int $employeeId): View
    {
        $employee = $this->authorizedEmployee($employeeId);
        $selectedMonth = (string) $request->input('month', Carbon::now()->format('Y-m'));
        $historyMonth = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth) === 1
            ? Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth()
            : Carbon::now()->startOfMonth();
        $month = (int) $historyMonth->month;
        $year = (int) $historyMonth->year;
        $attendance = $this->attendanceService->getMonthlyAttendance($employeeId, $month, $year);
        $currentMonth = ['month' => $month, 'year' => $year];
        $monthLabel = Carbon::create($year, $month, 1)->format('F Y');

        return view('Adminpanel.HRMS.Attendance.attendance-history', compact(
            'attendance', 'employee', 'currentMonth', 'monthLabel'
        ));
    }

    /** Display the authorized employee's monthly attendance calendar. */
    public function calendar(Request $request, int $employeeId): View
    {
        $employee = $this->authorizedEmployee($employeeId);
        $selectedMonth = (string) $request->input('month', Carbon::now()->format('Y-m'));
        $calendarMonth = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth) === 1
            ? Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth()
            : Carbon::now()->startOfMonth();
        $month = (int) $calendarMonth->month;
        $year = (int) $calendarMonth->year;
        $calendarData = $this->attendanceService->getMonthlyCalendar($month, $year, $employeeId);
        $attendanceSummary = $this->attendanceService->getUserAttendanceSummary($employeeId, $month, $year);
        $isDemoCalendar = ! $calendarData['has_attendance'] && app()->environment(['local', 'testing']);

        if ($isDemoCalendar) {
            $demoStatuses = [
                'Present', 'Present', 'Late', 'Present', 'Half Day', 'Present', 'Absent',
                'Present', 'Leave', 'Present', 'Absent', 'Present', 'Present', 'Late',
                'Present', 'Late', 'Present', 'Half Day', 'Present', 'Present', 'Leave',
                'Present', 'Present', 'Leave', 'Present', 'Late', 'Present', 'Absent',
            ];
            foreach ($calendarData['weeks'] as &$week) {
                foreach ($week as &$day) {
                    if ($day['is_current_month'] && $day['status'] === 'No Attendance') {
                        $day['status'] = $demoStatuses[($day['day'] - 1) % count($demoStatuses)];
                        $day['statuses'] = [$day['status']];
                    }
                }
                unset($day);
            }
            unset($week);
        }

        return view('Adminpanel.HRMS.Attendance.attendance-calendar', compact(
            'calendarData', 'isDemoCalendar', 'employee', 'attendanceSummary'
        ));
    }

    /** Render the authenticated user's prepared header attendance widget. */
    public function widget(): View
    {
        $attendanceWidget = $this->attendanceService->getTodayAttendanceWidget((int) auth()->id());

        return view('Adminpanel.HRMS.Attendance._partials.header-widget', compact('attendanceWidget'));
    }

    /** Check in the authenticated user. */
    public function widgetCheckIn(): RedirectResponse
    {
        try {
            $this->attendanceService->markCheckIn((int) auth()->id(), []);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Check-in marked successfully.');
    }

    /** Check out the authenticated user. */
    public function widgetCheckOut(): RedirectResponse
    {
        try {
            $this->attendanceService->markCheckOut((int) auth()->id());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Check-out marked successfully.');
    }
    /** Display the authenticated employee's own attendance calendar. */
    public function myAttendance(Request $request): View
    {
        return $this->calendar($request, (int) auth()->id());
    }

    /** Display an authorized employee attendance calendar. */
    public function employeeAttendance(Request $request, int $employeeId): View
    {
        return $this->calendar($request, $employeeId);
    }

    /** Resolve an employee only after attendance access is authorized. */
    protected function authorizedEmployee(int $employeeId): mixed
    {
        $authenticatedId = (int) auth()->id();
        abort_unless($authenticatedId === $employeeId || $this->canManageAttendance(), 403);

        return $this->userService->getEmployeeProfile($employeeId);
    }

    /** Determine whether the authenticated user can manage employee attendance. */
    protected function canManageAttendance(): bool
    {
        return $this->rolePermissionService->userHasAnyRole((int) auth()->id(), ['Admin', 'HR']);
    }
}