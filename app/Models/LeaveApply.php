<?php

namespace App\Models;

use App\Services\LeaveApprovalService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApply extends Model
{
    use HasFactory;

    protected $table = 'leave_apply';

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'from_date',
        'to_date',
        'total_days',
        'requested_days',
        'holiday_days',
        'weekly_off_days',
        'sandwich_days',
        'payable_leave_days',
        'leave_calculation_json',
        'reason',
        'status',
        'approval_level',
        'approved_by',
        'approved_at',
        'manager_id',
        'manager_status',
        'manager_remarks',
        'manager_action_at',
        'hr_id',
        'hr_status',
        'hr_remarks',
        'hr_action_at',
        'admin_id',
        'admin_status',
        'admin_remarks',
        'admin_action_at',
        'rejected_by',
        'rejected_at',
        'cancelled_by',
        'cancelled_at',
        'revoked_by',
        'revoked_at',
        'approval_timeline',
        'approval_audit_log',
        'attendance_warning',
        'payroll_locked_at',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'total_days' => 'integer',
        'requested_days' => 'decimal:2',
        'holiday_days' => 'decimal:2',
        'weekly_off_days' => 'decimal:2',
        'sandwich_days' => 'decimal:2',
        'payable_leave_days' => 'decimal:2',
        'leave_calculation_json' => 'array',
        'approval_timeline' => 'array',
        'approval_audit_log' => 'array',
        'approved_at' => 'datetime',
        'manager_action_at' => 'datetime',
        'hr_action_at' => 'datetime',
        'admin_action_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'revoked_at' => 'datetime',
        'payroll_locked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function hrApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_id');
    }

    public function adminApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            LeaveApprovalService::STATUS_PENDING,
            LeaveApprovalService::STATUS_MANAGER_APPROVED,
            LeaveApprovalService::STATUS_HR_APPROVED,
        ]);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', LeaveApprovalService::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', LeaveApprovalService::STATUS_REJECTED);
    }
}
