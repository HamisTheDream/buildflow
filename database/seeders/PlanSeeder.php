<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(['key' => 'free'], [
            'name' => 'Free',
            'price_monthly_cents' => 0,
            'max_projects' => 1,
            'max_members' => 3,
            'max_storage_mb' => 200,
            'can_share_reports' => true,
            'can_password_protect_reports' => false,
        ]);

        Plan::updateOrCreate(['key' => 'starter'], [
            'name' => 'Starter',
            'price_monthly_cents' => 2000000, // NGN 20,000 (kobo)
            'max_projects' => 10,
            'max_members' => 20,
            'max_storage_mb' => 2000,
            'can_share_reports' => true,
            'can_password_protect_reports' => true,
        ]);

        Plan::updateOrCreate(['key' => 'pro'], [
            'name' => 'Pro',
            'price_monthly_cents' => 5000000, // NGN 50,000 (kobo)
            'max_projects' => 50,
            'max_members' => 100,
            'max_storage_mb' => 10000,
            'can_share_reports' => true,
            'can_password_protect_reports' => true,
        ]);
    }
}
