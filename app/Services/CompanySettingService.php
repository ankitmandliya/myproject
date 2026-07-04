<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CompanySettingServiceInterface;
use App\Models\CompanySetting;
use Carbon\Carbon;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service for company-wide HRMS configuration.
 */
class CompanySettingService implements CompanySettingServiceInterface
{
    /**
     * Cached company settings model for the current request lifecycle.
     */
    protected ?CompanySetting $settings = null;

    /**
     * Create a new company setting service instance.
     */
    public function __construct(
        protected CompanySetting $companySetting
    ) {
    }

    /**
     * Get the company settings record.
     */
    public function getSettings(): CompanySetting
    {
        if ($this->settings instanceof CompanySetting) {
            return $this->settings;
        }

        $settings = $this->companySetting->first();

        if (! $settings instanceof CompanySetting) {
            throw new RuntimeException('Company settings record was not found.');
        }

        return $this->settings = $settings;
    }

    /**
     * Update company settings.
     */
    public function updateSettings(array $data): CompanySetting
    {
        $payload = $this->filterAllowedFields($data);

        $this->validateSettingsData($payload);

        $settings = $this->getSettings();
        $settings->update($payload);

        return $this->settings = $settings;
    }

    /**
     * Get office start time.
     */
    public function getOfficeStartTime(): string
    {
        return (string) $this->getSettings()->office_start_time;
    }

    /**
     * Get office end time.
     */
    public function getOfficeEndTime(): string
    {
        return (string) $this->getSettings()->office_end_time;
    }

    /**
     * Get late threshold in minutes.
     */
    public function getLateThreshold(): int
    {
        return (int) $this->getSettings()->late_after_minutes;
    }

    /**
     * Get half-day threshold in minutes.
     */
    public function getHalfDayThreshold(): int
    {
        return (int) $this->getSettings()->half_day_after_minutes;
    }

    /**
     * Get salary generation date.
     */
    public function getSalaryDate(): int
    {
        return (int) $this->getSettings()->salary_date;
    }

    /**
     * Get configured weekly off day.
     */
    public function getWeeklyOff(): string
    {
        return (string) $this->getSettings()->weekly_off;
    }

    /**
     * Determine whether the given date is the configured weekly off.
     */
    public function isWeeklyOff(Carbon $date): bool
    {
        return $date->englishDayOfWeek === $this->getWeeklyOff();
    }

    /**
     * Determine whether office is open for the given date.
     */
    public function isOfficeOpen(Carbon $date): bool
    {
        return ! $this->isWeeklyOff($date);
    }

    /**
     * Keep only fields owned by the company setting module.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function filterAllowedFields(array $data): array
    {
        $allowedFields = [
            'office_start_time',
            'office_end_time',
            'late_after_minutes',
            'half_day_after_minutes',
            'salary_date',
            'weekly_off',
        ];

        return array_intersect_key($data, array_flip($allowedFields));
    }

    /**
     * Validate company setting business configuration.
     *
     * @param array<string, mixed> $data
     */
    protected function validateSettingsData(array $data): void
    {
        foreach (['office_start_time', 'office_end_time'] as $field) {
            if (! isset($data[$field]) || ! is_string($data[$field]) || trim($data[$field]) === '') {
                throw new InvalidArgumentException(str_replace('_', ' ', ucfirst($field)) . ' is required.');
            }
        }

        $officeStartTime = $this->parseTime((string) $data['office_start_time'], 'Office start time');
        $officeEndTime = $this->parseTime((string) $data['office_end_time'], 'Office end time');

        if ($officeEndTime->lessThanOrEqualTo($officeStartTime)) {
            throw new InvalidArgumentException('Office end time must be greater than office start time.');
        }

        $lateThreshold = $this->validateInteger($data, 'late_after_minutes', 'Late threshold');
        $halfDayThreshold = $this->validateInteger($data, 'half_day_after_minutes', 'Half day threshold');
        $salaryDate = $this->validateInteger($data, 'salary_date', 'Salary date');

        if ($lateThreshold < 0) {
            throw new InvalidArgumentException('Late threshold must be greater than or equal to 0.');
        }

        if ($halfDayThreshold <= $lateThreshold) {
            throw new InvalidArgumentException('Half day threshold must be greater than late threshold.');
        }

        if ($salaryDate < 1 || $salaryDate > 31) {
            throw new InvalidArgumentException('Salary date must be between 1 and 31.');
        }

        $this->validateWeeklyOff($data['weekly_off'] ?? null);
    }

    /**
     * Parse a configured time value.
     */
    protected function parseTime(string $time, string $label): Carbon
    {
        $parsedTime = Carbon::createFromFormat('H:i:s', $time);

        if ($parsedTime === false || $parsedTime->format('H:i:s') !== $time) {
            throw new InvalidArgumentException($label . ' must use H:i:s format.');
        }

        return $parsedTime;
    }

    /**
     * Validate an integer configuration value.
     *
     * @param array<string, mixed> $data
     */
    protected function validateInteger(array $data, string $field, string $label): int
    {
        if (! array_key_exists($field, $data) || filter_var($data[$field], FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException($label . ' must be an integer.');
        }

        return (int) $data[$field];
    }

    /**
     * Validate configured weekly off day.
     */
    protected function validateWeeklyOff(mixed $weeklyOff): void
    {
        $allowedWeeklyOffs = [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];

        if (! is_string($weeklyOff) || ! in_array($weeklyOff, $allowedWeeklyOffs, true)) {
            throw new InvalidArgumentException('Weekly off must be a valid weekday.');
        }
    }
}
