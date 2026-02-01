<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete()->after('id');

            $table->string('subscription_status')->default('trial')->after('plan_id'); // trial|active|past_due|canceled|free
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');

            // cached usage (optional but useful; we’ll compute live for now)
            $table->unsignedInteger('projects_count_cache')->default(0);
            $table->unsignedInteger('members_count_cache')->default(0);
            $table->unsignedBigInteger('storage_mb_cache')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn([
                'subscription_status',
                'trial_ends_at',
                'projects_count_cache',
                'members_count_cache',
                'storage_mb_cache',
            ]);
        });
    }
};
