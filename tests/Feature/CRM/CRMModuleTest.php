<?php

namespace Tests\Feature\CRM;

use App\Models\User;
use App\Models\Organization;
use App\Models\CRM\Property;
use App\Models\CRM\PropertyUnit;
use App\Models\CRM\Lead;
use App\Models\CRM\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CRMModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Plan::create([
            'key' => 'free',
            'name' => 'Free',
            'price_monthly_cents' => 0,
            'max_projects' => 1,
            'max_members' => 1,
            'max_storage_mb' => 100,
        ]);

        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();

        $this->user->organizations()->attach($this->organization->id, ['role' => 'owner']);
        $this->user->update(['current_organization_id' => $this->organization->id]);
    }

    public function test_can_create_property()
    {
        $this->withoutExceptionHandling();

        $project = \App\Models\Project::create([
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
            'type' => 'residential',
            'project_id' => $project->id,
            'total_units' => 10,
        ]);
    }

    public function test_can_create_unit()
    {
        $property = Property::create([
            'organization_id' => $this->organization->id,
            'name' => 'Sunset Apartments',
            'type' => 'residential',
            'status' => 'active',
            'address' => '123 Sunset Blvd'
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('crm.properties.units.store', $property), [
                'unit_number' => '101',
                'type' => 'apartment', // Added required field
                'status' => 'available', // Fixed invalid status
                'bedrooms' => 2,
                'bathrooms' => 1,
                'bathrooms' => 1,
                'size_sqm' => 80,
                'rent_amount' => 1200,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('crm_property_units', [
            'property_id' => $property->id,
            'unit_number' => '101',
            'rent_amount_cents' => 120000,
        ]);
    }

    public function test_can_create_lead()
    {
        $response = $this->actingAs($this->user)
            ->post(route('crm.leads.store'), [
                'first_name' => 'Alice',
                'last_name' => 'Prospect',
                'email' => 'alice@example.com',
                'status' => 'new',
                'source' => 'website',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'organization_id' => $this->organization->id,
            'first_name' => 'Alice',
            'last_name' => 'Prospect',
            'email' => 'alice@example.com',
        ]);
    }

    public function test_can_create_deal()
    {
        $lead = Lead::create([
            'organization_id' => $this->organization->id,
            'first_name' => 'Bob',
            'last_name' => 'Buyer',
            'email' => 'bob@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('crm.deals.store'), [
                'lead_id' => $lead->id,
                'title' => 'Apartment Sale',
                'amount' => 500000,
                'stage' => 'negotiation',
                'close_date' => now()->addDays(30)->toDateString(),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('deals', [
            'organization_id' => $this->organization->id,
            'lead_id' => $lead->id,
            'title' => 'Apartment Sale',
            'amount_cents' => 50000000,
            'stage' => 'negotiation',
        ]);
    }

    public function test_crm_dashboard_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('crm.dashboard'));

        $response->assertStatus(200);
    }
}
