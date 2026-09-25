<?php

use App\Models\Organization;
use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Organizations created before plan assignment existed at registration
     * have plan_id NULL, which makes the owner "Plan" filter return no
     * results. Backfill them onto the Free plan. Idempotent and safe to
     * re-run.
     */
    public function up(): void
    {
        $freePlanId = Plan::where('key', 'free')->value('id');

        if ($freePlanId) {
            Organization::whereNull('plan_id')->update(['plan_id' => $freePlanId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No safe automatic reverse: cannot distinguish backfilled rows
        // from rows that were legitimately assigned the Free plan later.
    }
};
