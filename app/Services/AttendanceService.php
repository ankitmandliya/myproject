<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\CompanySettingServiceInterface;
use App\Contracts\HolidayServiceInterface;
use App\Contracts\LeavePolicyServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Models\Attendance;
use App\Models\LeaveApply;
use App\Models\User;
use App\Services\LeaveApprovalService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service for HRMS attendance management.
 */
class AttendanceService implements AttendanceServiceInterface
{
    /** Create a new attendance service instance. */
    public function __construct(
        protected Attendance $attendance,
        protected UserServiceInterface $userService,
        protected CompanySettingServiceInterface $companySettingService,
        protected HolidayServiceInterface $holidayService,
        protected LeavePolicyServiceInterface $leavePolicyService,
        protected LeaveApply $leaveApply
    ) {
    }


    /** Resolve the authoritative attendance status for a user and date. */
    public function getAttendanceStatus(int $userId, Carbon $date): array
    {
        $source = $this->getAttendanceSource($userId, $date);
        $leave = $source['leave'];
        $attendance = $source['attendance'];

        if ($source['holiday'] !== null) {
            return $this->statusPayload('Holiday', 'Holiday', 'secondary', $source);
        }

        if ($leave instanceof LeaveApply) {
            return $this->leaveStatusPayload($leave, $attendance, $source);
        }

        if ($source['is_weekly_off']) {
            return $this->statusPayload('Weekly Off', 'Weekly Off', 'dark', $source);
        }

        if ($attendance instanceof Attendance) {
            $display = $this->displayStatus($attendance);
            return $this->statusPayload($display, $display, $this->statusBadge($display), $source);
        }

        return $this->statusPayload('Absent', 'Absent', 'danger', $source);
    }

    /** Resolve raw status sources in priority order without recalculating leave. */
    public function getAttendanceSource(int $userId, Carbon $date): array
    {
        $this->validateUserExists($userId);
        $date = $date->copy()->startOfDay();
        $attendance = $this->attendance
            ->with(['user.userDetail', 'user.roles'])
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $date->toDateString())
            ->first();
        $holiday = $this->holidayService->active()->first(
            fn ($item): bool => $date->betweenIncluded($item->from_date, $item->to_date)
        );
        $leave = $this->getLeaveStatus($userId, $date);

