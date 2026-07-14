<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\LeavePolicyServiceInterface;
use App\Models\CompanySetting;
use App\Models\EmployeeLeaveBalance;
use App\Models\Holiday;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

class LeavePolicyService implements LeavePolicyServiceInterface
{
    public function __construct(
        protected EmployeeLeaveBalance $employeeLeaveBalance,
        protected LeaveType $leaveType,
        protected User $user,
        protected CompanySetting $companySetting,
        protected Holiday $holiday
    ) {
    }

    public function currentFinancialYear(?Carbon $date = null): string
    {
        $date = ($date ?? Carbon::today())->copy();
        $startYear = $date->month >= 4 ? $date->year : $date->year - 1;

        return $startYear . '-' . ($startYear + 1);
    }

    public function getFinancialYear(?Carbon $date = null): string
    {
        return $this->currentFinancialYear($date);
    }

    public function financialYearStart(?string $financialYear = null): Carbon
    {
        [$startYear] = $this->parseFinancialYear($financialYear ?? $this->currentFinancialYear());

        return Carbon::create($startYear, 4, 1)->startOfDay();
    }

    public function financialYearEnd(?string $financialYear = null): Carbon
    {
        [, $endYear] = $this->parseFinancialYear($financialYear ?? $this->currentFinancialYear());

        return Carbon::create($endYear, 3, 31)->endOfDay();
    }

    public function allocateLeave(?int $employeeId = null, ?string $financialYear = null): Collection
    {
        return $employeeId === null
            ? $this->allocateFinancialYear($financialYear)
            : $this->allocateEmployee($employeeId, $financialYear);
    }

    public function allocateFinancialYear(?string $financialYear = null): Collection
    {
        return $this->generateFinancialYearBalances($financialYear);
    }

    public function generateFinancialYearBalances(?string $financialYear = null): Collection
    {
        $financialYear = $financialYear ?? $this->currentFinancialYear();
        $balances = collect();

        $this->employeeQuery()
            ->chunkById(100, function (Collection $employees) use ($financialYear, $balances): void {
                foreach ($employees as $employee) {
                    $balances->push(...$this->allocateEmployee((int) $employee->id, $financialYear)->all());
                }
            });

        return $balances;
    }

    public function allocateEmployee(int $employeeId, ?string $financialYear = null, bool $forceRecalculate = false): Collection
    {
        $financialYear = $financialYear ?? $this->currentFinancialYear();
        $employee = $this->activeEmployee($employeeId);

        if (! $employee instanceof User) {
            return collect();
        }

        return $this->leaveType->active()->get()
            ->map(fn (LeaveType $leaveType): EmployeeLeaveBalance => $this->allocateLeaveType(
                (int) $employee->id,
                (int) $leaveType->id,
                $financialYear,
                $forceRecalculate
            ));
    }

    public function allocateLeaveType(int $employeeId, int $leaveTypeId, ?string $financialYear = null, bool $forceRecalculate = false): EmployeeLeaveBalance
    {
        $financialYear = $financialYear ?? $this->currentFinancialYear();
        $employee = $this->activeEmployee($employeeId);

        if (! $employee instanceof User) {
            throw new RuntimeException("Active employee [{$employeeId}] was not found.");
        }

        $leaveType = $this->findActiveLeaveType($leaveTypeId);
        $allocated = $this->allocateProrataForEmployee($employee, $leaveType, $financialYear);
        $carryForward = $this->resolveCarryForward((int) $employee->id, $leaveType, $financialYear);
        $balance = $this->employeeLeaveBalance->firstOrNew([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'financial_year' => $financialYear,
        ]);

        $used = (float) ($balance->exists ? $balance->used : 0);
        $nextValues = [
            'allocated' => $allocated,
            'used' => $this->roundDays($used),
            'carry_forward' => $carryForward,
            'remaining' => $this->roundDays($allocated + $carryForward - $used),
        ];

        if (! $balance->exists || $forceRecalculate || $this->balanceValuesChanged($balance, $nextValues)) {
            $balance->fill($nextValues);
            $balance->save();
        }

        return $balance->refresh()->load('leaveType');
    }

