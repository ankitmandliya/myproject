<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\NotificationServiceInterface;
use App\Models\CompanySetting;
use App\Models\LeaveApply;
use App\Services\LeaveApprovalService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LeaveStartReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationServiceInterface $notifications): void
    {
        $settings = CompanySetting::query()->first();
        if (! ($settings?->enable_notifications ?? true) || ! ($settings?->enable_leave_reminders ?? true)) {
            return;
        }

        $today = Carbon::today('Asia/Kolkata')->toDateString();
        LeaveApply::query()
            ->with('user')
            ->where('status', LeaveApprovalService::STATUS_APPROVED)
            ->whereDate('from_date', $today)
            ->get()
            ->each(function (LeaveApply $leave) use ($notifications): void {
                $notifications->sendToUsers([$leave->user], [
                    'title' => 'Your approved leave starts today',
                    'message' => 'Your approved leave starts today.',
                    'type' => NotificationService::TYPE_REMINDER,
                    'priority' => 'Medium',
                    'url' => route('hrms.leave-apply.show', $leave->id),
                    'reference_id' => $leave->id,
                    'reference_type' => LeaveApply::class,
                ]);
            });
    }
}
