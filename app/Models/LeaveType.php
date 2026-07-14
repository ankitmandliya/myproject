<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\OrderByScope; //importing the orderByScope class to use it as a global scope in the Holiday model

class LeaveType extends Model
{
    use HasFactory;

    protected $table = 'leave_types';
    public $timestamps = false; // Disable timestamps because the existing leave_types table has no timestamp columns.

    protected $fillable = [
        'leave_name',
        'leave_code',
        'total_days',
        'annual_allocation',
        'monthly_allocation',
        'carry_forward_enabled',
        'sandwich_applicable',
        'half_day_allowed',
        'requires_approval',
        'is_paid',
        'status',
    ];

    protected $casts = [
        'total_days' => 'integer',
        'annual_allocation' => 'decimal:2',
        'monthly_allocation' => 'decimal:2',
        'carry_forward_enabled' => 'boolean',
        'sandwich_applicable' => 'boolean',
        'half_day_allowed' => 'boolean',
        'requires_approval' => 'boolean',
        'is_paid' => 'boolean',
        'status' => 'boolean',
    ];

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApply::class);
    }

    public function employeeLeaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    //adding global scope to order holidays by id in descending order
    protected static function booted(): void
    {
        static::addGlobalScope(new OrderByScope); //adding global scope to order holidays by id in descending order
    }
}