    public function allocateProrataLeave(int $employeeId, int $leaveTypeId, ?string $financialYear = null): float
    {
        $employee = $this->activeEmployee($employeeId);
        if (! $employee instanceof User) {
            throw new RuntimeException("Active employee [{$employeeId}] was not found.");
        }

        return $this->allocateProrataForEmployee($employee, $this->findActiveLeaveType($leaveTypeId), $financialYear ?? $this->currentFinancialYear());
    }

    public function carryForwardEarnLeave(int $employeeId, string $fromFinancialYear, string $toFinancialYear): float
    {
        $employee = $this->activeEmployee($employeeId);
        if (! $employee instanceof User) {
            return 0.0;
        }

        return $this->leaveType->active()->get()
            ->filter(fn (LeaveType $leaveType): bool => $this->isEarnLeave($leaveType))
            ->sum(fn (LeaveType $leaveType): float => $this->carryForward($employeeId, (int) $leaveType->id, $fromFinancialYear, $toFinancialYear));
    }

    public function resetFinancialYear(string $fromFinancialYear, string $toFinancialYear): Collection
    {
        $this->parseFinancialYear($fromFinancialYear);
        $this->parseFinancialYear($toFinancialYear);

        $balances = collect();
        $this->employeeQuery()
            ->chunkById(100, function (Collection $employees) use ($toFinancialYear, $balances): void {
                foreach ($employees as $employee) {
                    $balances->push(...$this->allocateEmployee((int) $employee->id, $toFinancialYear, true)->all());
                }
            });

        return $balances;
    }

    public function calculateRemaining(int $employeeId, int $leaveTypeId, ?string $financialYear = null): float
    {
        $balance = $this->ensureBalance($employeeId, $leaveTypeId, $financialYear);

        return (float) $balance->remaining;
    }

    public function consumeLeave(int $employeeId, int $leaveTypeId, float $days, ?string $financialYear = null): EmployeeLeaveBalance
    {
        $this->validateDays($days);
        $balance = $this->ensureBalance($employeeId, $leaveTypeId, $financialYear);

        if ((float) $balance->remaining < $days) {
            throw new RuntimeException('Insufficient leave balance.');
        }

        $balance->used = $this->roundDays((float) $balance->used + $days);
        $balance->remaining = $this->roundDays((float) $balance->allocated + (float) $balance->carry_forward - (float) $balance->used);
        $balance->save();

        return $balance->refresh()->load('leaveType');
    }

    public function restoreLeave(int $employeeId, int $leaveTypeId, float $days, ?string $financialYear = null): EmployeeLeaveBalance
    {
        $this->validateDays($days);
        $balance = $this->ensureBalance($employeeId, $leaveTypeId, $financialYear);

        $balance->used = $this->roundDays(max(0, (float) $balance->used - $days));
        $balance->remaining = $this->roundDays((float) $balance->allocated + (float) $balance->carry_forward - (float) $balance->used);
        $balance->save();

        return $balance->refresh()->load('leaveType');
    }

    public function carryForward(int $employeeId, int $leaveTypeId, string $fromFinancialYear, string $toFinancialYear): float
    {
        $leaveType = $this->findActiveLeaveType($leaveTypeId);
        if (! $this->isEarnLeave($leaveType)) {
            return 0.0;
        }

        $previous = $this->getBalance($employeeId, $leaveTypeId, $fromFinancialYear);
        if (! $previous instanceof EmployeeLeaveBalance) {
            return 0.0;
        }

        $current = $this->allocateLeaveType($employeeId, $leaveTypeId, $toFinancialYear, true);
        $current->carry_forward = $this->roundDays((float) $previous->remaining);
        $current->remaining = $this->roundDays((float) $current->allocated + (float) $current->carry_forward - (float) $current->used);
        $current->save();

        return (float) $current->carry_forward;
    }

