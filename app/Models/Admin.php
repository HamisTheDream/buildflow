<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'is_super',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_super' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(PlatformDepartment::class, 'department_id');
    }

    public function managedDepartments()
    {
        return $this->hasMany(PlatformDepartment::class, 'manager_id');
    }

    public function notes()
    {
        return $this->hasMany(OrganizationNote::class);
    }

    public function tasks()
    {
        return $this->hasMany(OrganizationTask::class, 'assigned_to');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public function scopeRole($query, $role)
    {
        return $query->where('role_type', $role);
    }
}
