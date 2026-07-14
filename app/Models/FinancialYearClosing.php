<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialYearClosing extends Model
{
    use HasFactory;

    public const STATUS_CLOSED = 'closed';
    public const STATUS_REOPENED = 'reopened';

    protected $fillable = [
        'financial_year',
        'next_financial_year',
        'status',
        'closed_by',
        'closed_at',
        'reopened_by',
        'reopened_at',
        'employees_processed',
        'employees_skipped',
        'inactive_employees',
        'carry_forward_count',
        'reset_count',
        'error_count',
        'execution_time_ms',
        'ip_address',
        'summary',
        'execution_log',
        'audit_timeline',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
        'summary' => 'array',
        'execution_log' => 'array',
        'audit_timeline' => 'array',
    ];

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function archives(): HasMany
    {
        return $this->hasMany(FinancialYearArchive::class);
    }
}
