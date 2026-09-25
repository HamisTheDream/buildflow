<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\Project;
use App\Models\User;
use App\Services\UsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for the 2026-09-25 security assessment remediation:
 * trusted proxies, Content-Security-Policy, email verification enforcement,
 * and graceful plan-limit handling on project creation.
 */
class SecurityRemediationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;
    protected $freePlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freePlan = Plan::create([
            'key' => 'free',
            'name' => 'Free',
            'price_monthly_cents' => 0,
            'max_projects' => 1,
            'max_members' => 3,
            'max_storage_mb' => 200,
        ]);

        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create([
            'subscription_status' => 'free',
            'plan_id' => $this->freePlan->id,
        ]);

        $this->user->organizations()->attach($this->organization->id, ['role' => 'owner']);
        $this->user->update(['current_organization_id' => $this->organization->id]);
    }

    public function test_project_creation_over_plan_limit_returns_friendly_error_not_500()
    {
        Project::create([
            'organization_id' => $this->organization->id,
            'name' => 'Existing Project',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->post('/app/projects', [
            'name' => 'Over Limit Project',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString(
            'project limit',
            strtolower(session('error') ?? '')
        );
        $this->assertEquals(
            1,
            Project::where('organization_id', $this->organization->id)->count()
        );
    }

    public function test_project_creation_survives_usage_service_failure()
    {
        $this->mock(UsageService::class, function ($mock) {
            $mock->shouldReceive('withinLimits')->andThrow(new \RuntimeException('simulated gate failure'));
        });

        $response = $this->actingAs($this->user)->post('/app/projects', [
            'name' => 'Gate Failure Project',
            'status' => 'active',
        ]);

        // Must fail closed with a friendly message, never a 500.
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(
            0,
            Project::where('organization_id', $this->organization->id)->count()
        );
    }

    public function test_unverified_user_is_redirected_to_verification_notice()
    {
        $this->user->update(['email_verified_at' => null]);

        $response = $this->actingAs($this->user)->get('/app/dashboard');

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_access_app_dashboard()
    {
        $response = $this->actingAs($this->user)->get('/app/dashboard');

        $response->assertOk();
    }

    public function test_csp_header_is_present_on_web_responses()
    {
        $response = $this->actingAs($this->user)->get('/app/dashboard');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotEmpty($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
    }
}
