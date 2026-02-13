<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('NGN')->after('currency');
            $table->string('currency_symbol', 5)->default('₦')->after('currency_code');
            $table->decimal('exchange_rate_to_usd', 12, 4)->default(1.0)->after('currency_symbol');
        });

        // Snapshot currency on invoices at creation time for historical accuracy
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('NGN')->after('status');
            $table->string('currency_symbol', 5)->default('₦')->after('currency_code');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'currency_symbol', 'exchange_rate_to_usd']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'currency_symbol']);
        });
    }
};
