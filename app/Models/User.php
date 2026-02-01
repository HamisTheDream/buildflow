<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar_path',
        'password',
        'current_organization_id',
        'is_invited_only',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_invited_only' => 'boolean',
    ];

    // --- Tenant relations ---

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    public function orgRole(int $organizationId): ?string
    {
        return $this->organizations()
            ->where('organizations.id', $organizationId)
            ->first()?->pivot?->role;
    }

    public function isOrgOwner(int $organizationId): bool
    {
        return $this->orgRole($organizationId) === 'owner';
    }

    public function isOrgAdmin(int $organizationId): bool
    {
        return in_array($this->orgRole($organizationId), ['owner', 'admin'], true);
    }

    public function projectRole(int $projectId): ?string
    {
        if (method_exists($this, 'projects')) {
            return $this->projects()
                ->where('projects.id', $projectId)
                ->first()?->pivot?->role;
        }

        return \Illuminate\Support\Facades\DB::table('project_members')
            ->where('project_id', $projectId)
            ->where('user_id', $this->id)
            ->value('role');
    }

    public function isProjectOwner(int $projectId): bool
    {
        return $this->projectRole($projectId) === 'owner';
    }

    public function inOrganization(int $organizationId): bool
    {
        return $this->organizations()->where('organizations.id', $organizationId)->exists();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot(['role'])
            ->withTimestamps();
    }
}
