<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PageVisit;
use Carbon\Carbon;

class PrunePageVisits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:prune-visits {--days=90 : The number of days to retain data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old page visits to save database storage (GDPR compliance)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $date = Carbon::now()->subDays($days);

        $this->info("Pruning page visits older than {$days} days ({$date->toDateTimeString()})...");

        $count = PageVisit::where('created_at', '<', $date)->delete();

        $this->info("Successfully deleted {$count} old page visit records.");
        return Command::SUCCESS;
    }
}
