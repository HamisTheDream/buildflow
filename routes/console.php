<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');
Schedule::command('sitemap:generate')->daily()->at('02:00');
Schedule::job(new \App\Jobs\SendDripEmails)->daily()->at('09:00');

Schedule::command('subscriptions:run-lifecycle')->dailyAt('07:00');

// Health Monitoring
Schedule::command('monitor:health')->hourly();

// ── BuildFlow Automations ──
Schedule::command('automations:payment-schedules')->daily()->at('06:00');
Schedule::command('automations:daily-digest')->daily()->at('07:00');
Schedule::command('automations:escalate-tasks')->daily()->at('07:00');
Schedule::command('automations:budget-alerts')->daily()->at('07:30');
Schedule::command('automations:payment-reminders')->daily()->at('08:00');
Schedule::command('automations:followup-leads')->daily()->at('09:00');
Schedule::command('automations:run-payroll')->monthlyOn(1, '06:00');
Schedule::command('automations:maintenance-sla')->hourly();