    public function getBalance(int $employeeId, int $leaveTypeId, ?string $financialYear = null): ?EmployeeLeaveBalance
    {
        $this->validateEmployee($employeeId);
        $this->findLeaveType($leaveTypeId);
        $financialYear = $financialYear ?? $this->currentFinancialYear();

        return $this->employeeLeaveBalance
            ->with('leaveType')
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('financial_year', $financialYear)
            ->first();
    }

    public function getEmployeeBalances(int $employeeId, ?string $financialYear = null): Collection
    {
        $financialYear = $financialYear ?? $this->currentFinancialYear();
        $this->validateEmployee($employeeId);
        $this->allocateEmployee($employeeId, $financialYear);

        return $this->employeeLeaveBalance
            ->with('leaveType')
            ->where('employee_id', $employeeId)
            ->where('financial_year', $financialYear)
            ->orderBy('leave_type_id')
            ->get();
    }

    public function getBalanceResponse(int $employeeId, ?string $financialYear = null): Collection
    {
        return $this->getEmployeeBalances($employeeId, $financialYear)
            ->map(fn (EmployeeLeaveBalance $balance): array => [
                'leave_type_id' => $balance->leave_type_id,
                'leave_type' => $balance->leaveType?->leave_name ?? '-',
                'allocated' => (float) $balance->allocated,
                'used' => (float) $balance->used,
                'remaining' => (float) $balance->remaining,
                'carry_forward' => (float) $balance->carry_forward,
                'financial_year' => $balance->financial_year,
            ]);
    }

    public function calculateLeaveDuration(Carbon $from, Carbon $to, array $options = []): array
    {
        $this->validateDateRange($from, $to);
        $settings = $this->companySetting->first();
        $halfDay = $this->isHalfDayRequest($options);
        $halfDayType = $this->halfDayType($options);
        $allowHalfDay = (bool) ($settings?->allow_half_day_leave ?? true);

        if ($halfDay) {
            $this->validateHalfDayRequest($from, $to, $allowHalfDay, $halfDayType);
        }

        $weeklyOffs = $this->weeklyOffNames((string) ($settings?->weekly_off ?? 'Sunday'));
        $holidayDates = $this->calculateHolidayDays($from, $to);
        $weeklyOffDates = array_values(array_diff($this->calculateWeeklyOffDays($from, $to, $weeklyOffs), $holidayDates));
        $requestedDays = $this->calculateRequestedDays($from, $to, $options);
        $countHolidays = (bool) ($settings?->holiday_between_leave_count ?? true);
        $countWeeklyOffs = (bool) ($settings?->weekly_off_between_leave_count ?? true);
        $sandwichEnabled = (bool) ($settings?->sandwich_leave_enabled ?? false);
        $sandwichDates = $this->calculateSandwichDays($holidayDates, $weeklyOffDates, [
            'from' => $from,
            'to' => $to,
            'half_day' => $halfDay,
            'sandwich_enabled' => $sandwichEnabled,
            'count_holidays_in_leave' => $countHolidays,
            'count_weekly_offs_in_leave' => $countWeeklyOffs,
        ]);
        $allDates = $halfDay ? [$from->toDateString()] : $this->dateRange($from, $to);
        $nonWorkingDates = array_values(array_unique(array_merge($holidayDates, $weeklyOffDates)));
        $workingLeaveDays = $halfDay ? 0.5 : count(array_diff($allDates, $nonWorkingDates));

        $duration = [
            'requested_days' => $this->roundDays($requestedDays),
            'holiday_days' => $this->roundDays((float) ($countHolidays ? count($holidayDates) : 0)),
            'weekly_off_days' => $this->roundDays((float) ($countWeeklyOffs ? count($weeklyOffDates) : 0)),
            'sandwich_days' => $this->roundDays((float) count($sandwichDates)),
            'working_leave_days' => $this->roundDays((float) $workingLeaveDays),
            'requested_dates' => array_values($allDates),
            'holiday_dates' => array_values($holidayDates),
            'weekly_off_dates' => array_values($weeklyOffDates),
            'sandwich_dates' => array_values($sandwichDates),
            'office_weekly_offs' => $weeklyOffs,
            'sandwich_enabled' => $sandwichEnabled,
            'count_holidays_in_leave' => $countHolidays,
            'count_weekly_offs_in_leave' => $countWeeklyOffs,
            'allow_half_day_leave' => $allowHalfDay,
            'half_day' => $halfDay,
            'half_day_type' => $halfDay ? $halfDayType : null,
        ];
        $duration['payable_leave_days'] = $this->calculatePayableLeaveDays($duration);

        return $duration;
    }

