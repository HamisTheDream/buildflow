<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'key','name','price_monthly_cents',
        'max_projects','max_members','max_storage_mb',
        'can_share_reports','can_password_protect_reports',
    ];

    protected $casts = [
        'can_share_reports' => 'boolean',
        'can_password_protect_reports' => 'boolean',
    ];
}
