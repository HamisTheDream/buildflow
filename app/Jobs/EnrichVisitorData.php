<?php

namespace App\Jobs;

use App\Models\VisitorLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnrichVisitorData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $visitorLogId;

    /**
     * Create a new job instance.
     */
    public function __construct($visitorLogId)
    {
        $this->visitorLogId = $visitorLogId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = VisitorLog::find($this->visitorLogId);

        if (!$log || $log->country || $log->ip_address === '127.0.0.1') {
            return;
        }

        // Check cache first for this IP to save API calls
        $cacheKey = 'ip_geo_' . $log->ip_address;
        $geoData = cache()->remember($cacheKey, 86400, function () use ($log) {
            try {
                // Using ip-api.com (free tier, limited rate) or similar
                // For production, use a paid service or a more robust one
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$log->ip_address}?fields=status,country,countryCode,city,regionName,lat,lon");

                if ($response->successful() && $response->json('status') === 'success') {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::error('IP Lookup failed: ' . $e->getMessage());
            }
            return null;
        });

        if ($geoData) {
            $log->update([
                'country' => $geoData['country'] ?? null,
                'country_code' => $geoData['countryCode'] ?? null,
                'city' => $geoData['city'] ?? null,
                'region' => $geoData['regionName'] ?? null,
                'latitude' => $geoData['lat'] ?? null,
                'longitude' => $geoData['lon'] ?? null,
            ]);
        }
    }
}
