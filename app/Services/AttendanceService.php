<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\CompanySettingServiceInterface;
use App\Contracts\HolidayServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Models\Attendance;
use App\Models\User;
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
        protected HolidayServiceInterface $holidayService
    ) {
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
        $records = $this->attendance
            ->with(['user.userDetail', 'user.roles'])
            ->whereDate('attendance_date', Carbon::today()->toDateString())
            ->get();

        $activeEmployees = $this->userService->getActiveUsers()->count();
        $inactiveEmployees = $this->userService->getInactiveUsers()->count();

        return [
            'total_employees' => $activeEmployees + $inactiveEmployees,
            'present' => $records->where('status', 'Present')->count(),
            'absent' => $records->where('status', 'Absent')->count(),
            'late' => $records->filter(fn (Attendance $attendance): bool => (int) $attendance->late_minutes > 0)->count(),
            'half_day' => $records->filter(fn (Attendance $attendance): bool => (bool) $attendance->half_day)->count(),
            'leave' => $records->where('status', 'Leave')->count(),
        ];
    }

    /** Get a prepared, Monday-aligned monthly attendance calendar. */
    public function getMonthlyCalendar(int $month, int $year, ?int $userId = null): array
    {
        $this->validateMonthYear($month, $year);
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $records = ($userId === null
            ? $this->getAttendanceReport($month, $year)
            : $this->getMonthlyAttendance($userId, $month, $year))->keyBy(
                fn (Attendance $attendance): string => $attendance->attendance_date->toDateString()
            );
        $holidays = $this->holidayService->active();
        $weeks = [];
        $week = [];

        for ($date = $gridStart->copy(); $date->lte($gridEnd); $date->addDay()) {
            $record = $records->get($date->toDateString());
            $holiday = $holidays->first(
                fn ($item): bool => $date->betweenIncluded($item->from_date, $item->to_date)
            );
            $isWeeklyOff = $holiday === null && $this->companySettingService->isWeeklyOff($date);
            $status = $holiday !== null
                ? 'Holiday'
                : ($isWeeklyOff ? 'Weekly Off' : ($record instanceof Attendance ? $this->displayStatus($record) : 'No Attendance'));

            $week[] = [
                'date' => $date->toDateString(),
                'day' => $date->day,
                'weekday' => $date->format('D'),
                'is_current_month' => $date->month === $month,
                'is_today' => $date->isToday(),
                'status' => $status,
                'statuses' => [$status],
                'holiday_name' => $holiday?->name,
                'is_weekend' => $isWeeklyOff,
                'check_in' => $record?->check_in,
                'check_out' => $record?->check_out,
                'working_hours' => $record?->working_hours,
                'records_count' => $record instanceof Attendance ? 1 : 0,
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
            'has_attendance' => $records->isNotEmpty(),
            'weeks' => $weeks,
        ];
    }
    /** Get the complete authenticated-user header attendance view model. */
    public function getTodayAttendanceWidget(int $userId): array
    {
        $employee = $this->userService->getEmployeeProfile($userId);
        $today = Carbon::today();
        $now = Carbon::now();
        $attendance = $this->attendance->with(['user.userDetail', 'user.roles'])
            ->where('user_id', $userId)
            ->whereDate('attendance_date', $today->toDateString())
            ->first();
        $holiday = $this->holidayService->active()->first(
            fn ($item): bool => $today->betweenIncluded($item->from_date, $item->to_date)
        );
        $isHoliday = $holiday !== null;
        $isWeeklyOff = $this->companySettingService->isWeeklyOff($today);
        $officeStart = $this->companySettingService->getOfficeStartTime();
        $officeEnd = $this->companySettingService->getOfficeEndTime();
        $lateThreshold = $this->companySettingService->getLateThreshold();
        $halfDayThreshold = $this->companySettingService->getHalfDayThreshold();
        $completed = $attendance?->check_out !== null;
        $attendanceStatus = ($attendance?->half_day ?? false)
            ? 'Half Day'
            : ((int) ($attendance?->late_minutes ?? 0) > 0 ? 'Late' : ($attendance?->status ?? 'Attendance Pending'));
        $canCheckIn = ! $isHoliday && ! $isWeeklyOff && $attendance === null;
        $canCheckOut = ! $isHoliday && ! $isWeeklyOff && $attendance?->check_in !== null && ! $completed;
        $status = $isHoliday ? 'Holiday' : ($isWeeklyOff ? 'Weekly Off' : ($completed ? 'Attendance Completed' : ($attendanceStatus)));
        $detail = $employee->userDetail;

        return [
            'status' => $status,
            'statusBadge' => $isHoliday || $isWeeklyOff ? 'secondary' : ($completed ? 'success' : ($attendance ? 'warning' : 'secondary')),
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
            'isWeeklyOff' => $isWeeklyOff,
            'isCompanyHoliday' => $isHoliday,
            'holidayName' => $holiday?->name,
            'holidayDate' => $holiday?->from_date?->format('d M Y'),
            'officeOpen' => ! $isHoliday && ! $isWeeklyOff,
            'todayDate' => $today->format('d M Y'),
            'currentTime' => $now->format('h:i A'),
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
        $attendanceDate = $this->resolveAttendanceDate($data['attendance_date'] ?? null);
        $this->validateAttendanceContext($attendanceDate);

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
        $this->validateAttendanceContext($today);

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

    /** Get monthly attendance records for a user. */
    public function getMonthlyAttendance(int $userId, int $month, int $year): Collection
    {
        $this->validateUserExists($userId);
        $this->validateMonthYear($month, $year);

        return $this->attendance
            ->with(['user.userDetail', 'user.roles'])
            ->where('user_id', $userId)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date')
            ->get();
    }

    /** Get attendance records between dates. */
    public function getAttendanceBetweenDates(int $userId, Carbon $from, Carbon $to): Collection
    {
        $this->validateUserExists($userId);
        $this->validateDateRange($from, $to);

        return $this->attendance
            ->with(['user.userDetail', 'user.roles'])
            ->where('user_id', $userId)
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('attendance_date')
            ->get();
    }

    /** Get attendance summary for a user. */
    public function getUserAttendanceSummary(int $userId, int $month, int $year): array
    {
        $records = $this->getMonthlyAttendance($userId, $month, $year);

        return [
            'total_days' => $records->count(),
            'present' => $records->where('status', 'Present')->count(),
            'absent' => $records->where('status', 'Absent')->count(),
            'leave' => $records->where('status', 'Leave')->count(),
            'holiday' => $records->where('status', 'Holiday')->count(),
            'late_days' => $records->filter(fn (Attendance $attendance): bool => (int) $attendance->late_minutes > 0)->count(),
            'half_days' => $records->filter(fn (Attendance $attendance): bool => (bool) $attendance->half_day)->count(),
        ];
    }

    /** Get attendance report for all employees. */
    public function getAttendanceReport(int $month, int $year): Collection
    {
        $this->validateMonthYear($month, $year);

        return $this->attendance
            ->with(['user.userDetail', 'user.roles'])
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date')
            ->get();
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


    /** Resolve the display status used by attendance presentation. */
    protected function displayStatus(Attendance $attendance): string
    {
        if ((bool) $attendance->half_day) {
            return 'Half Day';
        }

        if ((int) $attendance->late_minutes > 0) {
            return 'Late';
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

    /** Validate settings, holidays, and weekly off before marking attendance. */
    protected function validateAttendanceContext(Carbon $date): void
    {
        $this->validateAttendanceDate($date);
        $this->validateCompanyAttendanceSettings();

        $holiday = $this->holidayService->active()->first(
            fn ($item): bool => $date->betweenIncluded($item->from_date, $item->to_date)
        );

        if ($holiday !== null) {
            throw new RuntimeException('Attendance cannot be marked on a company holiday.');
        }

        if ($this->companySettingService->isWeeklyOff($date)) {
            throw new RuntimeException('Attendance cannot be marked on a weekly off.');
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


