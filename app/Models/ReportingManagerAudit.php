<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportingManagerAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'old_manager_id',
        'new_manager_id',
        'changed_by',
        'action',
        'ip_address',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function oldManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'old_manager_id');
    }

    public function newManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_manager_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