    public function calculateLiveLeave(array $data): array
    {
        $employeeId = (int) ($data['employee_id'] ?? $data['user_id'] ?? 0);
        $leaveTypeId = (int) ($data['leave_type_id'] ?? 0);
        if ($employeeId <= 0) {
            throw new InvalidArgumentException('Employee is required.');
        }
        if ($leaveTypeId <= 0) {
            throw new InvalidArgumentException('Leave type is required.');
        }

        $employee = $this->activeEmployee($employeeId);
        if (! $employee instanceof User) {
            throw new RuntimeException("Active employee [{$employeeId}] was not found.");
        }

        $leaveType = $this->findActiveLeaveType($leaveTypeId);
        $from = $this->parseCalculationDate($data['from_date'] ?? null, 'From date');
        $to = $this->parseCalculationDate($data['to_date'] ?? null, 'To date');
        $duration = $this->calculateLeaveDuration($from, $to, $data);
        $financialYear = $this->currentFinancialYear($from);
        $payable = (float) $duration['payable_leave_days'];
        $withoutPay = $this->leaveTypeIsWithoutPay($leaveType);
        $remaining = null;
        $balanceAfterApproval = null;
        $warning = null;

        if (! $withoutPay) {
            $remaining = $this->calculateRemaining($employeeId, $leaveTypeId, $financialYear);
            $balanceAfterApproval = $this->roundDays($remaining - $payable);
            if ($payable > $remaining) {
                $warning = 'Insufficient Leave Balance';
            }
        }

        return array_merge($duration, [
            'employee_id' => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'leave_type' => $leaveType->leave_name ?? '-',
            'financial_year' => $financialYear,
            'remaining_balance' => $remaining,
            'balance_after_approval' => $balanceAfterApproval,
            'warning' => $warning,
            'can_submit' => $warning === null,
            'insufficient_balance' => $warning !== null,
            'leave_without_pay' => $withoutPay,
            'balance_validation_skipped' => $withoutPay,
            'emergency_leave' => filter_var($data['emergency_leave'] ?? false, FILTER_VALIDATE_BOOL),
            'emergency_leave_label' => filter_var($data['emergency_leave'] ?? false, FILTER_VALIDATE_BOOL) ? 'YES' : 'NO',
            'half_day_session' => $duration['half_day_type'],
        ]);
    }

    public function isLeaveWithoutPay(int $leaveTypeId): bool
    {
        return $this->leaveTypeIsWithoutPay($this->findLeaveType($leaveTypeId));
    }
    public function calculateRequestedDays(Carbon $from, Carbon $to, array $options = []): float
    {
        $this->validateDateRange($from, $to);

        return $this->isHalfDayRequest($options)
            ? 0.5
            : (float) ($from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1);
    }

    public function calculateHolidayDays(Carbon $from, Carbon $to): array
    {
        return $this->holidayDatesBetween($from, $to);
    }

    public function calculateWeeklyOffDays(Carbon $from, Carbon $to, array $weeklyOffs): array
    {
        return $this->weeklyOffDatesBetween($from, $to, $weeklyOffs);
    }

