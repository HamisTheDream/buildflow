<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Organization;
use App\Models\HR\Department;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $organizations = Organization::all();
        $defaults = ['Sales & Marketing', 'Accounts', 'HR', 'Admin', 'Legal'];

        foreach ($organizations as $org) {
            foreach ($defaults as $name) {
                Department::firstOrCreate([
                    'organization_id' => $org->id,
                    'name' => $name,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down action as we don't want to accidentally delete user-created departments
        // or departments that might have been used.
    }
};
