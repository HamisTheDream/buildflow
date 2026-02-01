<?php

namespace App\Services;

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
        
        $options = [
            'http' => [
                'method' => $method,
                'header' => [
                    "Authorization: Bearer {$this->secretKey}",
                    "Content-Type: application/json",
                    "Cache-Control: no-cache",
                ],
                'ignore_errors' => true,
            ],
        ];

        if ($method === 'POST') {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result, true);

        if (!$response || !($response['status'] ?? false)) {
            throw new \Exception('Paystack request failed: ' . ($response['message'] ?? 'Unknown error'));
        }

        return $response['data'];
    }
}