    public function calculateSandwichDays(array $holidayDates, array $weeklyOffDates, array $options = []): array
    {
        if (! (bool) ($options['sandwich_enabled'] ?? false) || (bool) ($options['half_day'] ?? false)) {
            return [];
        }

        $from = $options['from'] ?? null;
        $to = $options['to'] ?? null;
        if (! $from instanceof Carbon || ! $to instanceof Carbon) {
            return [];
        }

        $countHolidays = (bool) ($options['count_holidays_in_leave'] ?? true);
        $countWeeklyOffs = (bool) ($options['count_weekly_offs_in_leave'] ?? true);
        $candidateDates = [];

        if (! $countHolidays) {
            $candidateDates = array_merge($candidateDates, $holidayDates);
        }
        if (! $countWeeklyOffs) {
            $candidateDates = array_merge($candidateDates, $weeklyOffDates);
        }

        $start = $from->copy()->addDay()->toDateString();
        $end = $to->copy()->subDay()->toDateString();

        return collect($candidateDates)
            ->unique()
            ->filter(fn (string $date): bool => $date >= $start && $date <= $end)
            ->sort()
            ->values()
            ->all();
    }

    public function calculatePayableLeaveDays(array $duration): float
    {
        $payable = (float) ($duration['working_leave_days'] ?? 0)
            + (float) ($duration['holiday_days'] ?? 0)
            + (float) ($duration['weekly_off_days'] ?? 0)
            + (float) ($duration['sandwich_days'] ?? 0);

        return $this->roundDays(max(0, $payable));
    }

    public function calculateLeaveDays(Carbon $from, Carbon $to): float
    {
        return $this->calculateRequestedDays($from, $to);
    }

    public function validateLeaveBalance(int $employeeId, int $leaveTypeId, float $requestedDays, ?string $financialYear = null): bool
    {
        if ($this->isLeaveWithoutPay($leaveTypeId)) {
            return true;
        }

        $this->validateDays($requestedDays);
        $remaining = $this->calculateRemaining($employeeId, $leaveTypeId, $financialYear);

        if ($remaining < $requestedDays) {
            throw new RuntimeException('Insufficient leave balance.');
        }

        return true;
    }

    public function prepareCalculationSnapshot(int $employeeId, int $leaveTypeId, Carbon $from, Carbon $to, array $options = []): array
    {
        $this->validateEmployee($employeeId);
        $leaveType = $this->findLeaveType($leaveTypeId);
        $duration = $this->calculateLeaveDuration($from, $to, $options);

        $snapshot = array_merge($duration, [
            'financial_year' => $this->currentFinancialYear($from),
            'leave_type' => $leaveType->leave_name ?? '-',
            'generated_at' => Carbon::now()->toIso8601String(),
        ]);

        return [
            'requested_days' => $snapshot['requested_days'],
            'holiday_days' => $snapshot['holiday_days'],
            'weekly_off_days' => $snapshot['weekly_off_days'],
            'sandwich_days' => $snapshot['sandwich_days'],
            'payable_leave_days' => $snapshot['payable_leave_days'],
            'leave_calculation_json' => $snapshot,
        ];
    }

    protected function ensureBalance(int $employeeId, int $leaveTypeId, ?string $financialYear = null): EmployeeLeaveBalance
    {
        $financialYear = $financialYear ?? $this->currentFinancialYear();
        $this->allocateLeaveType($employeeId, $leaveTypeId, $financialYear);
        $balance = $this->getBalance($employeeId, $leaveTypeId, $financialYear);

        if (! $balance instanceof EmployeeLeaveBalance) {
            throw new RuntimeException('Leave balance was not found.');
        }

        return $balance;
    }

    protected function employeeQuery(?int $employeeId = null): mixed
    {
        $query = $this->user->with('userDetail')->whereHas('userDetail', fn ($query) => $query->active());

        if ($employeeId !== null) {
            $this->validateEmployee($employeeId);
            $query->whereKey($employeeId);
        }

        return $query;
    }

