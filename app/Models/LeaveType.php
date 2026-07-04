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
        'is_paid',
        'status',
    ];

    protected $casts = [
        'total_days' => 'integer',
        'is_paid' => 'boolean',
        'status' => 'boolean',
    ];

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApply::class);
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
