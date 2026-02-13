<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'type' => 'builder',
            'currency' => 'NGN',
            'subscription_status' => 'active', // Default to active for simpler testing
            'trial_ends_at' => now()->addDays(14),
            'paid_until' => now()->addYear(),
            'brand_name' => $this->faker->company(),
            'brand_email' => $this->faker->companyEmail(),
            'crm_stage' => 'lead',
        ];
    }
}
