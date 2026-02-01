<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_issues', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('status')->default('open'); // open|in_progress|blocked|resolved|closed
            $table->string('severity')->default('medium'); // low|medium|high|critical
            $table->string('category')->default('general'); // general|quality|safety|material|labor|client|finance|scope|other

            $table->date('due_date')->nullable();

            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'severity']);
            $table->index(['project_id', 'assigned_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_issues');
    }
};
