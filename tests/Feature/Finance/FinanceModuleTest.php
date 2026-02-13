<?php

namespace Tests\Feature\Finance;

use App\Models\User;
use App\Models\Organization;
use App\Models\Finance\Invoice;
use App\Models\Finance\Expense;
use App\Models\Finance\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
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

    public function test_can_create_invoice()
    {
        $this->withoutExceptionHandling();

        $client = User::factory()->create();
        $this->organization->users()->attach($client, ['role' => 'client']);

        $response = $this->actingAs($this->user)
            ->post(route('finance.invoices.store'), [
                'client_user_id' => $client->id,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'description' => 'Web Development',
                        'quantity' => 1,
                        'unit_price' => 5000,
                    ]
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'organization_id' => $this->organization->id,
            'client_user_id' => $client->id,
            'total_amount_cents' => 500000,
            'status' => 'draft',
        ]);
    }

    public function test_can_create_expense()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->user)
            ->post(route('finance.expenses.store'), [
                'description' => 'Server Costs',
                'amount' => 50,
                'incurred_date' => now()->toDateString(),
                'category' => 'Technology',
                'status' => 'pending',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'organization_id' => $this->organization->id,
            'description' => 'Server Costs',
            'amount_cents' => 5000,
        ]);
    }

    public function test_can_create_budget()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->user)
            ->post(route('finance.budgets.store'), [
                'name' => 'Marketing',
                'amount' => 1000,
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('budgets', [
            'organization_id' => $this->organization->id,
            'name' => 'Marketing',
            'total_amount_cents' => 100000,
        ]);
    }

    public function test_finance_dashboard_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('finance.dashboard'));

        $response->assertStatus(200);
    }
}
