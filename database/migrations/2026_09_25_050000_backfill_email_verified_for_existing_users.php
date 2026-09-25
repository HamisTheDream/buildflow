<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Email verification is enforced for all app routes from this point on.
     * Accounts created before enforcement are grandfathered as verified so
     * nobody is locked out of their workspace.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Grandfathering is one-way by design; nothing to revert.
    }
};
