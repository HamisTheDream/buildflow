<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Finance\Budget;
use App\Models\Finance\Expense;
use App\Models\Organization;
use App\Models\OrganizationInvite;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regression tests for the 500s and data bugs found during the
 * 2026-09-24 production QA pass (property creation, finance dashboard,
 * owner payment search, plan filter, member invites).
 */
class QaRegressionTest extends TestCase
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

    public function test_property_can_be_created_with_project_and_total_units()
    {
        $this->withoutExceptionHandling();

        $project = Project::create([
            'organization_id' => $this->organization->id,
            'name' => 'Site Alpha',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('crm.properties.store'), [
                'name' => 'Sunset Apartments',
                'address' => '123 Sunset Blvd',
                'type' => 'residential',
                'status' => 'ready',
                'project_id' => $project->id,
                'total_units' => 10,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('crm_properties', [
            'organization_id' => $this->organization->id,
            'name' => 'Sunset Apartments',
            'project_id' => $project->id,
            'total_units' => 10,
        ]);
    }

    public function test_finance_dashboard_loads_with_expenses_and_budgets()
    {
        $this->withoutExceptionHandling();

        Expense::create([
            'organization_id' => $this->organization->id,
            'description' => 'Fuel',
            'amount_cents' => 50000,
            'incurred_date' => now()->toDateString(),
            'category' => 'travel',
        ]);

        Budget::create([
            'organization_id' => $this->organization->id,
            'name' => 'Q4 Budget',
            'total_amount_cents' => 1000000,
            'start_date' => now()->startOfQuarter()->toDateString(),
            'end_date' => now()->endOfQuarter()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('finance.dashboard'));

        $response->assertOk();
    }

    public function test_owner_payment_search_by_reference_does_not_error()
    {
        $this->withoutExceptionHandling();

        $admin = Admin::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'is_super' => true,
            'is_active' => true,
        ]);

        Payment::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'plan_id' => $this->freePlan->id,
            'reference' => 'BF-TEST-001',
            'gateway_reference' => 'PSK-GW-999',
            'amount_cents' => 5000000,
            'status' => 'success',
        ]);

        // Search by gateway reference, combined with a status filter
        // (this previously 500'd on the missing column and had ungrouped ORs).
        $response = $this->actingAs($admin, 'owner')
            ->get(route('owner.billing.payments.index', ['q' => 'PSK-GW', 'status' => 'success']));

        $response->assertOk();

        // Reference search still matches the local reference.
        $response = $this->actingAs($admin, 'owner')
            ->get(route('owner.billing.payments.index', ['q' => 'BF-TEST']));

        $response->assertOk();
    }

    public function test_registration_assigns_free_plan_to_new_organization()
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => 'individual',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('app.dashboard', absolute: false));

        $org = Organization::where('name', "New User's Workspace")->first();
        $this->assertNotNull($org);
        $this->assertEquals($this->freePlan->id, $org->plan_id);
    }

    public function test_invite_succeeds_with_warning_when_mail_transport_fails()
    {
        $this->withoutExceptionHandling();

        // Simulate an SMTP transport failure without touching real mail config.
        $pending = \Mockery::mock(\Illuminate\Mail\PendingMail::class);
        $pending->shouldReceive('send')->once()->andThrow(new \Exception('SMTP connection failed'));
        Mail::shouldReceive('to')->once()->andReturn($pending);

        $response = $this->actingAs($this->user)
            ->post(route('org.invites.create'), [
                'email' => 'invitee@example.com',
                'role' => 'member',
                'expires_days' => 7,
            ]);

        // No 500: the invite record is committed and the user gets a
        // warning with a copyable link instead.
        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('organization_invites', [
            'organization_id' => $this->organization->id,
            'email' => 'invitee@example.com',
        ]);

        $invite = OrganizationInvite::where('email', 'invitee@example.com')->first();
        $this->assertStringContainsString(
            route('invites.show', $invite->token, absolute: false),
            session('warning')
        );
    }
}
