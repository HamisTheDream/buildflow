<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->timestamp('past_due_at')->nullable()->after('paid_until');
            $table->timestamp('grace_ends_at')->nullable()->after('past_due_at');

            $table->unsignedTinyInteger('dunning_stage')->default(0)->after('grace_ends_at');
            $table->timestamp('last_dunning_sent_at')->nullable()->after('dunning_stage');

            $table->timestamp('last_payment_failed_at')->nullable()->after('last_dunning_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'past_due_at',
                'grace_ends_at',
                'dunning_stage',
                'last_dunning_sent_at',
                'last_payment_failed_at',
            ]);
        });
    }
};
