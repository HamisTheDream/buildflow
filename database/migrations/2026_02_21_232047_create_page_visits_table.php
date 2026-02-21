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
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();

            $table->string('url', 2048);
            $table->string('path', 1024);
            $table->string('method', 10);

            // Device & Browser
            $table->string('user_agent', 1024)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('device_type', 50)->nullable(); // Desktop, Mobile, Tablet, Robot

            // Location
            $table->string('ip_address', 45)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();

            // Tracking
            $table->string('session_id')->nullable()->index();
            $table->timestamps();

            // Indexes for faster aggregation analytics
            $table->index(['created_at', 'path']);
            $table->index(['created_at', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
