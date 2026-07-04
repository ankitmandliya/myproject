<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\CompanySettingServiceInterface;
use App\Contracts\DashboardServiceInterface;
use App\Contracts\LeaveServiceInterface;
use App\Contracts\SalaryServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveApply;
use App\Models\SalarySlip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

/**
 * Lightweight aggregator for HRMS dashboard data.
 */
class DashboardService implements DashboardServiceInterface
{
    /** Create a new dashboard service instance. */
    public function __construct(
        protected UserServiceInterface $userService,
        protected AttendanceServiceInterface $attendanceService,
        protected LeaveServiceInterface $leaveService,
        protected SalaryServiceInterface $salaryService,
        protected CompanySettingServiceInterface $companySettingService,
        protected Attendance $attendance,
        protected LeaveApply $leaveApply,
        protected SalarySlip $salarySlip,
        protected Holiday $holiday,
        protected User $user
    ) {
    }

    /** Get complete dashboard summary. */
    public function getDashboardSummary(): array
    {
        return [
            'employees' => $this->getEmployeeStatistics(),
            'attendance' => $this->getAttendanceStatistics(),
            'leave' => $this->getLeaveStatistics(),
            'salary' => $this->getSalaryStatistics(),
            'company' => $this->getCompanyStatistics(),
        ];
    }

    /** Get employee statistics. */
    public function getEmployeeStatistics(): array
    {
        $activeEmployees = $this->userService->getActiveUsers();
        $inactiveEmployees = $this->userService->getInactiveUsers();

        return [
            'total_employees' => $activeEmployees->count() + $inactiveEmployees->count(),
            'active_employees' => $activeEmployees->count(),
            'inactive_employees' => $inactiveEmployees->count(),
        ];
    }

    /** Get attendance statistics for today. */
    public function getAttendanceStatistics(): array
    {
        $records = $this->attendance
            ->with('user.userDetail')
            ->whereDate('attendance_date', Carbon::today()->toDateString())
            ->get();

        return [
            'present' => $records->where('status', 'Present')->count(),
            'absent' => $records->where('status', 'Absent')->count(),
            'late' => $records->filter(fn (Attendance $attendance): bool => (int) $attendance->late_minutes > 0)->count(),
            'half_day' => $records->filter(fn (Attendance $attendance): bool => (bool) $attendance->half_day)->count(),
        ];
    }

    /** Get leave statistics. */
    public function getLeaveStatistics(): array
    {
        $pending = $this->leaveService->getPendingLeaves()->count();
        $approved = $this->leaveService->getApprovedLeaves()->count();
        $rejected = $this->leaveService->getRejectedLeaves()->count();

        return [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'total' => $pending + $approved + $rejected,
        ];
    }

    /** Get salary statistics for the current month. */
    public function getSalaryStatistics(): array
    {
        $payroll = $this->salaryService->getMonthlyPayroll((int) now()->month, (int) now()->year);
        $generated = $payroll->count();
        $activeEmployees = $this->userService->getActiveUsers()->count();

        return [
            'generated' => $generated,
            'pending' => max(0, $activeEmployees - $generated),
            'total_payroll' => round((float) $payroll->sum('net_salary'), 2),
        ];
    }

    /** Get company statistics. */
    public function getCompanyStatistics(): array
    {
        return [
            'office_start_time' => $this->companySettingService->getOfficeStartTime(),
            'office_end_time' => $this->companySettingService->getOfficeEndTime(),
            'weekly_off' => $this->companySettingService->getWeeklyOff(),
            'salary_date' => $this->companySettingService->getSalaryDate(),
        ];
    }

    /** Get recent leave applications. */
    public function getRecentLeaves(int $limit = 10): Collection
    {
        return $this->leaveApply
            ->with(['user.userDetail', 'leaveType'])
            ->latest('created_at')
            ->limit($this->normalizeLimit($limit))
            ->get();
    }

    /** Get recent attendance records. */
    public function getRecentAttendance(int $limit = 10): Collection
    {
        return $this->attendance
            ->with('user.userDetail')
            ->latest('attendance_date')
            ->latest('created_at')
            ->limit($this->normalizeLimit($limit))
            ->get();
    }

