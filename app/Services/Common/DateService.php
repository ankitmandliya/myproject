<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\Common\DateServiceInterface;
use Carbon\Carbon;

class DateService implements DateServiceInterface
{
    public function today(): Carbon
    {
        return Carbon::today();
    }

    public function now(): Carbon
    {
        return Carbon::now();
    }

    public function formatDate(mixed $date, string $format = 'd-m-Y'): string
    {
        return $this->parseDate($date)->format($format);
    }

    public function formatDateTime(mixed $date): string
    {
        return $this->parseDate($date)->format('d-m-Y H:i:s');
    }

    public function startOfMonth(mixed $date = null): Carbon
    {
        return $this->parseDate($date)->startOfMonth();
    }

    public function endOfMonth(mixed $date = null): Carbon
    {
        return $this->parseDate($date)->endOfMonth();
    }

    public function daysBetween(mixed $from, mixed $to): int
    {
        return $this->parseDate($from)->startOfDay()->diffInDays($this->parseDate($to)->startOfDay());
    }

    public function isWeekend(mixed $date): bool
    {
        return $this->parseDate($date)->isWeekend();
    }

    public function isFutureDate(mixed $date): bool
    {
        return $this->parseDate($date)->startOfDay()->isFuture();
    }

    public function isPastDate(mixed $date): bool
    {
        return $this->parseDate($date)->startOfDay()->isPast() && ! $this->parseDate($date)->isToday();
    }

    protected function parseDate(mixed $date = null): Carbon
    {
        if ($date instanceof Carbon) {
            return $date->copy();
        }

        return $date === null ? Carbon::now() : Carbon::parse($date);
    }
}
