<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BuildFlow Automations: payment schedules (installment / recurring
     * billing), per-org automation settings, and an automation audit log
     * used for idempotency (never send the same reminder twice).
     */
    public function up(): void
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "12-month plan — Plot A-101"
            $table->foreignId('client_lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('client_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('amount_cents'); // amount per installment
            $table->string('frequency')->default('monthly'); // weekly, monthly, quarterly, yearly
            $table->unsignedInteger('interval')->default(1); // every N frequency units
            $table->date('start_date');
            $table->date('next_run_date');
            $table->date('end_date')->nullable();
            $table->unsignedInteger('max_runs')->nullable(); // null = run until end_date / forever
            $table->unsignedInteger('runs_completed')->default(0);
            $table->string('status')->default('active'); // active, paused, completed, cancelled
            $table->json('meta')->nullable(); // description, line items, invoice notes
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('automation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('automation_key'); // e.g. payment_reminders
            $table->boolean('is_enabled')->default(true);
            $table->json('config')->nullable(); // per-automation knobs (thresholds, SLAs, …)
            $table->timestamps();

            $table->unique(['organization_id', 'automation_key']);
        });

        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('automation_key'); // e.g. payment_reminders
            $table->string('entity_type')->nullable(); // e.g. invoice
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action'); // e.g. reminder_stage_2_sent
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'automation_key', 'entity_type', 'entity_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
        Schema::dropIfExists('automation_settings');
        Schema::dropIfExists('payment_schedules');
    }
};
