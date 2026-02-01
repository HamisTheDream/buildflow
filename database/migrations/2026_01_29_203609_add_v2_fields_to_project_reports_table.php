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
        Schema::table('project_reports', function (Blueprint $table) {
            $table->string('type')->default('summary')->after('title'); // daily|summary|cost
            $table->string('share_password_hash')->nullable()->after('share_token');
        });
    }

    public function down(): void
    {
        Schema::table('project_reports', function (Blueprint $table) {
            $table->dropColumn(['type', 'share_password_hash']);
        });
    }
};
