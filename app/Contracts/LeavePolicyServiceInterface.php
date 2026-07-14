<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\EmployeeLeaveBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface LeavePolicyServiceInterface
{
    public function currentFinancialYear(?Carbon $date = null): string;

    public function getFinancialYear(?Carbon $date = null): string;

    public function financialYearStart(?string $financialYear = null): Carbon;

    public function financialYearEnd(?string $financialYear = null): Carbon;

    public function allocateLeave(?int $employeeId = null, ?string $financialYear = null): Collection;

    public function allocateFinancialYear(?string $financialYear = null): Collection;

    public function allocateEmployee(int $employeeId, ?string $financialYear = null, bool $forceRecalculate = false): Collection;

    public function allocateLeaveType(int $employeeId, int $leaveTypeId, ?string $financialYear = null, bool $forceRecalculate = false): EmployeeLeaveBalance;

    public function allocateProrataLeave(int $employeeId, int $leaveTypeId, ?string $financialYear = null): float;

    public function carryForwardEarnLeave(int $employeeId, string $fromFinancialYear, string $toFinancialYear): float;

    public function resetFinancialYear(string $fromFinancialYear, string $toFinancialYear): Collection;

    public function generateFinancialYearBalances(?string $financialYear = null): Collection;

    public function calculateRemaining(int $employeeId, int $leaveTypeId, ?string $financialYear = null): float;

    public function consumeLeave(int $employeeId, int $leaveTypeId, float $days, ?string $financialYear = null): EmployeeLeaveBalance;

    public function restoreLeave(int $employeeId, int $leaveTypeId, float $days, ?string $financialYear = null): EmployeeLeaveBalance;

    public function carryForward(int $employeeId, int $leaveTypeId, string $fromFinancialYear, string $toFinancialYear): float;

    public function getBalance(int $employeeId, int $leaveTypeId, ?string $financialYear = null): ?EmployeeLeaveBalance;

    public function getEmployeeBalances(int $employeeId, ?string $financialYear = null): Collection;

    public function getBalanceResponse(int $employeeId, ?string $financialYear = null): Collection;

    public function calculateLeaveDuration(Carbon $from, Carbon $to, array $options = []): array;

    public function calculateRequestedDays(Carbon $from, Carbon $to, array $options = []): float;

    public function calculateHolidayDays(Carbon $from, Carbon $to): array;

    public function calculateWeeklyOffDays(Carbon $from, Carbon $to, array $weeklyOffs): array;

    public function calculateSandwichDays(array $holidayDates, array $weeklyOffDates, array $options = []): array;

    public function calculatePayableLeaveDays(array $duration): float;

    public function calculateLeaveDays(Carbon $from, Carbon $to): float;

    public function validateLeaveBalance(int $employeeId, int $leaveTypeId, float $requestedDays, ?string $financialYear = null): bool;

    public function prepareCalculationSnapshot(int $employeeId, int $leaveTypeId, Carbon $from, Carbon $to, array $options = []): array;
}



