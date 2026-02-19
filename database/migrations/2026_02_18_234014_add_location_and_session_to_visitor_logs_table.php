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
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->string('session_id')->nullable()->after('user_id')->index();
            $table->string('country')->nullable()->after('referer');
            $table->string('country_code', 2)->nullable()->after('country');
            $table->string('city')->nullable()->after('country_code');
            $table->string('region')->nullable()->after('city');
            $table->decimal('latitude', 10, 8)->nullable()->after('region');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropColumn([
                'session_id',
                'country',
                'country_code',
                'city',
                'region',
                'latitude',
                'longitude'
            ]);
        });
    }
};
