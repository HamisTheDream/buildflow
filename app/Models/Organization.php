<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected static function booted()
    {
        static::creating(function ($org) {
            if (!$org->subscription_status) {
                $org->subscription_status = 'trial';
            }
            if (!$org->trial_ends_at) {
                $org->trial_ends_at = now()->addDays(14);
            }
        });
    }

    protected $fillable = [
        'name',
        'type',
        'currency',
        'plan_id',
        'subscription_status',
        'trial_ends_at',
        'brand_name',
        'brand_logo_path',
        'brand_email',
        'brand_phone',
        'brand_address',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'paid_until' => 'datetime',
        'past_due_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'last_dunning_sent_at' => 'datetime',
        'last_payment_failed_at' => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function invites(): HasMany
    {
        return $this->hasMany(OrganizationInvite::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Plan::class);
    }

    public function effectivePlan(): \App\Models\Plan
    {
        // fallback to free
        return $this->plan ?: \App\Models\Plan::where('key', 'free')->firstOrFail();
    }

    public function isTrialActive(): bool
    {
        return $this->subscription_status === 'trial'
            && $this->trial_ends_at
            && now()->lessThanOrEqualTo($this->trial_ends_at);
    }

    public function isPaidActive(): bool
    {
        return $this->subscription_status === 'active'
            && $this->paid_until
            && now()->lessThanOrEqualTo($this->paid_until);
    }

    public function isInGrace(): bool
    {
        return $this->subscription_status === 'past_due'
            && $this->grace_ends_at
            && now()->lessThanOrEqualTo($this->grace_ends_at);
    }

    public function isLocked(): bool
    {
        if ($this->subscription_status === 'suspended') return true;

        // Past due with grace expired = locked
        if ($this->subscription_status === 'past_due') {
            return !$this->isInGrace();
        }

        // Trial expired locks (unless you want to auto-fallback to free)
        if ($this->subscription_status === 'trial') {
            return !$this->isTrialActive();
        }

        // Active expired becomes past_due via lifecycle job (but in case job hasn’t run)
        if ($this->subscription_status === 'active') {
            return !$this->isPaidActive();
        }

        return false;
    }

    public function hasAppAccess(): bool
    {
        if ($this->subscription_status === 'suspended') return false;

        if ($this->subscription_status === 'free') return true;
        if ($this->subscription_status === 'trial') return $this->isTrialActive();
        if ($this->subscription_status === 'active') return $this->isPaidActive();
        if ($this->subscription_status === 'past_due') return $this->isInGrace();

        return false;
    }
    
    // Kept for backward compatibility if used elsewhere, aliased to hasAppAccess
    public function isActiveAccess(): bool
    {
        return $this->hasAppAccess();
    }

    public function ownerUser()
    {
        return $this->users()->wherePivot('role', 'owner')->first();
    }

    public function billingEmail(): ?string
    {
        if (!empty($this->brand_email)) return $this->brand_email;
        return $this->ownerUser()?->email;
    }
}
