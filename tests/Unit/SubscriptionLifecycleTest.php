<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    // ─── Trial Status ───────────────────────────────────────────

    public function test_new_org_defaults_to_trial(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'type' => 'company', 'currency' => 'NGN']);

        $this->assertEquals('trial', $org->subscription_status);
        $this->assertNotNull($org->trial_ends_at);
        $this->assertTrue($org->trial_ends_at->isFuture());
    }

    public function test_trial_is_active_within_period(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->assertTrue($org->isTrialActive());
        $this->assertTrue($org->hasAppAccess());
        $this->assertFalse($org->isLocked());
    }

    public function test_trial_expires_after_end_date(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->assertFalse($org->isTrialActive());
        $this->assertFalse($org->hasAppAccess());
        $this->assertTrue($org->isLocked());
    }

    // ─── Active (Paid) Status ───────────────────────────────────

    public function test_active_subscription_grants_access(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'active',
            'paid_until' => now()->addMonth(),
        ]);

        $this->assertTrue($org->isPaidActive());
        $this->assertTrue($org->hasAppAccess());
        $this->assertFalse($org->isLocked());
    }

    public function test_active_subscription_expired_denies_access(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'active',
            'paid_until' => now()->subDay(),
        ]);

        $this->assertFalse($org->isPaidActive());
        $this->assertFalse($org->hasAppAccess());
        $this->assertTrue($org->isLocked());
    }

    // ─── Past Due / Grace Period ────────────────────────────────

    public function test_past_due_with_grace_grants_access(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'past_due',
            'grace_ends_at' => now()->addDays(3),
        ]);

        $this->assertTrue($org->isInGrace());
        $this->assertTrue($org->hasAppAccess());
        $this->assertFalse($org->isLocked());
    }

    public function test_past_due_after_grace_denies_access(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'past_due',
            'grace_ends_at' => now()->subDay(),
        ]);

        $this->assertFalse($org->isInGrace());
        $this->assertFalse($org->hasAppAccess());
        $this->assertTrue($org->isLocked());
    }

    // ─── Suspended / Blocked / Expired ──────────────────────────

    public function test_suspended_org_is_locked(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'suspended',
        ]);

        $this->assertFalse($org->hasAppAccess());
        $this->assertTrue($org->isLocked());
    }

    public function test_blocked_org_has_no_access(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'blocked',
        ]);

        $this->assertFalse($org->hasAppAccess());
    }

    public function test_expired_org_has_no_access(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'expired',
        ]);

        $this->assertFalse($org->hasAppAccess());
    }

    // ─── Free Tier ──────────────────────────────────────────────

    public function test_free_status_always_has_access(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'free',
        ]);

        $this->assertTrue($org->hasAppAccess());
        $this->assertFalse($org->isLocked());
    }

    public function test_null_status_allows_access(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => null,
        ]);

        $this->assertTrue($org->hasAppAccess());
    }

    // ─── Unknown Status Fail-Closed ─────────────────────────────

    public function test_unknown_status_denies_access(): void
    {
        $org = Organization::factory()->create([
            'subscription_status' => 'some_random_status',
        ]);

        $this->assertFalse($org->hasAppAccess());
    }

    // ─── effectivePlan() ────────────────────────────────────────

    public function test_effective_plan_returns_assigned_plan(): void
    {
        $plan = Plan::where('key', 'starter')->first();
        $org = Organization::factory()->create(['plan_id' => $plan->id]);

        $this->assertEquals('starter', $org->effectivePlan()->key);
    }

    public function test_effective_plan_falls_back_to_free(): void
    {
        $org = Organization::factory()->create(['plan_id' => null]);

        $effective = $org->effectivePlan();
        $this->assertNotNull($effective);
        $this->assertEquals('free', $effective->key);
    }
}
