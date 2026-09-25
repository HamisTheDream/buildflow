<?php

namespace Tests\Feature\HR;

use App\Models\User;
use App\Models\Organization;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Leave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HRModuleTest extends TestCase
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

    public function test_can_create_department()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->user)
            ->post(route('hr.departments.store'), [
                'name' => 'Engineering',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', [
            'name' => 'Engineering',
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_can_create_employee()
    {
        $department = Department::create([
            'organization_id' => $this->organization->id,
            'name' => 'IT',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('hr.employees.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'department_id' => $department->id,
                'status' => 'active',
                'salary_amount' => 5000,
                'payment_frequency' => 'monthly',
            ]);

        $response->assertRedirect(route('hr.dashboard'));
        $this->assertDatabaseHas('employees', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'salary_amount_cents' => 500000,
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_can_create_payroll_run()
    {
        $employee = Employee::create([
            'organization_id' => $this->organization->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'salary_amount_cents' => 600000,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('hr.payroll.store'), [
                'run_date' => now()->toDateString(),
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
                'employees' => [
                    [
                        'id' => $employee->id,
                        'amount' => 6000,
                        'bonus' => 500,
                        'deductions' => 100,
                    ]
                ],
            ]);

        $response->assertRedirect(route('hr.dashboard'));
        $this->assertDatabaseHas('payrolls', [
            'organization_id' => $this->organization->id,
            'total_amount_cents' => 640000, // 6000 + 500 - 100 = 6400 * 100
        ]);
    }

    public function test_can_request_leave()
    {
        $employee = Employee::create([
            'organization_id' => $this->organization->id,
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('hr.leaves.store'), [
                'employee_id' => $employee->id,
                'type' => 'vacation',
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
                'reason' => 'Family trip',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leaves', [
            'employee_id' => $employee->id,
            'type' => 'vacation',
            'status' => 'pending',
        ]);
    }

    public function test_hr_dashboard_loads()
    {
        $response = $this->actingAs($this->user)
            ->get(route('hr.dashboard'));

        $response->assertStatus(200);
    }
}
