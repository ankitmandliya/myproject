<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveBalance extends Model
{
    use HasFactory;

    protected $table = 'employee_leave_balances';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'financial_year',
        'allocated',
        'used',
        'remaining',
        'carry_forward',
    ];

    protected $casts = [
        'allocated' => 'decimal:2',
        'used' => 'decimal:2',
        'remaining' => 'decimal:2',
        'carry_forward' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}