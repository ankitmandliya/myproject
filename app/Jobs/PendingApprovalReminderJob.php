<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\NotificationServiceInterface;
use App\Models\CompanySetting;
use App\Models\LeaveApply;
use App\Services\LeaveApprovalService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PendingApprovalReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationServiceInterface $notifications): void
    {
        $settings = CompanySetting::query()->first();
        if (! ($settings?->enable_notifications ?? true) || ! ($settings?->enable_approval_reminders ?? true)) {
            return;
        }

        $pending = LeaveApply::query()->pending()->count();
        if ($pending <= 0) {
            return;
        }

        $payload = [
            'title' => 'Pending Leave Approval Reminder',
            'message' => 'You have pending leave requests awaiting action.',
            'type' => NotificationService::TYPE_REMINDER,
            'priority' => 'High',
            'url' => route('hrms.leave-apply.approvals'),
            'reference_type' => LeaveApply::class,
        ];

        $managerIds = LeaveApply::query()
            ->pending()
            ->where('approval_level', LeaveApprovalService::LEVEL_MANAGER)
            ->whereNotNull('manager_id')
            ->distinct()
            ->pluck('manager_id');

        $managers = \App\Models\User::query()->whereIn('id', $managerIds)->get();
        $notifications->sendToUsers($managers, $payload);
        $notifications->sendToUsers($notifications->roleUsers(['HR', 'Admin']), $payload);
    }
}
