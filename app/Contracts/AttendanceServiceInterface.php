<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Defines the attendance service contract.
 */
interface AttendanceServiceInterface
{
    /** Get paginated attendance records. */
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    /** Get filtered paginated attendance records. */
    public function getFilteredAttendance(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /** Get today's attendance summary. */
    public function getTodaySummary(): array;

    /** Get prepared monthly calendar data. */
    public function getMonthlyCalendar(int $month, int $year, ?int $userId = null): array;

    /** Get an attendance record by ID. */
    public function getById(int $id): Attendance;

    /** Mark employee check-in. */
    public function markCheckIn(int $userId, array $data): Attendance;

    /** Mark employee check-out. */
    public function markCheckOut(int $userId): Attendance;

    /** Calculate attendance working hours. */
    public function calculateWorkingHours(int $attendanceId): float;

    /** Calculate late arrival minutes. */
    public function calculateLateMinutes(int $attendanceId): int;

    /** Detect whether attendance is half-day. */
    public function detectHalfDay(int $attendanceId): bool;

    /** Update attendance status. */
    public function updateAttendanceStatus(int $attendanceId): Attendance;

    /** Get today's attendance for a user. */
    public function getTodayAttendance(int $userId): ?Attendance;

    /** Get attendance for a user by date. */
    public function getAttendanceByDate(int $userId, Carbon $date): ?Attendance;

    /** Get monthly attendance records for a user. */
    public function getMonthlyAttendance(int $userId, int $month, int $year): Collection;

    /** Get attendance records between dates. */
    public function getAttendanceBetweenDates(int $userId, Carbon $from, Carbon $to): Collection;

    /** Get attendance summary for a user. */
    public function getUserAttendanceSummary(int $userId, int $month, int $year): array;

    /** Get attendance report for all employees. */
    public function getAttendanceReport(int $month, int $year): Collection;

    /** Determine whether a user checked in today. */
    public function hasCheckedInToday(int $userId): bool;

    /** Determine whether a user checked out today. */
    public function hasCheckedOutToday(int $userId): bool;

    /** Determine whether an attendance record is late. */
    public function isLate(int $attendanceId): bool;

    /** Determine whether an attendance record is half-day. */
    public function isHalfDay(int $attendanceId): bool;

    /** Delete an attendance record. */
    public function deleteAttendance(int $attendanceId): bool;
}
