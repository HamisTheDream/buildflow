<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaystackWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock the secret key for testing
        config(['paystack.secret_key' => 'sk_test_xxx']);
    }

    public function test_webhook_rejects_invalid_signature()
    {
        $payload = json_encode(['event' => 'charge.success']);
        
        $response = $this->postJson('/webhooks/paystack', ['event' => 'charge.success'], [
            'x-paystack-signature' => 'invalid-signature'
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_activates_subscription()
    {
        $plan = Plan::create([
            'key' => 'pro',
            'name' => 'Pro',
            'price_monthly_cents' => 5000000,
            'max_projects' => 50,
            'max_members' => 100,
            'max_storage_mb' => 10000,
        ]);

        $org = Organization::create([
            'name' => 'Test Org',
            'type' => 'company',
            'plan_id' => $plan->id,
            'trial_ends_at' => now()->subDay(), // Expired trial
        ]);

        $reference = 'bf_' . Str::random(20);
        
        // Simulate local payment record (initialized)
        $payment = Payment::create([
            'organization_id' => $org->id,
            'plan_id' => $plan->id,
            'reference' => $reference,
            'amount_cents' => 5000000,
            'status' => 'initialized',
        ]);

        $payload = json_encode([
            'event' => 'charge.success',
            'data' => [
                'reference' => $reference,
                'status' => 'success',
                'amount' => 5000000,
                'currency' => 'NGN',
                'paid_at' => now()->toIso8601String(),
                'metadata' => [
                    'org_id' => $org->id,
                    'plan_key' => 'pro',
                ]
            ]
        ]);

        $signature = hash_hmac('sha512', $payload, config('paystack.secret_key'));

        $response = $this->call('POST', '/webhooks/paystack', [], [], [], [
            'HTTP_x-paystack-signature' => $signature,
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $payload);

        $response->assertStatus(200);

        // Assert payment verified
        $this->assertEquals('success', $payment->fresh()->status);
        
        // Assert subscription active
        $org = $org->fresh();
        $this->assertEquals('active', $org->subscription_status);
        $this->assertTrue($org->paid_until->greaterThan(now()->addDays(29))); // roughly 30 days
    }

    public function test_webhook_is_idempotent()
    {
        $plan = Plan::create(['key' => 'pro', 'name' => 'Pro', 'price_monthly_cents' => 5000000, 'max_projects' => 10, 'max_members' => 10, 'max_storage_mb' => 1000]);
        $org = Organization::create(['name' => 'Test Org', 'type' => 'company']);
        $reference = 'bf_idempotency_test';

        $payment = Payment::create([
            'organization_id' => $org->id,
            'plan_id' => $plan->id,
            'reference' => $reference,
            'amount_cents' => 5000000,
            'status' => 'success', // Already success
        ]);

        // Manually set paid_until to something we can check doesn't change
        $originalPaidUntil = now()->addDays(10);
        $org->paid_until = $originalPaidUntil;
        $org->save();

        $payload = json_encode([
            'event' => 'charge.success',
            'data' => [
                'reference' => $reference,
                'status' => 'success',
                'amount' => 5000000,
            ]
        ]);

        $signature = hash_hmac('sha512', $payload, config('paystack.secret_key'));

        $this->call('POST', '/webhooks/paystack', [], [], [], [
            'HTTP_x-paystack-signature' => $signature,
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $payload);

        $org = $org->fresh();
        // Should NOT have extended because status was already success
        // Wait, my controller logic checks idempotency on $payment->status === 'success' and returns early.
        // So org should NOT change.
        
        // NOTE: In testing, timestamps might differ slightly due to DB execution time if it *did* update.
        // But here we expect it to be identical to what we set.
        $this->assertEquals($originalPaidUntil->toDateTimeString(), $org->paid_until->toDateTimeString());
    }
}
