<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\FinancialYearClosingServiceInterface;
use App\Contracts\LeavePolicyServiceInterface;
use App\Contracts\NotificationServiceInterface;
use App\Models\CompanySetting;
use App\Models\EmployeeLeaveBalance;
use App\Models\FinancialYearArchive;
use App\Models\FinancialYearClosing;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinancialYearClosingService implements FinancialYearClosingServiceInterface
{
    public function __construct(
        protected LeavePolicyServiceInterface $leavePolicyService,
        protected NotificationServiceInterface $notificationService,
        protected CompanySetting $companySetting,
        protected LeaveType $leaveType,
        protected User $user,
        protected EmployeeLeaveBalance $balance,
        protected FinancialYearClosing $closing,
        protected FinancialYearArchive $archive
    ) {}

    public function dashboard(?string $financialYear = null): array
    {
        $financialYear = $this->financialYear($financialYear);
        $latest = $this->closing->newQuery()->with('closedBy')->latest('closed_at')->latest('id')->first();
        $preview = $this->preview($financialYear);
        $closed = $this->closedRecord($financialYear);

        return [
            'financial_year' => $financialYear,
            'next_financial_year' => $this->nextFinancialYear($financialYear),
            'status' => $closed ? 'Closed' : 'Open',
            'total_employees' => $preview['summary']['employees'],
            'processed' => $closed?->employees_processed ?? 0,
            'pending' => $closed ? 0 : $preview['summary']['employees'],
            'carry_forward_employees' => $preview['summary']['carry_forward'],
            'last_closed_date' => $latest?->closed_at?->format('d M Y') ?? '-',
            'latest' => $latest,
            'summary' => $preview['summary'],
            'widget' => [
                'Current FY' => $this->nextFinancialYear($financialYear),
                'Last Closing' => $latest?->closed_at?->format('d M Y') ?? '-',
                'Status' => $closed ? 'Closed' : 'Open',
            ],
        ];
    }

    public function preview(?string $financialYear = null): array
    {
        $financialYear = $this->financialYear($financialYear);
        $this->validateCanPreview($financialYear);
        $nextFinancialYear = $this->nextFinancialYear($financialYear);
        $leaveTypes = $this->typedLeaveTypes();
        $rows = collect();
        $summary = ['employees' => 0, 'processed' => 0, 'skipped' => 0, 'inactive' => 0, 'carry_forward' => 0, 'reset' => 0, 'errors' => 0];

        $this->employeesWithDetails()->chunkById(100, function (Collection $employees) use (&$rows, &$summary, $financialYear, $nextFinancialYear, $leaveTypes): void {
            foreach ($employees as $employee) {
                $summary['employees']++;
                if (! (bool) $employee->userDetail?->status) {
                    $summary['inactive']++;
                    $summary['skipped']++;
                    $rows->push($this->previewRow($employee, $financialYear, $nextFinancialYear, $leaveTypes, 'Inactive - skipped'));
                    continue;
                }

                $row = $this->previewRow($employee, $financialYear, $nextFinancialYear, $leaveTypes, 'Ready');
                $summary['processed']++;
                $summary['reset'] += 2;
                if ((float) $row['carry_forward_el'] > 0) {
                    $summary['carry_forward']++;
                }
                $rows->push($row);
            }
        });

        return [
            'financial_year' => $financialYear,
            'next_financial_year' => $nextFinancialYear,
            'rows' => $rows,
            'summary' => $summary,
            'closed' => $this->closedRecord($financialYear) !== null,
        ];
    }

    public function close(string $financialYear, User $actor, ?string $ipAddress = null): FinancialYearClosing
    {
        $financialYear = $this->financialYear($financialYear);
        $this->validateCanClose($financialYear);
        $nextFinancialYear = $this->nextFinancialYear($financialYear);
        $started = microtime(true);
        $now = Carbon::now();
        $log = [];
        $summary = ['employees' => 0, 'processed' => 0, 'skipped' => 0, 'inactive' => 0, 'carry_forward' => 0, 'reset' => 0, 'errors' => 0];

        $closing = DB::transaction(function () use ($financialYear, $nextFinancialYear, $actor, $ipAddress, $started, $now, &$log, &$summary): FinancialYearClosing {
            if ($this->closedRecord($financialYear)) {
                throw new RuntimeException('Financial Year already closed.');
            }

            $closing = $this->closing->newQuery()->create([
                'financial_year' => $financialYear,
                'next_financial_year' => $nextFinancialYear,
                'status' => FinancialYearClosing::STATUS_CLOSED,
                'closed_by' => $actor->id,
                'closed_at' => $now,
                'ip_address' => $ipAddress,
            ]);

            $leaveTypes = $this->typedLeaveTypes();
            $this->employeesWithDetails()->chunkById(100, function (Collection $employees) use ($closing, $financialYear, $nextFinancialYear, $actor, $now, $leaveTypes, &$log, &$summary): void {
                foreach ($employees as $employee) {
                    $summary['employees']++;
                    if (! (bool) $employee->userDetail?->status) {
                        $summary['inactive']++;
                        $summary['skipped']++;
                        $log[] = $this->logLine($employee, 'Skipped inactive employee');
                        continue;
                    }

                    $this->archiveEmployee($closing, $employee, $financialYear, $actor, $now);
                    $newBalances = $this->leavePolicyService->allocateEmployee((int) $employee->id, $nextFinancialYear, true);
                    $carryForward = $this->applyCarryForwardLimit($employee, $financialYear, $nextFinancialYear, $leaveTypes);
                    $summary['processed']++;
                    $summary['reset'] += 2;
                    if ($carryForward > 0) {
                        $summary['carry_forward']++;
                    }
                    $log[] = $this->logLine($employee, 'Processed', ['new_balances' => $newBalances->count(), 'carry_forward' => $carryForward]);
                }
            });

            $closing->fill([
                'employees_processed' => $summary['processed'],
                'employees_skipped' => $summary['skipped'],
                'inactive_employees' => $summary['inactive'],
                'carry_forward_count' => $summary['carry_forward'],
                'reset_count' => $summary['reset'],
                'error_count' => $summary['errors'],
                'execution_time_ms' => (int) round((microtime(true) - $started) * 1000),
                'summary' => $summary,
                'execution_log' => $log,
                'audit_timeline' => [[
                    'action' => 'Closed',
                    'by' => $actor->name,
                    'user_id' => $actor->id,
                    'at' => $now->toIso8601String(),
                    'ip' => $closing->ip_address,
                ]],
            ])->save();

            return $closing->refresh()->load('closedBy');
        });

        $this->notifyClosed($closing, $actor);

        return $closing;
    }

    public function reopen(int $closingId, User $actor, ?string $ipAddress = null): FinancialYearClosing
    {
        if (! $this->hasAnyRole($actor, ['Admin'])) {
            throw new RuntimeException('Only Admin users can reopen a financial year.');
        }

        return DB::transaction(function () use ($closingId, $actor, $ipAddress): FinancialYearClosing {
            $closing = $this->closing->newQuery()->lockForUpdate()->findOrFail($closingId);
            if ($closing->status !== FinancialYearClosing::STATUS_CLOSED) {
                throw new RuntimeException('Only a closed financial year can be reopened.');
            }

            $timeline = is_array($closing->audit_timeline) ? $closing->audit_timeline : [];
            $timeline[] = [
                'action' => 'Reopened',
                'by' => $actor->name,
                'user_id' => $actor->id,
                'at' => Carbon::now()->toIso8601String(),
                'ip' => $ipAddress,
            ];

            $closing->fill([
                'status' => FinancialYearClosing::STATUS_REOPENED,
                'reopened_by' => $actor->id,
                'reopened_at' => Carbon::now(),
                'audit_timeline' => $timeline,
            ])->save();

            return $closing->refresh()->load(['closedBy', 'reopenedBy']);
        });
    }

    public function history(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->closing->newQuery()->with('closedBy')->latest('closed_at')->latest('id');
        if (($filters['financial_year'] ?? '') !== '') {
            $query->where('financial_year', trim((string) $filters['financial_year']));
        }
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', trim((string) $filters['status']));
        }

        return $query->paginate($this->perPage($perPage));
    }

    public function show(int $closingId): array
    {
        $closing = $this->closing->newQuery()->with(['closedBy', 'reopenedBy', 'archives.employee.userDetail', 'archives.leaveType'])->findOrFail($closingId);

        return [
            'closing' => $closing,
            'summary' => $closing->summary ?? [],
            'carry_forward' => $closing->archives->where('carry_forward', '>', 0)->count(),
            'reset_summary' => $closing->summary['reset'] ?? 0,
            'employee_count' => $closing->archives->pluck('employee_id')->unique()->count(),
            'execution_log' => $closing->execution_log ?? [],
            'audit_timeline' => $closing->audit_timeline ?? [],
            'archives' => $closing->archives->map(fn (FinancialYearArchive $archive): array => [
                'employee' => $this->employeeName($archive->employee),
                'department' => $archive->employee?->userDetail?->department ?? '-',
                'leave_type' => $archive->leaveType?->leave_name ?? '-',
                'opening_balance' => (float) $archive->opening_balance,
                'consumed' => (float) $archive->consumed,
                'remaining' => (float) $archive->remaining,
                'carry_forward' => (float) $archive->carry_forward,
                'closing_balance' => (float) $archive->closing_balance,
            ])->values(),
        ];
    }

    protected function archiveEmployee(FinancialYearClosing $closing, User $employee, string $financialYear, User $actor, Carbon $now): void
    {
        $this->balance->newQuery()
            ->with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('financial_year', $financialYear)
            ->get()
            ->each(function (EmployeeLeaveBalance $balance) use ($closing, $financialYear, $actor, $now): void {
                $this->archive->newQuery()->updateOrCreate([
                    'financial_year_closing_id' => $closing->id,
                    'employee_id' => $balance->employee_id,
                    'leave_type_id' => $balance->leave_type_id,
                ], [
                    'financial_year' => $financialYear,
                    'opening_balance' => round((float) $balance->allocated + (float) $balance->carry_forward, 2),
                    'allocated' => (float) $balance->allocated,
                    'consumed' => (float) $balance->used,
                    'remaining' => (float) $balance->remaining,
                    'carry_forward' => $this->carryForwardForBalance($balance),
                    'closing_balance' => (float) $balance->remaining,
                    'generated_at' => $now,
                    'generated_by' => $actor->id,
                ]);
            });
    }

    protected function applyCarryForwardLimit(User $employee, string $financialYear, string $nextFinancialYear, array $leaveTypes): float
    {
        $carryForward = 0.0;
        foreach (['cl', 'sl'] as $key) {
            if ($leaveTypes[$key] instanceof LeaveType) {
                $balance = $this->balanceFor($employee, $leaveTypes[$key], $nextFinancialYear);
                if ($balance) {
                    $balance->carry_forward = 0;
                    $balance->remaining = round((float) $balance->allocated - (float) $balance->used, 2);
                    $balance->save();
                }
            }
        }

        if ($leaveTypes['el'] instanceof LeaveType) {
            $previous = $this->balanceFor($employee, $leaveTypes['el'], $financialYear);
            $current = $this->balanceFor($employee, $leaveTypes['el'], $nextFinancialYear);
            if ($current) {
                $carryForward = $previous ? $this->carryForwardAmount($leaveTypes['el'], (float) $previous->remaining) : 0.0;
                $current->carry_forward = $carryForward;
                $current->remaining = round((float) $current->allocated + $carryForward - (float) $current->used, 2);
                $current->save();
            }
        }

        return $carryForward;
    }

    protected function previewRow(User $employee, string $financialYear, string $nextFinancialYear, array $leaveTypes, string $status): array
    {
        $cl = $this->balanceFor($employee, $leaveTypes['cl'], $financialYear);
        $sl = $this->balanceFor($employee, $leaveTypes['sl'], $financialYear);
        $el = $this->balanceFor($employee, $leaveTypes['el'], $financialYear);

        return [
            'employee' => $this->employeeName($employee),
            'department' => $employee->userDetail?->department ?? '-',
            'cl_current' => $cl ? (float) $cl->remaining : 0,
            'sl_current' => $sl ? (float) $sl->remaining : 0,
            'el_current' => $el ? (float) $el->remaining : 0,
            'carry_forward_el' => $el ? $this->carryForwardAmount($leaveTypes['el'], (float) $el->remaining) : 0,
            'new_cl' => $leaveTypes['cl'] ? $this->previewAllocation($employee, $leaveTypes['cl'], $nextFinancialYear) : 0,
            'new_sl' => $leaveTypes['sl'] ? $this->previewAllocation($employee, $leaveTypes['sl'], $nextFinancialYear) : 0,
            'new_el' => $leaveTypes['el'] ? $this->previewAllocation($employee, $leaveTypes['el'], $nextFinancialYear) + ($el ? $this->carryForwardAmount($leaveTypes['el'], (float) $el->remaining) : 0) : 0,
            'status' => $status,
        ];
    }

    protected function previewAllocation(User $employee, ?LeaveType $leaveType, string $financialYear): float
    {
        if (! $leaveType instanceof LeaveType || ! (bool) $employee->userDetail?->status) {
            return 0.0;
        }

        return $this->leavePolicyService->allocateProrataLeave((int) $employee->id, (int) $leaveType->id, $financialYear);
    }

    protected function validateCanPreview(string $financialYear): void
    {
        if (! $this->companySetting->newQuery()->exists()) {
            throw new RuntimeException('Company Settings incomplete.');
        }

        $types = $this->typedLeaveTypes();
        foreach (['cl' => 'Casual Leave', 'sl' => 'Sick Leave', 'el' => 'Earned Leave'] as $key => $label) {
            if (! $types[$key] instanceof LeaveType) {
                throw new RuntimeException($label . ' leave type is missing.');
            }
        }

        $this->leavePolicyService->financialYearStart($financialYear);
        $this->leavePolicyService->financialYearEnd($financialYear);
    }

    protected function validateCanClose(string $financialYear): void
    {
        $this->validateCanPreview($financialYear);
        if ($this->closedRecord($financialYear)) {
            throw new RuntimeException('Financial Year already closed.');
        }

        $previous = $this->previousFinancialYear($financialYear);
        $previousClosing = $this->closing->newQuery()->where('financial_year', $previous)->first();
        if ($previousClosing && $previousClosing->status !== FinancialYearClosing::STATUS_CLOSED) {
            throw new RuntimeException('Previous closing incomplete.');
        }
    }

    protected function closedRecord(string $financialYear): ?FinancialYearClosing
    {
        return $this->closing->newQuery()
            ->where('financial_year', $financialYear)
            ->where('status', FinancialYearClosing::STATUS_CLOSED)
            ->first();
    }

    protected function typedLeaveTypes(): array
    {
        $types = $this->leaveType->newQuery()->active()->get();

        return [
            'cl' => $types->first(fn (LeaveType $type): bool => $this->typeMatches($type, ['CL'], ['casual'])),
            'sl' => $types->first(fn (LeaveType $type): bool => $this->typeMatches($type, ['SL'], ['sick'])),
            'el' => $types->first(fn (LeaveType $type): bool => $this->typeMatches($type, ['EL', 'EARN', 'EARNED'], ['earn'])),
        ];
    }

    protected function typeMatches(LeaveType $type, array $codes, array $names): bool
    {
        $code = strtoupper(trim((string) $type->leave_code));
        $name = strtolower(trim((string) $type->leave_name));

        return in_array($code, $codes, true) || collect($names)->contains(fn (string $needle): bool => str_contains($name, $needle));
    }

    protected function carryForwardForBalance(EmployeeLeaveBalance $balance): float
    {
        if (! $balance->leaveType instanceof LeaveType || ! $this->typeMatches($balance->leaveType, ['EL', 'EARN', 'EARNED'], ['earn'])) {
            return 0.0;
        }

        return $this->carryForwardAmount($balance->leaveType, (float) $balance->remaining);
    }

    protected function carryForwardAmount(?LeaveType $leaveType, float $remaining): float
    {
        if (! $leaveType instanceof LeaveType || ! (bool) ($leaveType->carry_forward_enabled ?? false)) {
            return 0.0;
        }

        $limit = (float) ($leaveType->carry_forward_limit ?? 0);

        return round($limit > 0 ? min($remaining, $limit) : $remaining, 2);
    }

    protected function balanceFor(User $employee, ?LeaveType $leaveType, string $financialYear): ?EmployeeLeaveBalance
    {
        if (! $leaveType instanceof LeaveType) {
            return null;
        }

        return $this->balance->newQuery()
            ->with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('financial_year', $financialYear)
            ->first();
    }

    protected function employeesWithDetails(): Builder
    {
        return $this->user->newQuery()->with('userDetail')->whereHas('userDetail')->orderBy('id');
    }

    protected function financialYear(?string $financialYear): string
    {
        $financialYear = trim((string) $financialYear);
        return $financialYear !== '' ? $financialYear : $this->leavePolicyService->currentFinancialYear();
    }

    protected function nextFinancialYear(string $financialYear): string
    {
        [$start, $end] = $this->parseFinancialYear($financialYear);
        return ($start + 1) . '-' . ($end + 1);
    }

    protected function previousFinancialYear(string $financialYear): string
    {
        [$start, $end] = $this->parseFinancialYear($financialYear);
        return ($start - 1) . '-' . ($end - 1);
    }

    protected function parseFinancialYear(string $financialYear): array
    {
        if (preg_match('/^(\d{4})-(\d{4})$/', $financialYear, $matches) !== 1) {
            throw new RuntimeException('Financial year must use YYYY-YYYY format.');
        }

        $start = (int) $matches[1];
        $end = (int) $matches[2];
        if ($end !== $start + 1) {
            throw new RuntimeException('Financial year end must be the next calendar year.');
        }

        return [$start, $end];
    }

    protected function employeeName(?User $employee): string
    {
        if (! $employee) {
            return '-';
        }

        $name = trim((string) ($employee->userDetail?->first_name . ' ' . $employee->userDetail?->last_name));
        return $name !== '' ? $name : (string) ($employee->name ?: '-');
    }

    protected function logLine(User $employee, string $message, array $context = []): array
    {
        return [
            'employee_id' => $employee->id,
            'employee' => $this->employeeName($employee),
            'message' => $message,
            'context' => $context,
            'at' => Carbon::now()->toIso8601String(),
        ];
    }

    protected function notifyClosed(FinancialYearClosing $closing, User $actor): void
    {
        $this->notificationService->sendToUsers($this->notificationService->roleUsers(['HR', 'Admin']), [
            'title' => 'Financial Year Closed',
            'message' => 'Financial Year ' . $closing->financial_year . ' closed successfully.',
            'type' => NotificationService::TYPE_INFORMATION,
            'priority' => 'High',
            'url' => route('hrms.financial-year.show', $closing->id),
            'reference_id' => $closing->id,
            'reference_type' => FinancialYearClosing::class,
            'created_by' => $actor->id,
        ]);
    }

    protected function hasAnyRole(User $user, array $roles): bool
    {
        $user->loadMissing('roles');
        return $user->roles->contains(fn ($role): bool => in_array($role->role_name, $roles, true));
    }

    protected function perPage(int $perPage): int
    {
        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
    }
}
