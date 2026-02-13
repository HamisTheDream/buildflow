<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add CRM stage to organizations
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('crm_stage')->default('lead')->after('subscription_status');
            // stages: lead, onboarding, active, risk, churned
        });

        // Organization Notes
        Schema::create('organization_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('type')->default('note'); // note, call, email, meeting
            $table->text('content');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        // Organization Tasks
        Schema::create('organization_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('created_by')->constrained('admins')->cascadeOnDelete();
            $table->text('content');
            $table->dateTime('due_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_tasks');
        Schema::dropIfExists('organization_notes');

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('crm_stage');
        });
    }
};
