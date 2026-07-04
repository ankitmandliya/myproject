<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends Model
{
    use HasFactory;

    protected $table = 'role_permission';

    public const UPDATED_AT = null;

    protected $fillable = [
        'role_id',
        'permission_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(RoleMaster::class, 'role_id');
    }

    public function scopePermission($query, string $permissionName)
    {
        return $query->where('permission_name', $permissionName);
    }
}
