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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('title', 120);
            $table->text('body')->nullable();
            $table->string('tone')->default('info'); // info, success, warning, danger
            $table->string('cta_text', 40)->nullable();
            $table->string('cta_url')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_global')->default(false);

            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();

            $table->timestamps();

            $table->index(['is_active', 'is_global']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
