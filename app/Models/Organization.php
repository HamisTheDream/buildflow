<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\OrganizationDeletionService;

class Organization extends Model
{
    use HasFactory, SoftDeletes;
    protected static function booted()
    {
        // Soft-delete / restore of the whole organization database is
        // delegated to the dedicated service (transactional, ordered,
        // R2-safe). Force deletes bypass it entirely.
        static::deleted(function (Organization $org) {
            if ($org->isForceDeleting()) {
                return;
            }
            app(OrganizationDeletionService::class)->delete($org);
        });

        static::restoring(function (Organization $org) {
            app(OrganizationDeletionService::class)->restore($org);
        });

        static::creating(function ($org) {
            if (!$org->subscription_status) {
                $org->subscription_status = 'trial';
            }
            if (!$org->trial_ends_at) {
                $org->trial_ends_at = now()->addDays(14);
            }
        });

        static::created(function ($org) {
            $defaults = ['Sales & Marketing', 'Accounts', 'HR', 'Admin', 'Legal'];
            foreach ($defaults as $name) {
                \App\Models\HR\Department::create([
                    'organization_id' => $org->id,
                    'name' => $name,
                ]);
            }
        });
    }

    protected $fillable = [
        'name',
        'type',
        'currency',
        'plan_id',
        'subscription_status',
        'crm_stage',
        'trial_ends_at',
        'brand_name',
        'brand_logo_path',
        'brand_email',
        'brand_phone',
        'brand_address',
        'brand_color',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'paid_until' => 'datetime',
        'past_due_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'last_dunning_sent_at' => 'datetime',
        'last_payment_failed_at' => 'datetime',
    ];

    public function notes(): HasMany
    {
        return $this->hasMany(OrganizationNote::class)->orderByDesc('created_at');
    }

    public function crmTasks(): HasMany
    {
        return $this->hasMany(OrganizationTask::class)->orderBy('due_at');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot(['role'])
            ->wherePivotNull('organization_user.deleted_at')
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

    public function crmTasks(): HasMany
    {
        return $this->hasMany(OrganizationTask::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function crmProperties(): HasMany
    {
        return $this->hasMany(CRM\Property::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Finance\Invoice::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Finance\Expense::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Finance\Budget::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(HR\Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(HR\Employee::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(HR\Payroll::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(HR\Leave::class);
    }

    public function pageVisits(): HasMany
    {
        return $this->hasMany(PageVisit::class);
    }

    public function ownerDeals(): HasMany
    {
        return $this->hasMany(OwnerDeal::class);
    }

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Plan::class);
    }

    public function effectivePlan(): ?\App\Models\Plan
    {
        // fallback to free (use first() instead of firstOrFail() to avoid 404 crash)
        return $this->plan ?: \App\Models\Plan::where('key', 'free')->first();
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
        if ($this->subscription_status === 'blocked') return false;
        if ($this->subscription_status === 'expired') return false;

        // Null or empty status = allow access (dev/free tier fallback)
        if (empty($this->subscription_status)) return true;

        if ($this->subscription_status === 'free') return true;
        if ($this->subscription_status === 'trial') return $this->isTrialActive();
        if ($this->subscription_status === 'active') return $this->isPaidActive();
        if ($this->subscription_status === 'past_due') return $this->isInGrace();
        if ($this->subscription_status === 'grace') return $this->isInGrace();

        // Unknown status = deny access (fail closed for security)
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

        // Use the already-loaded relationship if available, otherwise query
        $owner = $this->relationLoaded('users')
            ? $this->users->firstWhere('pivot.role', 'owner')
            : $this->ownerUser();

        return $owner?->email;
    }
}
