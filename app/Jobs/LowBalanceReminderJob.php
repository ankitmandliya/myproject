<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\NotificationServiceInterface;
use App\Models\CompanySetting;
use App\Models\EmployeeLeaveBalance;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LowBalanceReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationServiceInterface $notifications): void
    {
        $settings = CompanySetting::query()->first();
        if (! ($settings?->enable_notifications ?? true) || ! ($settings?->enable_low_balance_alerts ?? true)) {
            return;
        }

        EmployeeLeaveBalance::query()
            ->with(['employee', 'leaveType'])
            ->whereHas('leaveType')
            ->get()
            ->filter(fn (EmployeeLeaveBalance $balance): bool => $this->isLow($balance))
            ->each(function (EmployeeLeaveBalance $balance) use ($notifications): void {
                if (! $balance->employee || $this->alreadySentThisMonth($balance)) {
                    return;
                }

                $notifications->sendToUsers([$balance->employee], [
                    'title' => 'Low Leave Balance',
                    'message' => 'Your ' . ($balance->leaveType?->leave_name ?? 'leave') . ' balance is low. Remaining: ' . number_format((float) $balance->remaining, 2),
                    'type' => NotificationService::TYPE_WARNING,
                    'priority' => 'High',
                    'url' => route('hrms.dashboard'),
                    'reference_id' => $balance->id,
                    'reference_type' => EmployeeLeaveBalance::class,
                ]);
            });
    }

    protected function isLow(EmployeeLeaveBalance $balance): bool
    {
        $name = strtoupper((string) ($balance->leaveType?->leave_name ?? ''));
        $remaining = (float) $balance->remaining;

        return (str_contains($name, 'CL') || str_contains($name, 'CASUAL')) && $remaining <= 2
            || (str_contains($name, 'SL') || str_contains($name, 'SICK')) && $remaining <= 2
            || (str_contains($name, 'EL') || str_contains($name, 'EARN')) && $remaining <= 3;
    }

    protected function alreadySentThisMonth(EmployeeLeaveBalance $balance): bool
    {
        $start = Carbon::now('Asia/Kolkata')->startOfMonth();

        return $balance->employee?->notifications()
            ->where('data->title', 'Low Leave Balance')
            ->where('data->reference_id', $balance->id)
            ->where('created_at', '>=', $start)
            ->exists() ?? false;
    }
}
