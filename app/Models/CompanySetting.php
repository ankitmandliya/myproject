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
    ];

    protected $casts = [
        'late_after_minutes' => 'integer',
        'half_day_after_minutes' => 'integer',
        'salary_date' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
