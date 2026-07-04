<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\SalarySlip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Defines the salary service contract.
 */
interface SalaryServiceInterface
{
    /** Get paginated salary slips. */
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    /** Get a salary slip by ID. */
    public function getById(int $id): SalarySlip;

    /** Generate monthly salary for a user. */
    public function generateMonthlySalary(int $userId, int|string $month, int|string $year): SalarySlip;

    /** Generate payroll for all active employees. */
    public function generatePayroll(int|string $month, int|string $year): Collection;

    /** Calculate net salary for a user. */
    public function calculateNetSalary(int $userId, int|string|null $month = null, int|string|null $year = null): array;

    /** Calculate monthly allowance. */
    public function calculateAllowance(int $userId): float;

    /** Calculate manual deductions. */
    public function calculateDeduction(int $userId): float;

    /** Calculate leave deduction. */
    public function calculateLeaveDeduction(int $userId, int|string $month, int|string $year): float;

    /** Calculate overtime amount. */
    public function calculateOvertime(int $userId, int|string $month, int|string $year): float;

    /** Get a salary slip for a user and month. */
    public function getSalarySlip(int $userId, int|string $month, int|string|null $year = null): ?SalarySlip;

    /** Get salary history for a user. */
    public function getSalaryHistory(int $userId): Collection;

    /** Get monthly payroll. */
    public function getMonthlyPayroll(int|string $month, int|string $year): Collection;

    /** Get salary report. */
    public function getSalaryReport(int|string $month, int|string $year): Collection;

    /** Determine whether a salary slip exists. */
    public function salaryExists(int $userId, int|string $month, int|string $year): bool;

    /** Delete a salary slip. */
    public function deleteSalarySlip(int $salaryId): bool;

    /** Get employee salary summary. */
    public function getEmployeeSalarySummary(int $userId): array;

    /** Get latest salary for a user. */
    public function getLatestSalary(int $userId): ?SalarySlip;
}
