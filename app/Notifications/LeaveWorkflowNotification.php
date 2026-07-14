<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\LeaveApply;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class LeaveWorkflowNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected LeaveApply $leave,
        protected string $action,
        protected ?string $remarks = null
    ) {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $type = $this->typeFromAction($this->action);
        $style = NotificationService::TYPE_STYLES[$type] ?? NotificationService::TYPE_STYLES[NotificationService::TYPE_INFORMATION];
        $employee = $this->leave->user?->name ?? 'Employee';
        $leaveType = $this->leave->leaveType?->leave_name ?? 'Leave';
        $dateRange = trim((optional($this->leave->from_date)->format('d M Y') ?? '-') . ' to ' . (optional($this->leave->to_date)->format('d M Y') ?? '-'));

        return new DatabaseMessage([
            'title' => $type,
            'message' => $this->message($employee, $leaveType, $dateRange),
            'type' => $type,
            'icon' => $style['icon'],
            'color' => $style['color'],
            'priority' => $this->priority($type),
            'url' => route('hrms.leave-apply.show', $this->leave->id),
            'reference_id' => $this->leave->id,
            'reference_type' => LeaveApply::class,
            'created_by' => auth()->id(),
            'leave_id' => $this->leave->id,
            'action' => $this->action,
            'status' => $this->leave->status,
            'remarks' => $this->remarks,
            'employee' => $employee,
            'leave_type' => $leaveType,
            'from_date' => optional($this->leave->from_date)->toDateString(),
            'to_date' => optional($this->leave->to_date)->toDateString(),
        ]);
    }

    protected function typeFromAction(string $action): string
    {
        $normalized = strtolower($action);

        return match (true) {
            str_contains($normalized, 'approved') || str_contains($normalized, 'approval') => NotificationService::TYPE_LEAVE_APPROVED,
            str_contains($normalized, 'rejected') => NotificationService::TYPE_LEAVE_REJECTED,
            str_contains($normalized, 'cancelled') || str_contains($normalized, 'canceled') => NotificationService::TYPE_LEAVE_CANCELLED,
            str_contains($normalized, 'revoked') => NotificationService::TYPE_LEAVE_REVOKED,
            str_contains($normalized, 'updated') => NotificationService::TYPE_LEAVE_UPDATED,
            str_contains($normalized, 'awaiting') || str_contains($normalized, 'submitted') || str_contains($normalized, 'request') => NotificationService::TYPE_LEAVE_APPLIED,
            default => NotificationService::TYPE_INFORMATION,
        };
    }

    protected function message(string $employee, string $leaveType, string $dateRange): string
    {
        return trim($employee . ' - ' . $leaveType . ' (' . $dateRange . '): ' . $this->action . ($this->remarks ? ' Remarks: ' . $this->remarks : ''));
    }

    protected function priority(string $type): string
    {
        return match ($type) {
            NotificationService::TYPE_LEAVE_REJECTED, NotificationService::TYPE_LEAVE_REVOKED => 'High',
            NotificationService::TYPE_WARNING => 'Critical',
            default => 'Medium',
        };
    }
}
