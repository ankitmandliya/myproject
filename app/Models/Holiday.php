<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\OrderByScope; //importing the orderByScope class to use it as a global scope in the Holiday model

class Holiday extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    protected $table = 'holidays';
    protected $fillable = [
        'name',
        'from_date',
        'to_date',
        'status',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'status' => 'integer',
    ];

    public function scopeActive($query, $statusValue) //local scope to filter holidays based on status
    {
        return $query->where('status', $statusValue); //where clause to filter holidays based on status
    }

    //adding global scope to order holidays by id in descending order
    protected static function booted(): void
    {
        static::addGlobalScope(new OrderByScope); //adding global scope to order holidays by id in descending order
    }

}