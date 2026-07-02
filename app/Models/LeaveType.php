<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;
    protected $table = 'leave_types';
    protected $guarded = [];
    public  $timestamps = false; // Disable timestamps if not needed
}
