<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add billing cycle to organizations (safe - skip if exists)
        if (!Schema::hasColumn('organizations', 'billing_cycle')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->string('billing_cycle', 10)->default('monthly')->after('plan_id');
            });
        }

        // Add annual pricing to plans (safe - skip if exists)
        if (!Schema::hasColumn('plans', 'price_annual_cents')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->unsignedInteger('price_annual_cents')->default(0)->after('price_monthly_cents');
            });
        }

        // Add site settings for annual discount configuration
        DB::table('settings')->insertOrIgnore([
            ['key' => 'annual_discount_type', 'value' => 'free_months', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'annual_discount_value', 'value' => '2', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('price_annual_cents');
        });

        DB::table('settings')->whereIn('key', ['annual_discount_type', 'annual_discount_value'])->delete();
    }
};
