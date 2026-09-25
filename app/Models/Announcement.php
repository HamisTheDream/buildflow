<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'body', 'tone', 'cta_text', 'cta_url',
        'is_active', 'is_global', 'plan_id', 'organization_id',
        'starts_at', 'ends_at', 'created_by_admin_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_global' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(\App\Models\Plan::class);
    }

    public function organization()
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }
}
