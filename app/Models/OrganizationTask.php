<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'assigned_to',
        'created_by',
        'content',
        'due_at',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignee()
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