    /** Get recent salary slips. */
    public function getRecentSalarySlips(int $limit = 10): Collection
    {
        return $this->salarySlip
            ->with('user.userDetail')
            ->latest('generated_at')
            ->latest('created_at')
            ->limit($this->normalizeLimit($limit))
            ->get();
    }

    /** Get recently added employees. */
    public function getRecentEmployees(int $limit = 10): Collection
    {
        return $this->user
            ->with(['userDetail', 'roles'])
            ->latest('created_at')
            ->limit($this->normalizeLimit($limit))
            ->get();
    }

    /** Get upcoming holidays. */
    public function getUpcomingHolidays(int $limit = 10): Collection
    {
        return $this->holiday
            ->newQueryWithoutScopes()
            ->where('status', 1)
            ->whereDate('to_date', '>=', Carbon::today()->toDateString())
            ->orderBy('from_date')
            ->limit($this->normalizeLimit($limit))
            ->get();
    }

    /** Get monthly attendance chart data. */
    public function getMonthlyAttendanceChart(int $month, int $year): array
    {
        $this->validateMonthYear($month, $year);
        $records = $this->attendanceService->getAttendanceReport($month, $year);

        return [
            'Present' => $records->where('status', 'Present')->count(),
            'Absent' => $records->where('status', 'Absent')->count(),
            'Late' => $records->filter(fn (Attendance $attendance): bool => (int) $attendance->late_minutes > 0)->count(),
            'Half Day' => $records->filter(fn (Attendance $attendance): bool => (bool) $attendance->half_day)->count(),
        ];
    }

    /** Get monthly leave chart data. */
    public function getMonthlyLeaveChart(int $month, int $year): array
    {
        $this->validateMonthYear($month, $year);
        $records = $this->leaveService->getLeavesByMonth($month, $year);

        return [
            'Pending' => $records->where('status', 'Pending')->count(),
            'Approved' => $records->where('status', 'Approved')->count(),
            'Rejected' => $records->where('status', 'Rejected')->count(),
        ];
    }

    /** Get monthly salary chart data. */
    public function getMonthlySalaryChart(int $month, int $year): array
    {
        $this->validateMonthYear($month, $year);
        $payroll = $this->salaryService->getMonthlyPayroll($month, $year);
        $generated = $payroll->count();
        $activeEmployees = $this->userService->getActiveUsers()->count();

        return [
            'Generated' => $generated,
            'Pending' => max(0, $activeEmployees - $generated),
            'Total Payroll' => round((float) $payroll->sum('net_salary'), 2),
        ];
    }

    /** Get system overview. */
    public function getSystemOverview(): array
    {
        return $this->getDashboardSummary();
    }

    /** Get all dashboard widget data. */
    public function getDashboardWidgets(): array
    {
        return [
            'summary' => $this->getDashboardSummary(),
            'recent_leaves' => $this->getRecentLeaves(10),
            'recent_attendance' => $this->getRecentAttendance(10),
            'recent_salary_slips' => $this->getRecentSalarySlips(10),
            'recent_employees' => $this->getRecentEmployees(10),
            'upcoming_holidays' => $this->getUpcomingHolidays(5),
            'attendance_chart' => $this->getMonthlyAttendanceChart((int) now()->month, (int) now()->year),
            'leave_chart' => $this->getMonthlyLeaveChart((int) now()->month, (int) now()->year),
            'salary_chart' => $this->getMonthlySalaryChart((int) now()->month, (int) now()->year),
        ];
    }

    /** Normalize dashboard record limit. */
    protected function normalizeLimit(int $limit): int
    {
        if ($limit <= 0) {
            throw new InvalidArgumentException('Limit must be a positive integer.');
        }

        return min($limit, 100);
    }

    /** Validate dashboard month and year inputs. */
    protected function validateMonthYear(int $month, int $year): void
    {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('Month must be between 1 and 12.');
        }

        if ($year < 2000 || $year > 2100) {
            throw new InvalidArgumentException('Year must be between 2000 and 2100.');
        }
    }
}
