<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SalaryServiceInterface;
use App\Models\LeaveApply;
use App\Models\SalarySlip;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service for HRMS salary and payroll management.
 */
class SalaryService implements SalaryServiceInterface
{
    /** Create a new salary service instance. */
    public function __construct(
        protected SalarySlip $salarySlip,
        protected UserDetail $userDetail,
        protected User $user,
        protected LeaveApply $leaveApply
    ) {
    }

    /** Get paginated salary slips. */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->salarySlip
            ->with('user.userDetail')
            ->latest('year')
            ->latest('month')
            ->latest('created_at')
            ->paginate($perPage);
    }

    /** Get a salary slip by ID. */
    public function getById(int $id): SalarySlip
    {
        return $this->findSalarySlip($id);
    }

    /** Generate monthly salary for a user. */
    public function generateMonthlySalary(int $userId, int|string $month, int|string $year): SalarySlip
    {
        $month = $this->validateMonth($month);
        $year = $this->validateYear($year);

        return DB::transaction(function () use ($userId, $month, $year): SalarySlip {
            $user = $this->validateActiveUser($userId);

            if ($this->salaryExists($userId, $month, $year)) {
                throw new RuntimeException("Salary already generated for user [{$userId}] for {$month}/{$year}.");
            }

            $salary = $this->calculateNetSalary($userId, $month, $year);

            return $this->salarySlip->create([
                'user_id' => $user->id,
                'month' => $month,
                'year' => $year,
                'basic_salary' => $salary['basic_salary'],
                'allowance' => $salary['allowance'],
                'deduction' => $salary['deduction'],
                'overtime' => $salary['overtime'],
                'leave_deduction' => $salary['leave_deduction'],
                'net_salary' => $salary['net_salary'],
                'generated_at' => now(),
            ])->load('user.userDetail');
        });
    }

    /** Generate payroll for all active employees. */
    public function generatePayroll(int|string $month, int|string $year): Collection
    {
        $month = $this->validateMonth($month);
        $year = $this->validateYear($year);

        return DB::transaction(function () use ($month, $year): Collection {
            $generated = [];

            $activeEmployees = $this->userDetail
                ->with('user')
                ->active()
                ->whereHas('user')
                ->orderBy('user_id')
                ->get();

            foreach ($activeEmployees as $employeeDetail) {
                if ($this->salaryExists((int) $employeeDetail->user_id, $month, $year)) {
                    continue;
                }

                $generated[] = $this->generateMonthlySalary((int) $employeeDetail->user_id, $month, $year);
            }

            return $this->salarySlip->newCollection($generated);
        });
    }

    /** Calculate net salary for a user. */
    public function calculateNetSalary(int $userId, int|string|null $month = null, int|string|null $year = null): array
    {
        $month = $this->validateMonth($month ?? now()->month);
        $year = $this->validateYear($year ?? now()->year);
        $user = $this->validateActiveUser($userId);

        $basicSalary = round((float) $user->userDetail->basic_salary, 2);
        $allowance = $this->calculateAllowance($userId);
        $deduction = $this->calculateDeduction($userId);
        $overtime = $this->calculateOvertime($userId, $month, $year);
        $leaveDeduction = $this->calculateLeaveDeduction($userId, $month, $year);
        $netSalary = round($basicSalary + $allowance + $overtime - $deduction - $leaveDeduction, 2);

        return [
            'basic_salary' => $basicSalary,
            'allowance' => $allowance,
            'deduction' => $deduction,
            'overtime' => $overtime,
            'leave_deduction' => $leaveDeduction,
            'net_salary' => $netSalary,
        ];
    }

    /** Calculate monthly allowance. */
    public function calculateAllowance(int $userId): float
    {
        $this->validateActiveUser($userId);

        return round((float) ($this->getLatestSalary($userId)?->allowance ?? 0), 2);
    }

    /** Calculate manual deductions. */
    public function calculateDeduction(int $userId): float
    {
        $this->validateActiveUser($userId);

        return round((float) ($this->getLatestSalary($userId)?->deduction ?? 0), 2);
    }

    /** Calculate leave deduction. */
    public function calculateLeaveDeduction(int $userId, int|string $month, int|string $year): float
    {
        $month = $this->validateMonth($month);
        $year = $this->validateYear($year);
        $user = $this->validateActiveUser($userId);

        $periodStart = Carbon::create($year, $month, 1)->startOfDay();
        $periodEnd = $periodStart->copy()->endOfMonth()->startOfDay();
        $approvedLeaves = $this->leaveApply
            ->approved()
            ->where('user_id', $user->id)
            ->whereDate('from_date', '<=', $periodEnd->toDateString())
            ->whereDate('to_date', '>=', $periodStart->toDateString())
            ->get();

        if ($approvedLeaves->isEmpty()) {
            return 0.0;
        }

        $leaveDays = 0;

        foreach ($approvedLeaves as $leave) {
            $from = Carbon::parse($leave->from_date)->startOfDay()->max($periodStart);
            $to = Carbon::parse($leave->to_date)->startOfDay()->min($periodEnd);
            $leaveDays += $from->diffInDays($to) + 1;
        }

        $dailySalary = (float) $user->userDetail->basic_salary / $periodStart->daysInMonth;

        return round($dailySalary * $leaveDays, 2);
    }

    /** Calculate overtime amount. */
    public function calculateOvertime(int $userId, int|string $month, int|string $year): float
    {
        $this->validateMonth($month);
        $this->validateYear($year);
        $this->validateActiveUser($userId);

        return round((float) ($this->getLatestSalary($userId)?->overtime ?? 0), 2);
    }

    /** Get a salary slip for a user and month. */
    public function getSalarySlip(int $userId, int|string $month, int|string|null $year = null): ?SalarySlip
    {
        $month = $this->validateMonth($month);
        $this->validateUserExists($userId);

        $query = $this->salarySlip
            ->with('user.userDetail')
            ->where('user_id', $userId)
            ->where('month', $month);

        if ($year !== null) {
            $query->where('year', $this->validateYear($year));
        }

        return $query
            ->latest('year')
            ->latest('created_at')
            ->first();
    }

    /** Get salary history for a user. */
    public function getSalaryHistory(int $userId): Collection
    {
        $this->validateUserExists($userId);

        return $this->salarySlip
            ->with('user.userDetail')
            ->where('user_id', $userId)
            ->latest('year')
            ->latest('month')
            ->latest('created_at')
            ->get();
    }

    /** Get monthly payroll. */
    public function getMonthlyPayroll(int|string $month, int|string $year): Collection
    {
        $month = $this->validateMonth($month);
        $year = $this->validateYear($year);

        return $this->salarySlip
            ->with('user.userDetail')
            ->forPeriod($month, $year)
            ->orderBy('user_id')
            ->get();
    }

    /** Get salary report. */
    public function getSalaryReport(int|string $month, int|string $year): Collection
    {
        return $this->getMonthlyPayroll($month, $year);
    }

    /** Determine whether a salary slip exists. */
    public function salaryExists(int $userId, int|string $month, int|string $year): bool
    {
        $month = $this->validateMonth($month);
        $year = $this->validateYear($year);
        $this->validateUserExists($userId);

        return $this->salarySlip
            ->where('user_id', $userId)
            ->forPeriod($month, $year)
            ->exists();
    }

    /** Delete a salary slip. */
    public function deleteSalarySlip(int $salaryId): bool
    {
        return DB::transaction(function () use ($salaryId): bool {
            return (bool) $this->findSalarySlip($salaryId)->delete();
        });
    }

    /** Get employee salary summary. */
    public function getEmployeeSalarySummary(int $userId): array
    {
        $history = $this->getSalaryHistory($userId);
        $netSalaries = $history->map(fn (SalarySlip $salarySlip): float => (float) $salarySlip->net_salary);

        return [
            'total_salary_records' => $history->count(),
            'latest_salary' => $history->first(),
            'average_salary' => $netSalaries->isEmpty() ? 0.0 : round($netSalaries->average(), 2),
            'highest_salary' => $netSalaries->isEmpty() ? 0.0 : round($netSalaries->max(), 2),
            'lowest_salary' => $netSalaries->isEmpty() ? 0.0 : round($netSalaries->min(), 2),
        ];
    }

    /** Get latest salary for a user. */
    public function getLatestSalary(int $userId): ?SalarySlip
    {
        $this->validateUserExists($userId);

        return $this->salarySlip
            ->with('user.userDetail')
            ->where('user_id', $userId)
            ->latest('year')
            ->latest('month')
            ->latest('created_at')
            ->first();
    }

    /** Find a salary slip by ID. */
    protected function findSalarySlip(int $salaryId): SalarySlip
    {
        $this->validatePositiveId($salaryId, 'Salary slip ID');

        $salarySlip = $this->salarySlip->with('user.userDetail')->find($salaryId);

        if (! $salarySlip instanceof SalarySlip) {
            throw new RuntimeException("Salary record [{$salaryId}] was not found.");
        }

        return $salarySlip;
    }

    /** Validate that a user exists. */
    protected function validateUserExists(int $userId): User
    {
        $this->validatePositiveId($userId, 'User ID');

        $user = $this->user->with('userDetail')->find($userId);

        if (! $user instanceof User) {
            throw new RuntimeException("User [{$userId}] was not found.");
        }

        return $user;
    }

    /** Validate that a user exists and is active. */
    protected function validateActiveUser(int $userId): User
    {
        $user = $this->validateUserExists($userId);

        if (! $user->userDetail instanceof UserDetail) {
            throw new RuntimeException("User [{$userId}] does not have an employee profile.");
        }

        if (! (bool) $user->userDetail->status) {
            throw new RuntimeException("User [{$userId}] is inactive.");
        }

        if ((float) $user->userDetail->basic_salary < 0) {
            throw new RuntimeException("User [{$userId}] has an invalid basic salary.");
        }

        return $user;
    }

    /** Validate a month value. */
    protected function validateMonth(int|string $month): int
    {
        if (! is_numeric($month)) {
            throw new InvalidArgumentException('Month must be numeric.');
        }

        $month = (int) $month;

        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('Month must be between 1 and 12.');
        }

        return $month;
    }

    /** Validate a year value. */
    protected function validateYear(int|string $year): int
    {
        if (! is_numeric($year)) {
            throw new InvalidArgumentException('Year must be numeric.');
        }

        $year = (int) $year;

        if ($year < 2000 || $year > 2100) {
            throw new InvalidArgumentException('Year must be between 2000 and 2100.');
        }

        return $year;
    }

    /** Validate a positive integer ID. */
    protected function validatePositiveId(int $id, string $label): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("{$label} must be a positive integer.");
        }
    }
}
