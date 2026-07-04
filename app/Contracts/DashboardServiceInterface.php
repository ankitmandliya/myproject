<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * Defines the dashboard aggregation service contract.
 */
interface DashboardServiceInterface
{
    /** Get complete dashboard summary. */
    public function getDashboardSummary(): array;

    /** Get employee statistics. */
    public function getEmployeeStatistics(): array;

    /** Get attendance statistics. */
    public function getAttendanceStatistics(): array;

    /** Get leave statistics. */
    public function getLeaveStatistics(): array;

    /** Get salary statistics. */
    public function getSalaryStatistics(): array;

    /** Get company statistics. */
    public function getCompanyStatistics(): array;

    /** Get recent leave applications. */
    public function getRecentLeaves(int $limit = 10): Collection;

    /** Get recent attendance records. */
    public function getRecentAttendance(int $limit = 10): Collection;

    /** Get recent salary slips. */
    public function getRecentSalarySlips(int $limit = 10): Collection;

    /** Get recently added employees. */
    public function getRecentEmployees(int $limit = 10): Collection;

    /** Get upcoming holidays. */
    public function getUpcomingHolidays(int $limit = 10): Collection;

    /** Get monthly attendance chart data. */
    public function getMonthlyAttendanceChart(int $month, int $year): array;

    /** Get monthly leave chart data. */
    public function getMonthlyLeaveChart(int $month, int $year): array;

    /** Get monthly salary chart data. */
    public function getMonthlySalaryChart(int $month, int $year): array;

    /** Get system overview. */
    public function getSystemOverview(): array;

    /** Get all dashboard widget data. */
    public function getDashboardWidgets(): array;
}
