<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('action'); // e.g. org.suspend, org.extend_trial
            $table->string('subject_type')->nullable(); // Organization, Payment, etc
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('reason')->nullable();

            $table->string('ip')->nullable();
            $table->string('user_agent', 512)->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};
