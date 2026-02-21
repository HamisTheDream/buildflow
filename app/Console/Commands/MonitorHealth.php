<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class MonitorHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor application uptime and core infrastructure health';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting health checks...');
        $errors = [];

        // 1. Check Database
        try {
            DB::connection()->getPdo();
            $this->info('[OK] Database connected');
        } catch (\Exception $e) {
            $errors[] = 'Database Connection Failed: ' . $e->getMessage();
            $this->error('[FAIL] Database error');
        }

        // 2. Check Cache
        try {
            $cacheType = config('cache.default');
            if ($cacheType === 'redis') {
                Redis::connection()->ping();
            } else {
                cache()->put('health_check', 'ok', 1);
                if (cache()->get('health_check') !== 'ok') {
                    throw new \Exception('Cache write/read mismatch.');
                }
            }
            $this->info("[OK] Cache ($cacheType) is working");
        } catch (\Exception $e) {
            $errors[] = 'Cache Failed: ' . $e->getMessage();
            $this->error('[FAIL] Cache error');
        }

        // 3. Check Storage
        try {
            Storage::disk('public')->put('health_check.txt', 'ok');
            Storage::disk('public')->delete('health_check.txt');
            $this->info('[OK] Local Storage writable');
        } catch (\Exception $e) {
            $errors[] = 'Storage Failed: ' . $e->getMessage();
            $this->error('[FAIL] Storage error');
        }

        // Report
        if (count($errors) > 0) {
            $message = "Health Check Failures:\n" . implode("\n", $errors);
            Log::critical($message);

            // Send notification to support/admin if mail is configured
            if (config('mail.from.address')) {
                try {
                    Mail::raw($message, function ($msg) {
                        $msg->to(config('mail.from.address'))
                            ->subject('CRITICAL: BuildFlow Health Check Failed');
                    });
                } catch (\Exception $e) {
                    Log::error('Failed to send health check alert email: ' . $e->getMessage());
                }
            }

            $this->error('Health checks completed with errors.');
            return Command::FAILURE;
        }

        $this->info('All systems operational. ✅');
        return Command::SUCCESS;
    }
}