        return [
            'date' => $date,
            'attendance' => $attendance,
            'holiday' => $holiday,
            'leave' => $leave,
            'is_weekly_off' => $holiday === null && $this->companySettingService->isWeeklyOff($date),
        ];
    }

    /** Get an approved leave covering the supplied date. */
    public function getLeaveStatus(int $userId, Carbon $date): ?object
    {
        return $this->leaveApply
            ->with(['leaveType', 'approvedBy', 'user.userDetail'])
            ->where('user_id', $userId)
            ->where('status', LeaveApprovalService::STATUS_APPROVED)
            ->whereDate('from_date', '<=', $date->toDateString())
            ->whereDate('to_date', '>=', $date->toDateString())
            ->latest('approved_at')
            ->first();
    }

    /** Resolve Bootstrap badge metadata for a leave type. */
    public function getLeaveBadge(?object $leave): array
    {
        if (! $leave instanceof LeaveApply) {
            return ['code' => 'LV', 'class' => 'bg-primary', 'label' => 'Leave'];
        }

        $code = strtoupper(trim((string) ($leave->leaveType?->leave_code ?? '')));
        $code = $code !== '' ? $code : strtoupper(substr((string) ($leave->leaveType?->leave_name ?? 'LV'), 0, 2));
        $isLwp = $this->leavePolicyService->isLeaveWithoutPay((int) $leave->leave_type_id);

        if ($isLwp) {
            return ['code' => 'LWP', 'class' => 'bg-dark', 'label' => 'Leave Without Pay'];
        }

        $class = match ($code) {
            'CL' => 'bg-primary',
            'SL' => 'bg-success',
            'EL', 'EARN', 'EARNED' => 'bg-purple text-white',
            'ML' => 'bg-danger',
            'PL' => 'bg-info text-dark',
            default => 'bg-primary',
        };

        return ['code' => $code, 'class' => $class, 'label' => $leave->leaveType?->leave_name ?? 'Leave'];
    }

    /** Determine whether attendance marking is allowed for a date. */
    public function isAttendanceAllowed(int $userId, Carbon $date): bool
    {
        return $this->getAttendanceStatus($userId, $date)['attendance_allowed'];
    }

    /** Determine whether check-in is allowed. */
    public function canCheckIn(int $userId, ?Carbon $date = null): bool
    {
        $date = ($date ?? Carbon::today())->copy()->startOfDay();
        $source = $this->getAttendanceSource($userId, $date);

        if ($source['holiday'] !== null || $source['is_weekly_off']) {
            return false;
        }

        $leave = $source['leave'];
        if ($leave instanceof LeaveApply && ! $this->isHalfDayLeave($leave)) {
            return false;
        }

        return ! $source['attendance'] instanceof Attendance;
    }

    /** Determine whether check-out is allowed. */
    public function canCheckOut(int $userId, ?Carbon $date = null): bool
    {
        $date = ($date ?? Carbon::today())->copy()->startOfDay();
        $source = $this->getAttendanceSource($userId, $date);
        $attendance = $source['attendance'];
        $leave = $source['leave'];

        if ($source['holiday'] !== null || $source['is_weekly_off']) {
            return false;
        }

        if ($leave instanceof LeaveApply && ! $this->isHalfDayLeave($leave)) {
            return false;
        }

        return $attendance instanceof Attendance && $attendance->check_in !== null && $attendance->check_out === null;
    }

    /** Get paginated attendance records. */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->attendance
            ->with(['user.userDetail', 'user.roles'])
            ->latest('attendance_date')
            ->paginate($perPage);
    }


    /** Get filtered paginated attendance records. */
    public function getFilteredAttendance(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query = $this->attendance->with(['user.userDetail', 'user.roles']);

        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            $query->whereHas('user', function ($userQuery) use ($name): void {
                $userQuery->where('name', 'like', "%{$name}%")
                    ->orWhere('email', 'like', "%{$name}%")
                    ->orWhereHas('userDetail', function ($detailQuery) use ($name): void {
                        $detailQuery->where('first_name', 'like', "%{$name}%")
                            ->orWhere('last_name', 'like', "%{$name}%");
                    });
            });
        }

        $employeeCode = trim((string) ($filters['emp_code'] ?? ''));
        if ($employeeCode !== '') {
            $query->whereHas('user.userDetail', fn ($detailQuery) => $detailQuery->where('emp_code', 'like', "%{$employeeCode}%"));
        }

        $department = trim((string) ($filters['department'] ?? ''));
        if ($department !== '') {
            $query->whereHas('user.userDetail', fn ($detailQuery) => $detailQuery->where('department', 'like', "%{$department}%"));
        }

        $designation = trim((string) ($filters['designation'] ?? ''));
        if ($designation !== '') {
            $query->whereHas('user.userDetail', fn ($detailQuery) => $detailQuery->where('designation', 'like', "%{$designation}%"));
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            if ($status === 'Late') {
                $query->where('late_minutes', '>', 0);
            } elseif ($status === 'Half Day') {
                $query->where('half_day', true);
            } else {
                $query->where('status', $status);
            }
        }

        $fromDate = trim((string) ($filters['from_date'] ?? ''));
        if ($fromDate !== '') {
            $query->whereDate('attendance_date', '>=', $fromDate);
        }

        $toDate = trim((string) ($filters['to_date'] ?? ''));
        if ($toDate !== '') {
            $query->whereDate('attendance_date', '<=', $toDate);
        }

        return $query
            ->latest('attendance_date')
            ->latest('created_at')
            ->paginate($perPage);
    }

    /** Get today's attendance summary. */
    public function getTodaySummary(): array
    {
        $today = Carbon::today();
        $activeEmployees = $this->userService->getActiveUsers();
        $summary = [
            'total_employees' => $activeEmployees->count() + $this->userService->getInactiveUsers()->count(),
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'half_day' => 0,
            'leave' => 0,
            'lwp' => 0,
            'holiday' => 0,
            'weekly_off' => 0,
        ];

        foreach ($activeEmployees as $employee) {
            $status = $this->getAttendanceStatus((int) $employee->id, $today)['status'];
            match ($status) {
                'Present' => $summary['present']++,
                'Late' => $summary['late']++,
                'Half Day' => $summary['half_day']++,
                'Leave' => $summary['leave']++,
                'LWP' => $summary['lwp']++,
                'Holiday' => $summary['holiday']++,
                'Weekly Off' => $summary['weekly_off']++,
                default => $summary['absent']++,
            };
        }

        return $summary;
    }

    /** Get a prepared, Monday-aligned monthly attendance calendar. */
    public function getMonthlyCalendar(int $month, int $year, ?int $userId = null): array
    {
        $this->validateMonthYear($month, $year);
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];
        $week = [];

        for ($date = $gridStart->copy(); $date->lte($gridEnd); $date->addDay()) {
            $status = $userId !== null
                ? $this->getAttendanceStatus($userId, $date)
                : $this->aggregateDayStatus($date);
            $attendance = $status['attendance'] ?? null;
            $holiday = $status['holiday'] ?? null;
            $leaveBadge = $status['leave_badge'] ?? null;

            $week[] = [
                'date' => $date->toDateString(),
                'day' => $date->day,
                'weekday' => $date->format('D'),
                'is_current_month' => $date->month === $month,
                'is_today' => $date->isToday(),
                'status' => $status['status'],
                'status_label' => $status['label'],
                'statuses' => $status['statuses'] ?? [$status['status']],
                'status_badge' => $status['badge'],
                'leave_code' => $leaveBadge['code'] ?? null,
                'leave_badge_class' => $leaveBadge['class'] ?? null,
                'leave_type' => $status['leave_type'] ?? null,
                'leave_reason' => $status['leave_reason'] ?? null,
                'approved_by' => $status['approved_by'] ?? null,
                'holiday_name' => $holiday?->name,
                'is_weekend' => ($status['source'] ?? null) === 'weekly_off',
                'check_in' => $attendance?->check_in,
                'check_out' => $attendance?->check_out,
                'working_hours' => $attendance?->working_hours,
                'records_count' => $attendance instanceof Attendance ? 1 : 0,
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        return [
            'month' => $month,
            'year' => $year,
            'label' => $monthStart->format('F Y'),
            'selected_month' => $monthStart->format('Y-m'),
            'previous_month' => $monthStart->copy()->subMonth()->format('Y-m'),
            'next_month' => $monthStart->copy()->addMonth()->format('Y-m'),
            'weekdays' => collect(range(0, 6))->map(
                fn (int $offset): string => $gridStart->copy()->addDays($offset)->format('D')
            )->all(),
            'has_attendance' => collect($weeks)->flatten(1)->whereNotIn('status', ['Absent'])->isNotEmpty(),
            'weeks' => $weeks,
        ];
    }
    /** Auto-finalize previous open attendance records for a user. */
    public function autoFinalizeOpenAttendance(int $userId): int
    {
        $this->validateActiveUser($userId);
        $today = Carbon::today()->toDateString();
        $finalized = 0;

        DB::transaction(function () use ($userId, $today, &$finalized): void {
            $records = $this->attendance
                ->where('user_id', $userId)
                ->whereDate('attendance_date', '<', $today)
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->orderBy('attendance_date')
                ->lockForUpdate()
                ->get();

            foreach ($records as $attendance) {
                $officeClose = $this->timeOnAttendanceDate($attendance, $this->companySettingService->getOfficeEndTime());
                $checkIn = $this->attendanceDateTime($attendance, 'check_in');
                $checkOut = $officeClose->greaterThan($checkIn) ? $officeClose : $checkIn->copy()->addMinute();

                $attendance->update(['check_out' => $checkOut->format('H:i:s')]);
                $this->calculateLateMinutes($attendance->id);
                $this->calculateWorkingHours($attendance->id);
                $this->detectHalfDay($attendance->id);
                $this->updateAttendanceStatus($attendance->id);
                $finalized++;
            }
        });

        return $finalized;
    }
    /** Get the complete authenticated-user header attendance view model. */
    public function getTodayAttendanceWidget(int $userId): array
    {
        $this->autoFinalizeOpenAttendance($userId);
        $employee = $this->userService->getEmployeeProfile($userId);
        $today = Carbon::today();
        $now = Carbon::now();
        $source = $this->getAttendanceSource($userId, $today);
        $status = $this->getAttendanceStatus($userId, $today);
        $attendance = $source['attendance'];
        $holiday = $source['holiday'];
        $leave = $source['leave'];
        $officeStart = $this->companySettingService->getOfficeStartTime();
        $officeEnd = $this->companySettingService->getOfficeEndTime();
        $lateThreshold = $this->companySettingService->getLateThreshold();
        $halfDayThreshold = $this->companySettingService->getHalfDayThreshold();
        $completed = $attendance?->check_out !== null;
        $canCheckIn = $this->canCheckIn($userId, $today);
        $canCheckOut = $this->canCheckOut($userId, $today);
        $widgetStatus = $leave instanceof LeaveApply && ! (bool) ($status['is_half_leave'] ?? false)
            ? $status['label']
            : ($holiday !== null || $source['is_weekly_off'] ? $status['status'] : ($completed ? 'Attendance Completed' : ($canCheckOut ? 'ON' : 'OFF')));
        $detail = $employee->userDetail;

        return [
            'status' => $widgetStatus,
            'statusBadge' => $status['badge'],
            'canCheckIn' => $canCheckIn,
            'canCheckOut' => $canCheckOut,
            'attendanceCompleted' => $completed,
            'checkInTime' => $attendance?->check_in ? Carbon::parse($attendance->check_in)->format('h:i A') : null,
            'checkOutTime' => $attendance?->check_out ? Carbon::parse($attendance->check_out)->format('h:i A') : null,
            'officeStartTime' => Carbon::parse($officeStart)->format('h:i A'),
            'officeEndTime' => Carbon::parse($officeEnd)->format('h:i A'),
            'lateThreshold' => Carbon::parse($officeStart)->addMinutes($lateThreshold)->format('h:i A'),
            'halfDayThreshold' => Carbon::parse($officeStart)->addMinutes($halfDayThreshold)->format('h:i A'),
            'weeklyOff' => $this->companySettingService->getWeeklyOff(),
            'isWeeklyOff' => (bool) $source['is_weekly_off'],
            'isCompanyHoliday' => $holiday !== null,
            'holidayName' => $holiday?->name,
            'holidayDate' => $holiday?->from_date?->format('d M Y'),
            'isApprovedLeave' => $leave instanceof LeaveApply,
            'leaveType' => $status['leave_type'] ?? null,
            'leaveStatusLabel' => $status['label'],
            'leaveBadge' => $status['leave_badge'] ?? null,
            'leaveReason' => $status['leave_reason'] ?? null,
            'approvedBy' => $status['approved_by'] ?? null,
            'attendanceMessage' => $leave instanceof LeaveApply && ! (bool) ($status['is_half_leave'] ?? false) ? 'Attendance not required today' : null,
            'officeOpen' => $status['attendance_allowed'],
            'todayDate' => $today->format('d M Y'),
            'currentTime' => $now->format('h:i A T'),
            'employeeName' => trim(($detail?->first_name ?? '') . ' ' . ($detail?->last_name ?? '')) ?: $employee->name,
            'employeeCode' => $detail?->emp_code,
            'workingHours' => $attendance?->working_hours,
        ];
    }
    /** Get an attendance record by ID. */
    public function getById(int $id): Attendance
    {
        return $this->findAttendance($id);
    }

    /** Mark employee check-in for today. */
    public function markCheckIn(int $userId, array $data): Attendance
    {
        $this->validateActiveUser($userId);
        $this->autoFinalizeOpenAttendance($userId);
        $attendanceDate = $this->resolveAttendanceDate($data['attendance_date'] ?? null);
        $this->validateAttendanceContext($userId, $attendanceDate);

        try {
            return DB::transaction(function () use ($userId, $data, $attendanceDate): Attendance {
                $existingAttendance = $this->attendance
                    ->where('user_id', $userId)
                    ->whereDate('attendance_date', $attendanceDate->toDateString())
                    ->lockForUpdate()
                    ->first();

                if ($existingAttendance instanceof Attendance) {
                    throw new RuntimeException('Attendance already exists for this employee and date.');
                }

                $checkIn = $this->resolveTime($data['check_in'] ?? null);

                $attendance = $this->attendance->create([
                    'user_id' => $userId,
                    'attendance_date' => $attendanceDate->toDateString(),
                    'check_in' => $checkIn->format('H:i:s'),
                    'status' => 'Present',
                    'working_hours' => 0,
                    'late_minutes' => 0,
                    'half_day' => false,
                ]);

                $this->calculateLateMinutes($attendance->id);

                return $this->findAttendance($attendance->id);
            });
        } catch (QueryException $exception) {
            Log::warning('Duplicate attendance check-in blocked.', [
                'user_id' => $userId,
                'attendance_date' => $attendanceDate->toDateString(),
                'exception' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Attendance already exists for this employee and date.', 0, $exception);
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Unexpected attendance check-in exception.', [
                'user_id' => $userId,
                'attendance_date' => $attendanceDate->toDateString(),
                'exception' => $exception,
            ]);

            throw new RuntimeException('Unable to mark attendance. Please try again or contact the administrator.', 0, $exception);
        }
    }

    /** Mark employee checkout for today. */
    public function markCheckOut(int $userId): Attendance
    {
        $this->validateActiveUser($userId);
        $today = Carbon::today();
        $this->validateCompanyAttendanceSettings();
        if (! $this->canCheckOut($userId, $today)) {
            $status = $this->getAttendanceStatus($userId, $today);
            if (($status['leave'] ?? null) instanceof LeaveApply) {
                throw new RuntimeException('You are already on approved leave today.');
            }
            throw new RuntimeException('Check-out is not allowed for today.');
        }

        try {
            return DB::transaction(function () use ($userId, $today): Attendance {
                $attendance = $this->attendance->with(['user.userDetail', 'user.roles'])
                    ->where('user_id', $userId)
                    ->whereDate('attendance_date', $today->toDateString())
                    ->lockForUpdate()
                    ->first();

                if (! $attendance instanceof Attendance) {
                    throw new RuntimeException("Today attendance was not found for user [{$userId}].");
                }

                if ((int) $attendance->user_id !== $userId) {
                    throw new RuntimeException('Attendance belongs to another employee.');
                }

                if ($attendance->check_out !== null) {
                    throw new RuntimeException("User [{$userId}] has already checked out today.");
                }

                if ($attendance->check_in === null) {
                    throw new RuntimeException("User [{$userId}] has not checked in today.");
                }

                $checkOut = Carbon::now();
                $checkIn = $this->attendanceDateTime($attendance, 'check_in');

                if ($checkOut->lessThanOrEqualTo($checkIn)) {
                    throw new RuntimeException('Checkout time must be greater than check-in time.');
                }

                $attendance->update([
                    'check_out' => $checkOut->format('H:i:s'),
                ]);

                $this->calculateWorkingHours($attendance->id);
                $this->detectHalfDay($attendance->id);
                $this->updateAttendanceStatus($attendance->id);

                return $this->findAttendance($attendance->id);
            });
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Unexpected attendance check-out exception.', [
                'user_id' => $userId,
                'attendance_date' => $today->toDateString(),
                'exception' => $exception,
            ]);

            throw new RuntimeException('Unable to mark check-out. Please try again or contact the administrator.', 0, $exception);
        }
    }
    /** Calculate and update working hours for an attendance record. */
    public function calculateWorkingHours(int $attendanceId): float
    {
        $attendance = $this->findAttendance($attendanceId);

        if ($attendance->check_in === null || $attendance->check_out === null) {
            throw new RuntimeException('Check-in and checkout are required to calculate working hours.');
        }

        $checkIn = $this->attendanceDateTime($attendance, 'check_in');
        $checkOut = $this->attendanceDateTime($attendance, 'check_out');

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            throw new RuntimeException('Checkout time must be greater than check-in time.');
        }

        $workingHours = round($checkIn->floatDiffInHours($checkOut), 2);

        $attendance->update(['working_hours' => $workingHours]);

        return $workingHours;
    }

    /** Calculate and update late minutes for an attendance record. */
    public function calculateLateMinutes(int $attendanceId): int
    {
        $attendance = $this->findAttendance($attendanceId);

        if ($attendance->check_in === null) {
            throw new RuntimeException('Check-in is required to calculate late minutes.');
        }

        $checkIn = $this->attendanceDateTime($attendance, 'check_in');
        $officeStart = $this->timeOnAttendanceDate($attendance, $this->companySettingService->getOfficeStartTime());
        $lateAfter = $officeStart->copy()->addMinutes($this->companySettingService->getLateThreshold());

        $lateMinutes = $checkIn->greaterThan($lateAfter)
            ? $lateAfter->diffInMinutes($checkIn)
            : 0;

        $attendance->update(['late_minutes' => $lateMinutes]);

        return $lateMinutes;
    }

    /** Detect and update half-day flag for an attendance record. */
    public function detectHalfDay(int $attendanceId): bool
    {
        $attendance = $this->findAttendance($attendanceId);

        if ($attendance->check_out === null) {
            throw new RuntimeException('Checkout is required to detect half-day attendance.');
        }

        $workingHours = (float) $attendance->working_hours;

        if ($workingHours <= 0) {
            $workingHours = $this->calculateWorkingHours($attendanceId);
        }

        $requiredHours = $this->requiredWorkingHours($attendance);
        $thresholdHours = $this->companySettingService->getHalfDayThreshold() / 60;
        $isHalfDay = $workingHours < max(0, $requiredHours - $thresholdHours);

        $attendance->update(['half_day' => $isHalfDay]);

        return $isHalfDay;
    }

    /** Update attendance status after successful check-in. */
    public function updateAttendanceStatus(int $attendanceId): Attendance
    {
        $attendance = $this->findAttendance($attendanceId);

        if (! in_array($attendance->status, ['Leave', 'Holiday'], true) && $attendance->check_in !== null) {
            $attendance->update(['status' => 'Present']);
        }

        return $this->findAttendance($attendanceId);
    }

    /** Get today's attendance for a user. */
    public function getTodayAttendance(int $userId): ?Attendance
    {
        return $this->getAttendanceByDate($userId, Carbon::today());
    }

    /** Get attendance for a user by date. */
    public function getAttendanceByDate(int $userId, Carbon $date): ?Attendance
    {
        $this->validateUserExists($userId);
        $this->validateAttendanceDate($date);

        return $this->attendance
            ->with(['user.userDetail', 'user.roles'])
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $date->toDateString())
            ->first();
    }

    /** Get monthly attendance records for a user, including approved leave as virtual rows. */
    public function getMonthlyAttendance(int $userId, int $month, int $year): Collection
    {
        $this->validateUserExists($userId);
        $this->validateMonthYear($month, $year);
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        return $this->leaveAwareRows($from, $to, $userId);
    }

    /** Get attendance records between dates, including approved leave as virtual rows. */
    public function getAttendanceBetweenDates(int $userId, Carbon $from, Carbon $to): Collection
    {
        $this->validateUserExists($userId);
        $this->validateDateRange($from, $to);

        return $this->leaveAwareRows($from, $to, $userId);
    }

    /** Get attendance summary for a user. */
    public function getUserAttendanceSummary(int $userId, int $month, int $year): array
    {
        $records = $this->getMonthlyAttendance($userId, $month, $year);

        return [
            'total_days' => $records->count(),
            'working_days' => $records->whereNotIn('status', ['Holiday', 'Weekly Off'])->count(),
            'present' => $records->where('status', 'Present')->count(),
            'absent' => $records->where('status', 'Absent')->count(),
            'leave' => $records->where('status', 'Leave')->count(),
            'lwp' => $records->where('status', 'LWP')->count(),
            'holiday' => $records->where('status', 'Holiday')->count(),
            'weekly_off' => $records->where('status', 'Weekly Off')->count(),
            'late_days' => $records->where('display_status', 'Late')->count(),
            'half_days' => $records->where('display_status', 'Half Day')->count(),
        ];
    }

    /** Get attendance report for all employees, including approved leave as virtual rows. */
    public function getAttendanceReport(int $month, int $year): Collection
    {
        $this->validateMonthYear($month, $year);
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        return $this->leaveAwareRows($from, $to, null);
    }
    /** Determine whether a user checked in today. */
    public function hasCheckedInToday(int $userId): bool
    {
        return $this->getTodayAttendance($userId)?->check_in !== null;
    }

    /** Determine whether a user checked out today. */
    public function hasCheckedOutToday(int $userId): bool
    {
        return $this->getTodayAttendance($userId)?->check_out !== null;
    }

    /** Determine whether an attendance record is late. */
    public function isLate(int $attendanceId): bool
    {
        return (int) $this->findAttendance($attendanceId)->late_minutes > 0;
    }

    /** Determine whether an attendance record is half-day. */
    public function isHalfDay(int $attendanceId): bool
    {
        return (bool) $this->findAttendance($attendanceId)->half_day;
    }

    /** Delete an attendance record. */
    public function deleteAttendance(int $attendanceId): bool
    {
        return DB::transaction(function () use ($attendanceId): bool {
            return (bool) $this->findAttendance($attendanceId)->delete();
        });
    }




    /** Build leave-aware attendance rows for reporting and history. */
    protected function leaveAwareRows(Carbon $from, Carbon $to, ?int $userId = null): Collection
    {
        $employees = $userId !== null
            ? collect([$this->userService->getEmployeeProfile($userId)])
            : $this->userService->getActiveUsers();
        $rows = new Collection();

        foreach ($employees as $employee) {
            if (! $employee instanceof User) {
                continue;
            }

            for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                $status = $this->getAttendanceStatus((int) $employee->id, $date);
                $attendance = $status['attendance'];

                if ($attendance instanceof Attendance) {
                    $attendance->setAttribute('display_status', $status['label']);
                    $attendance->setAttribute('status_badge', $status['badge']);
                    $attendance->setAttribute('leave_type', $status['leave_type']);
                    $attendance->setAttribute('leave_reason', $status['leave_reason']);
                    $attendance->setAttribute('approved_by_name', $status['approved_by']);
                    $attendance->setAttribute('approval_status', ($status['leave'] ?? null) instanceof LeaveApply ? LeaveApprovalService::STATUS_APPROVED : null);
                    $attendance->setAttribute('is_lwp', $status['status'] === 'LWP');
                    $attendance->setAttribute('is_half_leave', (bool) ($status['is_half_leave'] ?? false));
                    $rows->push($attendance);
                    continue;
                }

                if (($status['leave'] ?? null) instanceof LeaveApply) {
                    $rows->push($this->virtualLeaveAttendance($status['leave'], $date, $status));
                    continue;
                }

                if (in_array($status['status'], ['Holiday', 'Weekly Off', 'Absent'], true)) {
                    $rows->push($this->virtualStatusAttendance($employee, $date, $status));
                }
            }
        }

        return new Collection($rows->sortBy(fn (Attendance $row): string => $row->attendance_date->toDateString() . '-' . str_pad((string) $row->user_id, 10, '0', STR_PAD_LEFT))->values()->all());
    }

    /** Aggregate all employees into a single day status for HR calendar. */
    protected function aggregateDayStatus(Carbon $date): array
    {
        $employees = $this->userService->getActiveUsers();
        $counts = [
            'Present' => 0,
            'Late' => 0,
            'Half Day' => 0,
            'Leave' => 0,
            'LWP' => 0,
            'Holiday' => 0,
            'Weekly Off' => 0,
            'Absent' => 0,
        ];

        foreach ($employees as $employee) {
            $status = $this->getAttendanceStatus((int) $employee->id, $date);
            $counts[$status['status']] = ($counts[$status['status']] ?? 0) + 1;
        }

        $primary = collect($counts)->filter(fn (int $count): bool => $count > 0)->sortDesc()->keys()->first() ?? 'Absent';

        return $this->statusPayload($primary, $primary, $this->statusBadge($primary), [
            'date' => $date,
            'attendance' => null,
            'holiday' => null,
            'leave' => null,
            'is_weekly_off' => false,
        ], ['counts' => $counts]);
    }

    /** Build a virtual row for non-attendance statuses such as Holiday, Weekly Off, and Absent. */
    protected function virtualStatusAttendance(User $employee, Carbon $date, array $status): Attendance
    {
        $attendance = new Attendance([
            'user_id' => $employee->id,
            'attendance_date' => $date->toDateString(),
            'check_in' => null,
            'check_out' => null,
            'working_hours' => null,
            'late_minutes' => 0,
            'half_day' => false,
            'status' => $status['status'],
        ]);
        $attendance->exists = false;
        $attendance->setRelation('user', $employee);
        $attendance->setAttribute('display_status', $status['label']);
        $attendance->setAttribute('status_badge', $status['badge']);
        $attendance->setAttribute('leave_type', null);
        $attendance->setAttribute('leave_reason', null);
        $attendance->setAttribute('approved_by_name', null);
        $attendance->setAttribute('approval_status', null);
        $attendance->setAttribute('is_lwp', false);
        $attendance->setAttribute('is_half_leave', false);

        return $attendance;
    }

    /** Build a normalized status payload. */
    protected function statusPayload(string $status, string $label, string $badge, array $source, array $extra = []): array
    {
        $attendance = $source['attendance'] ?? null;
        $leave = $source['leave'] ?? null;

        return array_merge([
            'status' => $status,
            'label' => $label,
            'badge' => $badge,
            'attendance_allowed' => ! in_array($status, ['Holiday', 'Weekly Off', 'Leave', 'LWP'], true),
            'source' => $leave instanceof LeaveApply ? 'leave' : ($attendance instanceof Attendance ? 'attendance' : strtolower(str_replace(' ', '_', $status))),
            'attendance' => $attendance,
            'leave' => $leave,
            'holiday' => $source['holiday'] ?? null,
            'is_half_leave' => false,
            'leave_type' => $leave instanceof LeaveApply ? $leave->leaveType?->leave_name : null,
            'leave_reason' => $leave instanceof LeaveApply ? $leave->reason : null,
            'approved_by' => $leave instanceof LeaveApply ? $leave->approvedBy?->name : null,
        ], $extra);
    }

    /** Build a status payload from an approved leave snapshot. */
    protected function leaveStatusPayload(LeaveApply $leave, ?Attendance $attendance, array $source): array
    {
        $badge = $this->getLeaveBadge($leave);
        $isLwp = $this->leavePolicyService->isLeaveWithoutPay((int) $leave->leave_type_id);
        $isHalfDay = $this->isHalfDayLeave($leave);
        $halfType = $this->halfDayType($leave);

        if ($isHalfDay) {
            $presentPart = $attendance instanceof Attendance && $attendance->check_in !== null
                ? ($halfType === 'first_half' ? 'Second Half Present' : 'First Half Present')
                : null;
            $leavePart = $halfType === 'first_half' ? 'First Half Leave' : 'Second Half Leave';
            $label = $presentPart ? $leavePart . ' / ' . $presentPart : $leavePart;

            return $this->statusPayload('Half Day', $label, 'info', $source, [
                'attendance_allowed' => ! $attendance instanceof Attendance,
                'source' => 'leave',
                'is_half_leave' => true,
                'half_day_type' => $halfType,
                'leave_badge' => $badge,
                'statuses' => array_values(array_filter([$leavePart, $presentPart])),
            ]);
        }

        return $this->statusPayload($isLwp ? 'LWP' : 'Leave', $badge['label'], $isLwp ? 'dark' : 'primary', $source, [
            'attendance_allowed' => false,
            'leave_badge' => $badge,
            'statuses' => [$isLwp ? 'LWP' : 'Leave'],
        ]);
    }

    /** Determine whether an approved leave is a half-day leave from its immutable snapshot. */
    protected function isHalfDayLeave(LeaveApply $leave): bool
    {
        $snapshot = is_array($leave->leave_calculation_json) ? $leave->leave_calculation_json : [];

        return (bool) ($snapshot['half_day'] ?? false) || (float) ($leave->requested_days ?? 0) === 0.5;
    }

    /** Resolve half-day session from the leave snapshot. */
    protected function halfDayType(LeaveApply $leave): string
    {
        $snapshot = is_array($leave->leave_calculation_json) ? $leave->leave_calculation_json : [];
        $type = strtolower((string) ($snapshot['half_day_type'] ?? $snapshot['half_day_session'] ?? 'first_half'));

        return in_array($type, ['first_half', 'second_half'], true) ? $type : 'first_half';
    }

    /** Resolve a Bootstrap badge color for attendance statuses. */
    protected function statusBadge(string $status): string
    {
        return match ($status) {
            'Present' => 'success',
            'Late' => 'warning',
            'Half Day' => 'info',
            'Leave' => 'primary',
            'LWP' => 'dark',
            'Holiday' => 'secondary',
            'Weekly Off' => 'dark',
            'Absent' => 'danger',
            default => 'secondary',
        };
    }

    /** Build a non-persisted attendance row for approved leave reporting. */
    protected function virtualLeaveAttendance(LeaveApply $leave, Carbon $date, array $status): Attendance
    {
        $attendance = new Attendance([
            'user_id' => $leave->user_id,
            'attendance_date' => $date->toDateString(),
            'check_in' => null,
            'check_out' => null,
            'working_hours' => null,
            'late_minutes' => 0,
            'half_day' => $status['status'] === 'Half Day',
            'status' => $status['status'],
        ]);
        $attendance->exists = false;
        $attendance->setRelation('user', $leave->user);
        $attendance->setAttribute('display_status', $status['label']);
        $attendance->setAttribute('status_badge', $status['badge']);
        $attendance->setAttribute('leave_type', $status['leave_type']);
        $attendance->setAttribute('leave_reason', $status['leave_reason']);
        $attendance->setAttribute('approved_by_name', $status['approved_by']);
        $attendance->setAttribute('approval_status', LeaveApprovalService::STATUS_APPROVED);
        $attendance->setAttribute('is_lwp', $status['status'] === 'LWP');
        $attendance->setAttribute('is_half_leave', (bool) ($status['is_half_leave'] ?? false));

        return $attendance;
    }

    /** Resolve the display status used by attendance presentation. */
    protected function displayStatus(Attendance $attendance): string
    {
        if ((int) $attendance->late_minutes > 0) {
            return 'Late';
        }

        if ((bool) $attendance->half_day) {
            return 'Half Day';
        }

        return (string) $attendance->status;
    }
    /** Find attendance by ID. */
    protected function findAttendance(int $attendanceId): Attendance
    {
        if ($attendanceId <= 0) {
            throw new InvalidArgumentException('Attendance ID must be a positive integer.');
        }

        $attendance = $this->attendance->with(['user.userDetail', 'user.roles'])->find($attendanceId);

        if (! $attendance instanceof Attendance) {
            throw new RuntimeException("Attendance [{$attendanceId}] was not found.");
        }

        return $attendance;
    }

    /** Validate that a user exists. */
    protected function validateUserExists(int $userId): void
    {
        if ($userId <= 0 || ! $this->userService->userExists($userId)) {
            throw new RuntimeException("User [{$userId}] was not found.");
        }
    }

    /** Validate that a user exists and is active. */
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

    /** Resolve a time value or use now. */
    protected function resolveTime(mixed $time): Carbon
    {
        if ($time === null || $time === '') {
            return Carbon::now();
        }

        if (! is_string($time)) {
            throw new InvalidArgumentException('Attendance time must be a string.');
        }

        try {
            $resolved = Carbon::createFromFormat('H:i:s', $time);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException('Attendance time must use H:i:s format.', 0, $exception);
        }

        if ($resolved === false || $resolved->format('H:i:s') !== $time) {
            throw new InvalidArgumentException('Attendance time must use H:i:s format.');
        }

        return $resolved;
    }

    /** Resolve the attendance date from a request payload or today. */
    protected function resolveAttendanceDate(mixed $date): Carbon
    {
        if ($date === null || $date === '') {
            return Carbon::today();
        }

        if (! is_string($date)) {
            throw new InvalidArgumentException('Attendance date is invalid.');
        }

        try {
            $resolved = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException('Attendance date format is invalid.', 0, $exception);
        }

        if ($resolved === false || $resolved->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException('Attendance date format is invalid.');
        }

        $resolved->startOfDay();
        $this->validateAttendanceDate($resolved);

        return $resolved;
    }

    /** Build a Carbon instance for an attendance time field. */
    protected function attendanceDateTime(Attendance $attendance, string $field): Carbon
    {
        $value = $attendance->{$field};

        if ($value === null) {
            throw new RuntimeException("Attendance {$field} is required.");
        }

        return Carbon::parse($attendance->attendance_date->toDateString() . ' ' . $value);
    }

    /** Build a Carbon instance for a configured time on the attendance date. */
    protected function timeOnAttendanceDate(Attendance $attendance, string $time): Carbon
    {
        return Carbon::parse($attendance->attendance_date->toDateString() . ' ' . $time);
    }

    /** Calculate required working hours from company settings. */
    protected function requiredWorkingHours(Attendance $attendance): float
    {
        $start = $this->timeOnAttendanceDate($attendance, $this->companySettingService->getOfficeStartTime());
        $end = $this->timeOnAttendanceDate($attendance, $this->companySettingService->getOfficeEndTime());

        if ($end->lessThanOrEqualTo($start)) {
            throw new RuntimeException('Office end time must be greater than office start time.');
        }

        return $start->floatDiffInHours($end);
    }

    /** Validate settings, holidays, weekly off, and approved leave before marking attendance. */
    protected function validateAttendanceContext(int $userId, Carbon $date): void
    {
        $this->validateAttendanceDate($date);
        $this->validateCompanyAttendanceSettings();
        $status = $this->getAttendanceStatus($userId, $date);

        if ($status['status'] === 'Holiday') {
            throw new RuntimeException('Attendance cannot be marked on a company holiday.');
        }

        if ($status['status'] === 'Weekly Off') {
            throw new RuntimeException('Attendance cannot be marked on a weekly off.');
        }

        if (($status['leave'] ?? null) instanceof LeaveApply && ! (bool) ($status['is_half_leave'] ?? false)) {
            throw new RuntimeException('You are already on approved leave today.');
        }
    }

    /** Validate required company attendance settings. */
    protected function validateCompanyAttendanceSettings(): void
    {
        $message = 'Company attendance settings are incomplete. Please contact the administrator.';

        try {
            $officeStart = $this->companySettingService->getOfficeStartTime();
            $officeEnd = $this->companySettingService->getOfficeEndTime();
            $lateThreshold = $this->companySettingService->getLateThreshold();
            $halfDayThreshold = $this->companySettingService->getHalfDayThreshold();
            $weeklyOff = $this->companySettingService->getWeeklyOff();

            foreach ([$officeStart, $officeEnd, $weeklyOff] as $value) {
                if (trim((string) $value) === '') {
                    throw new RuntimeException($message);
                }
            }

            $start = Carbon::createFromFormat('H:i:s', $officeStart);
            $end = Carbon::createFromFormat('H:i:s', $officeEnd);

            if (
                $start === false
                || $end === false
                || $start->format('H:i:s') !== $officeStart
                || $end->format('H:i:s') !== $officeEnd
                || $end->lessThanOrEqualTo($start)
                || $lateThreshold < 0
                || $halfDayThreshold <= $lateThreshold
                || ! in_array($weeklyOff, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], true)
            ) {
                throw new RuntimeException($message);
            }
        } catch (RuntimeException $exception) {
            throw $exception->getMessage() === $message ? $exception : new RuntimeException($message, 0, $exception);
        } catch (\Throwable $exception) {
            throw new RuntimeException($message, 0, $exception);
        }
    }
    /** Validate attendance date. */
    protected function validateAttendanceDate(Carbon $date): void
    {
        if ($date->year < 2000) {
            throw new InvalidArgumentException('Attendance date is invalid.');
        }

        if ($date->isFuture()) {
            throw new InvalidArgumentException('Attendance date cannot be in the future.');
        }
    }

    /** Validate month and year. */
    protected function validateMonthYear(int $month, int $year): void
    {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('Attendance month must be between 1 and 12.');
        }

        if ($year < 2000 || $year > 2100) {
            throw new InvalidArgumentException('Attendance year must be between 2000 and 2100.');
        }
    }

    /** Validate date range. */
    protected function validateDateRange(Carbon $from, Carbon $to): void
    {
        $this->validateAttendanceDate($from);
        $this->validateAttendanceDate($to);

        if ($to->lessThan($from)) {
            throw new InvalidArgumentException('Attendance end date must be greater than or equal to start date.');
        }
    }
}














