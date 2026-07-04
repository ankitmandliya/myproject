<?php

declare(strict_types=1);

namespace App\Contracts;

use Carbon\Carbon;

/**
 * Defines the company setting service contract.
 */
interface CompanySettingServiceInterface
{
    /** Get company settings. */
    public function getSettings(): mixed;

    /** Update company settings. */
    public function updateSettings(array $data): mixed;

    /** Get office start time. */
    public function getOfficeStartTime(): string;

    /** Get office end time. */
    public function getOfficeEndTime(): string;

    /** Get late threshold in minutes. */
    public function getLateThreshold(): int;

    /** Get half-day threshold in minutes. */
    public function getHalfDayThreshold(): int;

    /** Get salary generation date. */
    public function getSalaryDate(): int;

    /** Get configured weekly off day. */
    public function getWeeklyOff(): string;

    /** Determine whether the given date is the configured weekly off. */
    public function isWeeklyOff(Carbon $date): bool;

    /** Determine whether office is open for the given date. */
    public function isOfficeOpen(Carbon $date): bool;
}
