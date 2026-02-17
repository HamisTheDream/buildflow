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
    }
}
