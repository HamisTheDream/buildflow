<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\CascadesSoftDeletes;

class Project extends Model
{
    use SoftDeletes, CascadesSoftDeletes;

    /**
     * Project-scoped children soft-deleted / restored with the project.
     * Member pivots are soft-deleted by their own trait config.
     */
    protected array $cascadeSoftDeletes = [
        'media',
        'attachments',
        'tasks',
        'issues',
        'dailyLogs',
        'costs',
        'units',
        'reports',
        'todayLogs',
        'activityLogs',
    ];

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'client_name',
        'client_phone',
        'client_email',
        'location',
        'start_date',
        'end_date',
        'status',
        'description',
        'budget',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'float',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot(['role'])
            ->wherePivotNull('project_members.deleted_at')
            ->withTimestamps();
    }

    public function memberRole(User $user): ?string
    {
        $row = $this->members()->where('users.id', $user->id)->first();
        return $row?->pivot?->role;
    }

    public function dailyLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectLog::class);
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function issues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectIssue::class);
    }

    public function costs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectCost::class);
    }

    public function units(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectUnit::class);
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectReport::class);
    }

    public function media(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectMedia::class);
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function todayLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TodayLog::class);
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
