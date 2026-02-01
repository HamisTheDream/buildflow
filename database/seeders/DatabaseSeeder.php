<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PlanSeeder::class);

        // Super Admin
        Admin::firstOrCreate(
            ['email' => 'admin@buildflow.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_super' => true,
            ]
        );

        // Owner User
        $user = User::firstOrCreate(
            ['email' => 'owner@buildflow.com'],
            [
                'name' => 'Owner User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Organization
        $org = Organization::firstOrCreate(
            ['name' => 'BuildFlow HQ'],
            [
                'type' => 'company',
                'currency' => 'NGN',
            ]
        );

        // Link User to Organization
        if (!$org->users()->where('user_id', $user->id)->exists()) {
             $org->users()->attach($user->id, ['role' => 'owner']);
        }
        
        // Set current organization
        $user->forceFill(['current_organization_id' => $org->id])->save();
    }
}
