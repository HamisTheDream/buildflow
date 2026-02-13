<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
    }

    public function initialize(string $email, int $amountCents, array $metadata = []): array
    {
        $payload = [
            'email' => $email,
            'amount' => $amountCents,
            'metadata' => json_encode($metadata),
            'callback_url' => route('app.billing.callback'),
        ];

        return $this->request('POST', '/transaction/initialize', $payload);
    }

    public function verify(string $reference): array
    {
        return $this->request('GET', "/transaction/verify/{$reference}");
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;

        $http = Http::withToken($this->secretKey)
            ->timeout(15)
            ->acceptJson();

        $response = match (strtoupper($method)) {
            'POST' => $http->post($url, $data),
            'GET' => $http->get($url),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        $response->throw();

        $body = $response->json();

        if (!($body['status'] ?? false)) {
            throw new \Exception('Paystack request failed: ' . ($body['message'] ?? 'Unknown error'));
        }

        return $body['data'];
    }
}
