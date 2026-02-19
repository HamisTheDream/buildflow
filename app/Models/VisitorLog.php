<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'visit_time' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeBounceRate($query)
    {
        // simplistic bounce rate: visitor only visited 1 page in a session
        // This is tricky without a session table or more complex query
        // For now, let's just leave it as a placeholder or implement in controller
        return $query;
    }
}
