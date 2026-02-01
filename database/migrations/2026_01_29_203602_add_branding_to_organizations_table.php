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
            $table->string('brand_name')->nullable()->after('name');
            $table->string('brand_logo_path')->nullable()->after('brand_name'); // stored in public disk
            $table->string('brand_email')->nullable()->after('brand_logo_path');
            $table->string('brand_phone')->nullable()->after('brand_email');
            $table->string('brand_address')->nullable()->after('brand_phone');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'brand_name',
                'brand_logo_path',
                'brand_email',
                'brand_phone',
                'brand_address',
            ]);
        });
    }
};
