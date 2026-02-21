<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\PageVisit;

class LogPageVisit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $visitData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $visitData)
    {
        $this->visitData = $visitData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        PageVisit::create($this->visitData);
    }
}
