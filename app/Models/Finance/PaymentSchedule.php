<?php

namespace App\Models\Finance;

use App\Models\Organization;
use App\Models\CRM\Lead;
use App\Models\User;
use App\Models\Project;
use App\Models\CRM\Deal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSchedule extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'next_run_date' => 'date',
        'end_date' => 'date',
        'amount_cents' => 'integer',
        'meta' => 'array',
    ];

    public const FREQUENCIES = ['weekly', 'monthly', 'quarterly', 'yearly'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function clientLead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function clientUser()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function isDue(): bool
    {
        return $this->status === 'active'
            && $this->next_run_date->isPast()
            && ($this->end_date === null || $this->next_run_date->lte($this->end_date))
            && ($this->max_runs === null || $this->runs_completed < $this->max_runs);
    }

    public function advance(): void
    {
        $next = match ($this->frequency) {
            'weekly' => $this->next_run_date->copy()->addWeeks($this->interval),
            'quarterly' => $this->next_run_date->copy()->addMonths(3 * $this->interval),
            'yearly' => $this->next_run_date->copy()->addYears($this->interval),
            default => $this->next_run_date->copy()->addMonths($this->interval),
        };

        $runs = $this->runs_completed + 1;
        $done = ($this->max_runs !== null && $runs >= $this->max_runs)
            || ($this->end_date !== null && $next->gt($this->end_date));

        $this->update([
            'next_run_date' => $next,
            'runs_completed' => $runs,
            'status' => $done ? 'completed' : 'active',
        ]);
    }

    public function clientName(): string
    {
        if ($this->clientLead) {
            return trim($this->clientLead->first_name . ' ' . ($this->clientLead->last_name ?? ''));
        }
        if ($this->clientUser) {
            return $this->clientUser->name;
        }
        return 'Client';
    }

    public function clientEmail(): ?string
    {
        return $this->clientLead?->email ?? $this->clientUser?->email;
    }
}