    protected function activeEmployee(int $employeeId): ?User
    {
        $this->validateEmployee($employeeId);

        return $this->employeeQuery($employeeId)->first();
    }

    protected function allocateProrataForEmployee(User $employee, LeaveType $leaveType, string $financialYear): float
    {
        $start = $this->financialYearStart($financialYear);
        $end = $this->financialYearEnd($financialYear);
        $joiningDate = $employee->userDetail?->joining_date instanceof Carbon
            ? $employee->userDetail->joining_date->copy()->startOfDay()
            : $start->copy();

        if ($joiningDate->gt($end)) {
            return 0.0;
        }

        $eligibleStart = $joiningDate->greaterThan($start) ? $joiningDate->copy()->startOfMonth() : $start->copy();
        $eligibleEnd = $end->copy()->startOfMonth();
        $months = max(0, $eligibleStart->diffInMonths($eligibleEnd) + 1);

        return $this->roundDays($months * $this->monthlyAllocation($leaveType));
    }

    protected function monthlyAllocation(LeaveType $leaveType): float
    {
        $monthly = (float) ($leaveType->monthly_allocation ?? 0);
        if ($monthly > 0) {
            return $monthly;
        }

        return $this->roundDays($this->annualAllocation($leaveType) / 12);
    }

    protected function annualAllocation(LeaveType $leaveType): float
    {
        $annual = (float) ($leaveType->annual_allocation ?? 0);

        return $annual > 0 ? $annual : (float) $leaveType->total_days;
    }

    protected function resolveCarryForward(int $employeeId, LeaveType $leaveType, string $financialYear): float
    {
        if (! $this->isEarnLeave($leaveType)) {
            return 0.0;
        }

        $previous = $this->getBalance($employeeId, (int) $leaveType->id, $this->previousFinancialYear($financialYear));

        return $previous instanceof EmployeeLeaveBalance ? $this->roundDays((float) $previous->remaining) : 0.0;
    }

    protected function previousFinancialYear(string $financialYear): string
    {
        [$startYear, $endYear] = $this->parseFinancialYear($financialYear);

        return ($startYear - 1) . '-' . ($endYear - 1);
    }

    protected function parseFinancialYear(string $financialYear): array
    {
        if (preg_match('/^(\d{4})-(\d{4})$/', $financialYear, $matches) !== 1) {
            throw new InvalidArgumentException('Financial year must use YYYY-YYYY format.');
        }

        $startYear = (int) $matches[1];
        $endYear = (int) $matches[2];

        if ($endYear !== $startYear + 1) {
            throw new InvalidArgumentException('Financial year end must be the next calendar year.');
        }

        return [$startYear, $endYear];
    }

    protected function validateEmployee(int $employeeId): void
    {
        if ($employeeId <= 0 || ! $this->user->whereKey($employeeId)->exists()) {
            throw new RuntimeException("Employee [{$employeeId}] was not found.");
        }
    }

    protected function findLeaveType(int $leaveTypeId): LeaveType
    {
        if ($leaveTypeId <= 0) {
            throw new InvalidArgumentException('Leave type ID must be a positive integer.');
        }

        $leaveType = $this->leaveType->find($leaveTypeId);
        if (! $leaveType instanceof LeaveType) {
            throw new RuntimeException("Leave type [{$leaveTypeId}] was not found.");
        }

        return $leaveType;
    }

    protected function findActiveLeaveType(int $leaveTypeId): LeaveType
    {
        $leaveType = $this->findLeaveType($leaveTypeId);
        if (! (bool) $leaveType->status) {
            throw new RuntimeException("Active leave type [{$leaveTypeId}] was not found.");
        }

        return $leaveType;
    }

    protected function isEarnLeave(LeaveType $leaveType): bool
    {
        $code = strtoupper(trim((string) $leaveType->leave_code));
        $name = strtolower(trim((string) $leaveType->leave_name));

        return in_array($code, ['EL', 'EARN', 'EARNED'], true) || str_contains($name, 'earn');
    }

