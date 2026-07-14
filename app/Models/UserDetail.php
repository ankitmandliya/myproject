<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
    use HasFactory;

    protected $table = 'user_details';

    protected $fillable = [
        'user_id',
        'reporting_manager_id',
        'emp_code',
        'first_name',
        'last_name',
        'gender',
        'dob',
        'joining_date',
        'department',
        'designation',
        'basic_salary',
        'address',
        'aadhaar',
        'pan',
        'profile_photo',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'basic_salary' => 'decimal:2',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporting_manager_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
