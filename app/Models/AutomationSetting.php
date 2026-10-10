<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_enabled' => 'boolean',
        'config' => 'array',
    ];

    public const AUTOMATIONS = [
        'payment_schedules' => [
            'name' => 'Installment & Recurring Billing',
            'description' => 'Auto-generates invoices from payment schedules when installments fall due.',
            'schedule' => 'Daily at 06:00',
        ],
        'payment_reminders' => [
            'name' => 'Payment Reminders',
            'description' => 'Nudges clients before and after invoice due dates (7 days before, on due date, 7/14/30 days overdue).',
            'schedule' => 'Daily at 08:00',
        ],
        'budget_alerts' => [
            'name' => 'Budget Overrun Alerts',
            'description' => 'Alerts owners when project spend crosses 80% and 100% of budget.',
            'schedule' => 'Daily at 07:30',
        ],
        'task_escalation' => [
            'name' => 'Task Escalation',
            'description' => 'Reminds assignees of overdue tasks daily; escalates to managers after 3 days.',
            'schedule' => 'Daily at 07:00',
        ],
        'lead_followup' => [
            'name' => 'Lead Follow-up',
            'description' => 'Auto-assigns new leads, sends welcome emails, and chases stale leads.',
            'schedule' => 'Daily at 09:00',
        ],
        'maintenance_sla' => [
            'name' => 'Maintenance SLA Watch',
            'description' => 'Escalates facility/maintenance tickets that breach their SLA.',
            'schedule' => 'Hourly',
        ],
        'auto_payroll' => [
            'name' => 'Automatic Payroll',
            'description' => 'Generates a draft payroll from active staff on the 1st of each month.',
            'schedule' => 'Monthly on the 1st at 06:00',
        ],
        'daily_digest' => [
            'name' => 'Daily Digest',
            'description' => 'Morning briefing for owners: overdue tasks, unpaid invoices, budget alerts, pending leaves.',
            'schedule' => 'Daily at 07:00',
        ],
    ];

    public static function isEnabled(int $organizationId, string $key): bool
    {
        $setting = static::where('organization_id', $organizationId)
            ->where('automation_key', $key)
            ->first();

        // Default ON: automations work out of the box; orgs opt out.
        return $setting ? (bool) $setting->is_enabled : true;
    }

    public static function config(int $organizationId, string $key, array $defaults = []): array
    {
        $setting = static::where('organization_id', $organizationId)
            ->where('automation_key', $key)
            ->first();

        return array_merge($defaults, $setting?->config ?? []);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
