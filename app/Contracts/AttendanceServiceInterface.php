<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceServiceInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function getFilteredAttendance(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function getTodaySummary(): array;

    public function getMonthlyCalendar(int $month, int $year, ?int $userId = null): array;

    public function autoFinalizeOpenAttendance(int $userId): int;

    public function getTodayAttendanceWidget(int $userId): array;

    public function getById(int $id): Attendance;

    public function markCheckIn(int $userId, array $data): Attendance;

    public function markCheckOut(int $userId): Attendance;

    public function calculateWorkingHours(int $attendanceId): float;

    public function calculateLateMinutes(int $attendanceId): int;

    public function detectHalfDay(int $attendanceId): bool;

    public function updateAttendanceStatus(int $attendanceId): Attendance;

    public function getTodayAttendance(int $userId): ?Attendance;

    public function getAttendanceByDate(int $userId, Carbon $date): ?Attendance;

    public function getMonthlyAttendance(int $userId, int $month, int $year): Collection;

    public function getAttendanceBetweenDates(int $userId, Carbon $from, Carbon $to): Collection;

    public function getUserAttendanceSummary(int $userId, int $month, int $year): array;

    public function getAttendanceReport(int $month, int $year): Collection;

    public function getAttendanceStatus(int $userId, Carbon $date): array;

    public function getAttendanceSource(int $userId, Carbon $date): array;

    public function getLeaveStatus(int $userId, Carbon $date): ?object;

    public function getLeaveBadge(?object $leave): array;

    public function isAttendanceAllowed(int $userId, Carbon $date): bool;

    public function canCheckIn(int $userId, ?Carbon $date = null): bool;

    public function canCheckOut(int $userId, ?Carbon $date = null): bool;

    public function hasCheckedInToday(int $userId): bool;

    public function hasCheckedOutToday(int $userId): bool;

    public function isLate(int $attendanceId): bool;

    public function isHalfDay(int $attendanceId): bool;

    public function deleteAttendance(int $attendanceId): bool;
}
