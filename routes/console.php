<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');
Schedule::command('sitemap:generate')->daily()->at('02:00');
Schedule::job(new \App\Jobs\SendDripEmails)->daily()->at('09:00');

Schedule::command('subscriptions:run-lifecycle')->dailyAt('07:00');
