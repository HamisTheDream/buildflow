<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackClient
{
    private function base()
    {
        return Http::withToken(config('paystack.secret_key'))
            ->acceptJson()
            ->baseUrl(config('paystack.base_url'));
    }

    public function initializeTransaction(array $payload): array
    {
        $res = $this->base()->post('/transaction/initialize', $payload);
        return $res->json();
    }

    public function verifyTransaction(string $reference): array
    {
        $res = $this->base()->get('/transaction/verify/' . urlencode($reference));
        return $res->json();
    }
}
