<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
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
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function memberRole(User $user): ?string
    {
        $row = $this->members()->where('users.id', $user->id)->first();
        return $row?->pivot?->role;
    }
}
