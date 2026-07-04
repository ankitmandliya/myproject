<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\CompanySettingServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
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
        protected CompanySettingServiceInterface $companySettingService
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

    /** Get an attendance record by ID. */
    public function getById(int $id): Attendance
    {
        return $this->findAttendance($id);
    }

    /** Mark employee check-in for today. */
    public function markCheckIn(int $userId, array $data): Attendance
    {
        $this->validateActiveUser($userId);

        $today = Carbon::today();

        return DB::transaction(function () use ($userId, $data, $today): Attendance {
            if ($this->getAttendanceByDate($userId, $today) instanceof Attendance) {
                throw new RuntimeException("User [{$userId}] has already checked in today.");
            }

            $checkIn = $this->resolveTime($data['check_in'] ?? null);

            $attendance = $this->attendance->create([
                'user_id' => $userId,
                'attendance_date' => $today->toDateString(),
                'check_in' => $checkIn->format('H:i:s'),
                'status' => 'Present',
                'working_hours' => 0,
                'late_minutes' => 0,
                'half_day' => false,
            ]);

            $this->calculateLateMinutes($attendance->id);

            return $this->findAttendance($attendance->id);
        });
    }

    /** Mark employee checkout for today. */
    public function markCheckOut(int $userId): Attendance
    {
        $this->validateActiveUser($userId);

        return DB::transaction(function () use ($userId): Attendance {
            $attendance = $this->getTodayAttendance($userId);

            if (! $attendance instanceof Attendance) {
                throw new RuntimeException("Today attendance was not found for user [{$userId}].");
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

        return Carbon::createFromFormat('H:i:s', $time);
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

    /** Validate attendance date. */
    protected function validateAttendanceDate(Carbon $date): void
    {
        if ($date->year < 2000) {
            throw new InvalidArgumentException('Attendance date is invalid.');
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
