<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\LeaveApply;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshAttendanceFromLeave
{
    public function handle(object $event): void
    {
        $leave = $event->leave ?? null;

        if (! $leave instanceof LeaveApply) {
            return;
        }

        $from = $leave->from_date?->copy()->startOfDay();
        $to = $leave->to_date?->copy()->startOfDay();

        if ($from === null || $to === null) {
            return;
        }

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            Cache::forget('attendance.status.' . $leave->user_id . '.' . $date->toDateString());
            Cache::forget('attendance.widget.' . $leave->user_id . '.' . $date->toDateString());
        }

        Log::info('Attendance leave status refreshed.', [
            'leave_id' => $leave->id,
            'user_id' => $leave->user_id,
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'event' => $event::class,
        ]);
    }
}