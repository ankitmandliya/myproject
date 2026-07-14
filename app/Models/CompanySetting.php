<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $table = 'company_setting';

    protected $fillable = [
        'office_start_time',
        'office_end_time',
        'late_after_minutes',
        'half_day_after_minutes',
        'salary_date',
        'weekly_off',
        'sandwich_leave_enabled',
        'holiday_between_leave_count',
        'weekly_off_between_leave_count',
        'allow_half_day_leave',
        'leave_apply_before_days',
        'leave_cancel_before_days',
        'leave_auto_approval',
        'leave_approval_levels',
    ];

    protected $casts = [
        'late_after_minutes' => 'integer',
        'half_day_after_minutes' => 'integer',
        'salary_date' => 'integer',
        'sandwich_leave_enabled' => 'boolean',
        'holiday_between_leave_count' => 'boolean',
        'weekly_off_between_leave_count' => 'boolean',
        'allow_half_day_leave' => 'boolean',
        'leave_apply_before_days' => 'integer',
        'leave_cancel_before_days' => 'integer',
        'leave_auto_approval' => 'boolean',
        'leave_approval_levels' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}


