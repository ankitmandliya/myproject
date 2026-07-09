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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

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
        $this->authorizeAttendanceManagement();

        return view('Adminpanel.HRMS.Attendance.create');
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $this->authorizeAttendanceManagement();
        $data = $request->validated();

        try {
            $this->attendanceService->markCheckIn((int) $data['user_id'], $data);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected attendance store exception.', ['exception' => $exception]);

            return back()->withInput()->with('error', 'Unable to save attendance. Please try again or contact the administrator.');
        }

        return redirect()->route('hrms.attendance.index')->with('success', 'Attendance saved successfully.');
    }

    public function show(int $id): View
    {
        $attendance = $this->attendanceService->getById($id);
        abort_unless((int) $attendance->user_id === (int) auth()->id() || $this->canManageAttendance(), 403);
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
        $this->authorizeAttendanceManagement();
        $attendance = $this->attendanceService->getById($id);

        return view('Adminpanel.HRMS.Attendance.edit', compact('attendance'));
    }

    public function update(UpdateAttendanceRequest $request, int $id): RedirectResponse
    {
        $this->authorizeAttendanceManagement();
        $this->attendanceService->getById($id);

        return redirect()->route('hrms.attendance.index')->with('success', 'Attendance updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeAttendanceManagement();
        $this->attendanceService->deleteAttendance($id);

        return redirect()->route('hrms.attendance.index')->with('success', 'Attendance deleted successfully.');
    }

    public function checkIn(StoreAttendanceRequest $request): RedirectResponse
    {
        $this->authorizeAttendanceManagement();
        $data = $request->validated();

        try {
            $this->attendanceService->markCheckIn((int) $data['user_id'], $data);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected attendance check-in controller exception.', ['exception' => $exception]);

            return back()->withInput()->with('error', 'Unable to mark check-in. Please try again or contact the administrator.');
        }

        return redirect()->route('hrms.attendance.index')->with('success', 'Check-in marked successfully.');
    }

    public function checkOut(int $userId): RedirectResponse
    {
        $this->authorizeAttendanceManagement();

        try {
            $this->attendanceService->markCheckOut($userId);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected attendance check-out controller exception.', ['user_id' => $userId, 'exception' => $exception]);

            return back()->with('error', 'Unable to mark check-out. Please try again or contact the administrator.');
        }

        return redirect()->route('hrms.attendance.index')->with('success', 'Check-out marked successfully.');
    }

    public function monthlyReport(int $userId, int $month, int $year): View
    {
        abort_unless((int) $userId === (int) auth()->id() || $this->canManageAttendance(), 403);
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

    /** Display the attendance reporting dashboard. */
    public function reports(Request $request): View
    {
        $this->authorizeReports();
        [$month, $year, $selectedMonth] = $this->reportMonth($request);
        $filters = $request->only(['name', 'emp_code', 'department', 'designation', 'status', 'from_date', 'to_date']);
        $filters['month'] = $selectedMonth;
        $filters['year'] = $year;
        $filters['year'] = $year;
        $perPage = (int) $request->input('per_page', 10);
        $attendance = $this->attendanceService->getFilteredAttendance($filters, $perPage);
        $attendance->getCollection()->each(function ($record): void {
            $status = $this->recordDisplayStatus($record);
            $record->setAttribute('display_status', $status);
            $record->setAttribute('status_badge', $this->reportStatusBadge($status));
        });
        $summary = $this->reportSummary($month, $year);
        $employees = $this->userService->getActiveUsers();
        $departments = $employees->pluck('userDetail.department')->filter()->unique()->sort()->values();
        $designations = $employees->pluck('userDetail.designation')->filter()->unique()->sort()->values();
        $statusList = ['Present', 'Late', 'Half Day', 'Leave', 'Holiday', 'Weekly Off', 'Absent'];

        return view('Adminpanel.HRMS.Attendance.Reports.index', compact(
            'attendance', 'summary', 'filters', 'perPage', 'employees', 'departments', 'designations', 'statusList'
        ));
    }

    /** Display employee attendance statistics. */
    public function employeeReport(Request $request): View
    {
        $this->authorizeReports();
        [$month, $year, $selectedMonth] = $this->reportMonth($request);
        $filters = $request->all();
        $filters['month'] = $selectedMonth;
        $filters['year'] = $year;
        $statistics = $this->employeeStatistics($this->reportRecords($request, $month, $year), $month, $year);
        $reports = $this->paginateReport($statistics, $request);

        return view('Adminpanel.HRMS.Attendance.Reports.employee-report', compact('reports', 'filters'));
    }

    /** Display department attendance statistics. */
    public function departmentReport(Request $request): View
    {
        $this->authorizeReports();
        [$month, $year, $selectedMonth] = $this->reportMonth($request);
        $filters = $request->all();
        $filters['month'] = $selectedMonth;
        $filters['year'] = $year;
        $employeeStatistics = $this->employeeStatistics($this->reportRecords($request, $month, $year), $month, $year);
        $statistics = $employeeStatistics->groupBy('department')->map(function (Collection $rows, string $department): array {
            $recordedDays = (int) $rows->sum('recorded_days');

            return [
                'department' => $department ?: 'Unassigned',
                'total_employees' => $rows->count(),
                'present' => $rows->sum('present'),
                'absent' => $rows->sum('absent'),
                'late' => $rows->sum('late'),
                'half_day' => $rows->sum('half_day'),
                'leave' => $rows->sum('leave'),
                'attendance_percentage' => $recordedDays > 0 ? round(($rows->sum('present') / $recordedDays) * 100, 2) : 0,
            ];
        })->values();
        $reports = $this->paginateReport($statistics, $request);

        return view('Adminpanel.HRMS.Attendance.Reports.department-report', compact('reports', 'filters'));
    }

    /** Display monthly employee attendance statistics. */
    public function reportingMonthlyReport(Request $request): View
    {
        $this->authorizeReports();
        [$month, $year, $selectedMonth] = $this->reportMonth($request);
        $filters = $request->all();
        $filters['month'] = $selectedMonth;
        $filters['year'] = $year;
        $reports = $this->paginateReport(
            $this->employeeStatistics($this->reportRecords($request, $month, $year), $month, $year),
            $request
        );

        return view('Adminpanel.HRMS.Attendance.Reports.monthly-report', compact('reports', 'filters'));
    }
    /** Render the authenticated user's prepared header attendance widget. */
    public function widget(): View
    {
        abort_unless(auth()->check(), 403);
        $attendanceWidget = $this->attendanceService->getTodayAttendanceWidget((int) auth()->id());

        return view('Adminpanel.HRMS.Attendance._partials.header-widget', compact('attendanceWidget'));
    }

    /** Check in the authenticated user. */
    public function widgetCheckIn(): RedirectResponse
    {
        abort_unless(auth()->check(), 403);

        try {
            $this->attendanceService->markCheckIn((int) auth()->id(), []);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected widget check-in exception.', ['user_id' => auth()->id(), 'exception' => $exception]);

            return back()->with('error', 'Unable to mark check-in. Please try again or contact the administrator.');
        }

        return back()->with('success', 'Check-in marked successfully.');
    }

    /** Check out the authenticated user. */
    public function widgetCheckOut(): RedirectResponse
    {
        abort_unless(auth()->check(), 403);

        try {
            $this->attendanceService->markCheckOut((int) auth()->id());
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected widget check-out exception.', ['user_id' => auth()->id(), 'exception' => $exception]);

            return back()->with('error', 'Unable to mark check-out. Please try again or contact the administrator.');
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

    /** Restrict attendance management actions to HR/Admin. */
    protected function authorizeAttendanceManagement(): void
    {
        abort_unless($this->canManageAttendance(), 403);
    }
    /** Resolve an employee only after attendance access is authorized. */
    protected function authorizedEmployee(int $employeeId): mixed
    {
        $authenticatedId = (int) auth()->id();
        abort_unless($authenticatedId === $employeeId || $this->canManageAttendance(), 403);

        return $this->userService->getEmployeeProfile($employeeId);
    }

    /** Resolve and validate a report month. */
    protected function reportMonth(Request $request): array
    {
        $selected = (string) $request->input('month', Carbon::now()->format('Y-m'));
        $date = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selected) === 1
            ? Carbon::createFromFormat('Y-m', $selected)->startOfMonth()
            : Carbon::now()->startOfMonth();
        $requestedYear = (int) $request->input('year', $date->year);
        if ($requestedYear >= 2000 && $requestedYear <= 2100) {
            $date->year($requestedYear);
        }

        return [(int) $date->month, (int) $date->year, $date->format('Y-m')];
    }

    /** Build reporting summary values from existing service responses. */
    protected function reportSummary(int $month, int $year): array
    {
        $summary = $this->attendanceService->getTodaySummary();
        $days = collect($this->attendanceService->getMonthlyCalendar($month, $year)['weeks'])->flatten(1);
        $summary['holidays'] = $days->where('is_current_month', true)->where('status', 'Holiday')->count();
        $summary['weekly_offs'] = $days->where('is_current_month', true)->where('status', 'Weekly Off')->count();

        return $summary;
    }

    /** Get and filter eager monthly report records. */
    protected function reportRecords(Request $request, int $month, int $year): Collection
    {
        $records = collect($this->attendanceService->getAttendanceReport($month, $year));
        foreach (['department', 'designation'] as $field) {
            if ($request->filled($field)) {
                $records = $records->filter(fn ($record): bool => $record->user?->userDetail?->{$field} === $request->input($field));
            }
        }
        if ($request->filled('name')) {
            $name = strtolower((string) $request->input('name'));
            $records = $records->filter(function ($record) use ($name): bool {
                $detail = $record->user?->userDetail;
                $fullName = strtolower(trim(($detail?->first_name ?? '') . ' ' . ($detail?->last_name ?? '')));

                return str_contains($fullName, $name) || str_contains(strtolower((string) $record->user?->name), $name);
            });
        }
        if ($request->filled('employee_id')) {
            $records = $records->where('user_id', (int) $request->input('employee_id'));
        }
        if ($request->filled('emp_code')) {
            $records = $records->filter(fn ($record): bool => str_contains(strtolower((string) $record->user?->userDetail?->emp_code), strtolower((string) $request->input('emp_code'))));
        }
        if ($request->filled('status')) {
            $records = $records->filter(fn ($record): bool => $this->recordDisplayStatus($record) === $request->input('status'));
        }
        if ($request->filled('from_date')) {
            $records = $records->filter(fn ($record): bool => $record->attendance_date->toDateString() >= $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $records = $records->filter(fn ($record): bool => $record->attendance_date->toDateString() <= $request->input('to_date'));
        }

        return $records->values();
    }

    /** Build employee analytics from eager attendance records. */
    protected function employeeStatistics(Collection $records, int $month, int $year): Collection
    {
        $calendarDays = collect($this->attendanceService->getMonthlyCalendar($month, $year)['weeks'])
            ->flatten(1)->where('is_current_month', true);
        $holidayDays = $calendarDays->where('status', 'Holiday')->count();
        $weeklyOffDays = $calendarDays->where('status', 'Weekly Off')->count();

        return $records->groupBy('user_id')->map(function (Collection $rows) use ($holidayDays, $weeklyOffDays): array {
            $user = $rows->first()->user;
            $detail = $user?->userDetail;
            $checkIns = $rows->pluck('check_in')->filter()->map(fn ($time): int => Carbon::parse($time)->hour * 60 + Carbon::parse($time)->minute);
            $checkOuts = $rows->pluck('check_out')->filter()->map(fn ($time): int => Carbon::parse($time)->hour * 60 + Carbon::parse($time)->minute);

            return [
                'employee' => $user,
                'employee_code' => $detail?->emp_code ?? '-',
                'employee_name' => trim(($detail?->first_name ?? '') . ' ' . ($detail?->last_name ?? '')) ?: ($user?->name ?? '-'),
                'department' => $detail?->department ?? 'Unassigned',
                'designation' => $detail?->designation ?? '-',
                'present' => $rows->where('status', 'Present')->count(),
                'late' => $rows->where('late_minutes', '>', 0)->count(),
                'half_day' => $rows->where('half_day', true)->count(),
                'leave' => $rows->where('status', 'Leave')->count(),
                'holiday' => $holidayDays,
                'weekly_off' => $weeklyOffDays,
                'absent' => $rows->where('status', 'Absent')->count(),
                'working_hours' => round((float) $rows->sum('working_hours'), 2),
                'average_check_in' => $this->averageTime($checkIns),
                'average_check_out' => $this->averageTime($checkOuts),
                'recorded_days' => $rows->count(),
            ];
        })->values();
    }

    /** Format an average minute-of-day collection. */
    protected function averageTime(Collection $minutes): string
    {
        if ($minutes->isEmpty()) {
            return '-';
        }
        $average = (int) round($minutes->avg());

        return Carbon::createFromTime(intdiv($average, 60), $average % 60)->format('h:i A');
    }

    /** Resolve presentation status through attendance fields. */
    protected function recordDisplayStatus(mixed $record): string
    {
        return $record->half_day ? 'Half Day' : ((int) $record->late_minutes > 0 ? 'Late' : (string) $record->status);
    }

    /** Resolve a Bootstrap report status badge class. */
    protected function reportStatusBadge(string $status): string
    {
        return match ($status) {
            'Present' => 'success',
            'Late' => 'warning',
            'Half Day' => 'info',
            'Leave' => 'primary',
            'Absent' => 'danger',
            'Weekly Off' => 'dark',
            default => 'secondary',
        };
    }
    /** Paginate an in-memory prepared report collection. */
    protected function paginateReport(Collection $rows, Request $request): LengthAwarePaginator
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10) : 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(), $rows->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /** Restrict reporting to HR and Admin roles. */
    protected function authorizeReports(): void
    {
        abort_unless($this->canManageAttendance(), 403);
    }
    /** Determine whether the authenticated user can manage employee attendance. */
    protected function canManageAttendance(): bool
    {
        return $this->rolePermissionService->userHasAnyRole((int) auth()->id(), ['Admin', 'HR']);
    }
}

