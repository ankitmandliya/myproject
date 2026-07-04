<?php

declare(strict_types=1);

namespace App\Contracts\Common;

use Carbon\Carbon;

interface DateServiceInterface
{
    public function today(): Carbon;

    public function now(): Carbon;

    public function formatDate(mixed $date, string $format = 'd-m-Y'): string;

    public function formatDateTime(mixed $date): string;

    public function startOfMonth(mixed $date = null): Carbon;

    public function endOfMonth(mixed $date = null): Carbon;

    public function daysBetween(mixed $from, mixed $to): int;

    public function isWeekend(mixed $date): bool;

    public function isFutureDate(mixed $date): bool;

    public function isPastDate(mixed $date): bool;
}
