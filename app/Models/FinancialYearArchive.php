<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialYearArchive extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_year_closing_id',
        'financial_year',
        'employee_id',
        'leave_type_id',
        'opening_balance',
        'allocated',
        'consumed',
        'remaining',
        'carry_forward',
        'closing_balance',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'allocated' => 'decimal:2',
        'consumed' => 'decimal:2',
        'remaining' => 'decimal:2',
        'carry_forward' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public function closing(): BelongsTo
    {
        return $this->belongsTo(FinancialYearClosing::class, 'financial_year_closing_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