    protected function balanceValuesChanged(EmployeeLeaveBalance $balance, array $values): bool
    {
        foreach ($values as $key => $value) {
            if ($this->roundDays((float) $balance->{$key}) !== $this->roundDays((float) $value)) {
                return true;
            }
        }

        return false;
    }

    protected function parseCalculationDate(mixed $value, string $label): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy()->startOfDay();
        }
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("{$label} is required.");
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            throw new InvalidArgumentException("{$label} must be a valid date.");
        }
    }

    protected function leaveTypeIsWithoutPay(LeaveType $leaveType): bool
    {
        $code = strtoupper(trim((string) $leaveType->leave_code));
        $name = strtolower(trim((string) $leaveType->leave_name));

        $isPaid = $leaveType->getRawOriginal('is_paid');

        return in_array($code, ['LWP', 'LOP', 'LWOP'], true)
            || str_contains($name, 'without pay')
            || str_contains($name, 'loss of pay')
            || str_contains($name, 'unpaid')
            || ($isPaid !== null && (bool) $leaveType->is_paid === false);
    }
    protected function weeklyOffNames(string $weeklyOff): array
    {
        return collect(preg_split('/[,+|\\/]+/', $weeklyOff) ?: [])
            ->map(fn (string $day): string => ucfirst(strtolower(trim($day))))
            ->filter(fn (string $day): bool => $day !== '')
            ->values()
            ->all();
    }

    protected function holidayDatesBetween(Carbon $from, Carbon $to): array
    {
        $dates = collect();
        $holidays = $this->holiday
            ->newQueryWithoutScopes()
            ->where('status', 1)
            ->whereDate('from_date', '<=', $to->toDateString())
            ->whereDate('to_date', '>=', $from->toDateString())
            ->get();

        foreach ($holidays as $holiday) {
            $start = $holiday->from_date->copy()->max($from);
            $end = $holiday->to_date->copy()->min($to);
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $dates->push($date->toDateString());
            }
        }

        return $dates->unique()->sort()->values()->all();
    }

    protected function weeklyOffDatesBetween(Carbon $from, Carbon $to, array $weeklyOffs): array
    {
        $dates = collect();
        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            if (in_array($date->englishDayOfWeek, $weeklyOffs, true)) {
                $dates->push($date->toDateString());
            }
        }

        return $dates->values()->all();
    }

    protected function dateRange(Carbon $from, Carbon $to): array
    {
        $dates = [];
        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $dates[] = $date->toDateString();
        }

        return $dates;
    }

    protected function validateDateRange(Carbon $from, Carbon $to): void
    {
        if ($to->lessThan($from)) {
            throw new InvalidArgumentException('Leave to date must be greater than or equal to from date.');
        }
    }

    protected function isHalfDayRequest(array $options): bool
    {
        $value = $options['is_half_day'] ?? $options['half_day'] ?? false;

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    protected function halfDayType(array $options): ?string
    {
        $type = $options['half_day_type'] ?? $options['half_day_session'] ?? 'first_half';

        return is_string($type) ? strtolower(trim($type)) : null;
    }

    protected function validateHalfDayRequest(Carbon $from, Carbon $to, bool $allowHalfDay, ?string $halfDayType): void
    {
        if (! $allowHalfDay) {
            throw new InvalidArgumentException('Half day leave is not allowed by company policy.');
        }

        if (! $from->isSameDay($to)) {
            throw new InvalidArgumentException('Half day leave can only be applied for a single date.');
        }

        if (! in_array($halfDayType, ['first_half', 'second_half'], true)) {
            throw new InvalidArgumentException('Half day type must be first_half or second_half.');
        }
    }

    protected function validateDays(float $days): void
    {
        if ($days <= 0) {
            throw new InvalidArgumentException('Leave days must be greater than zero.');
        }
    }

    protected function roundDays(float $days): float
    {
        return round($days, 2);
    }
}









